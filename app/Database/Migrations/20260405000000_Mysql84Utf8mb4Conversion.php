<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class Migration_Mysql84Utf8mb4Conversion extends Migration
{
    public function up(): void
    {
        helper('migration');

        $this->assertNoCollationCollisions();

        $foreignKeys = dropAllForeignKeyConstraints();

        try {
            if (indexExists('people', 'first_name')) {
                $this->db->query('ALTER TABLE ' . $this->db->prefixTable('people') . ' DROP INDEX first_name');
            }

            $script = APPPATH . 'Database/Migrations/sqlscripts/3.4.3_mysql84_utf8mb4_conversion.sql';
            if (!executeScript($script)) {
                throw new RuntimeException('Failed to execute utf8mb4 conversion migration: ' . $script);
            }

            if (!$this->db->query('ALTER TABLE ' . $this->db->prefixTable('people')
                . ' ADD INDEX(`first_name`(191), `last_name`(191), `email`(191), `phone_number`(191))')) {
                throw new RuntimeException('Failed to add composite index on people table');
            }
        } catch (\Throwable $exception) {
            log_message('error', 'utf8mb4 conversion failed; recovering dropped foreign keys: '
                . json_encode($foreignKeys));

            try {
                recreateForeignKeyConstraints($foreignKeys);
            } catch (\Throwable $restoreError) {
                log_message('error', 'Foreign key recovery failed: ' . $restoreError->getMessage());
            }

            throw $exception;
        }

        recreateForeignKeyConstraints($foreignKeys);
    }

    // Intentionally irreversible: converting back to utf8 could cause data loss for utf8mb4 characters.
    public function down(): void
    {
    }

    /**
     * utf8mb4_unicode_520_ci treats invisible/ignorable Unicode characters (e.g. the LTR
     * mark U+200E) as equal weight to nothing, unlike utf8_general_ci. Values that were
     * previously distinct under a UNIQUE constraint can collide once converted, which aborts
     * the ALTER TABLE mid-script. Detect those collisions up front and fail with an actionable
     * message rather than the raw "Duplicate entry" error partway through the conversion.
     */
    private function assertNoCollationCollisions(): void
    {
        $uniqueColumns = $this->db->query("
            SELECT DISTINCT s.TABLE_NAME, s.COLUMN_NAME
            FROM information_schema.STATISTICS s
            JOIN information_schema.COLUMNS c
                ON c.TABLE_SCHEMA = s.TABLE_SCHEMA
                AND c.TABLE_NAME = s.TABLE_NAME
                AND c.COLUMN_NAME = s.COLUMN_NAME
            WHERE s.TABLE_SCHEMA = DATABASE()
                AND s.NON_UNIQUE = 0
                AND s.TABLE_NAME LIKE '" . $this->db->getPrefix() . "%'
                AND c.DATA_TYPE IN ('char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext')
                AND (
                    SELECT COUNT(*) FROM information_schema.STATISTICS s2
                    WHERE s2.TABLE_SCHEMA = s.TABLE_SCHEMA
                        AND s2.TABLE_NAME = s.TABLE_NAME
                        AND s2.INDEX_NAME = s.INDEX_NAME
                ) = 1
        ")->getResultArray();

        $problems = [];
        foreach ($uniqueColumns as $uniqueColumn) {
            $table = $uniqueColumn['TABLE_NAME'];
            $column = $uniqueColumn['COLUMN_NAME'];

            $collisions = $this->db->query("
                SELECT CONVERT(`$column` USING utf8mb4) COLLATE utf8mb4_unicode_520_ci AS value,
                       COUNT(*) AS count
                FROM `$table`
                WHERE `$column` IS NOT NULL
                GROUP BY CONVERT(`$column` USING utf8mb4) COLLATE utf8mb4_unicode_520_ci
                HAVING count > 1
            ")->getResultArray();

            if ($collisions) {
                $problems[] = "$table.$column (" . count($collisions) . ' colliding value(s))';
            }
        }

        if ($problems) {
            throw new RuntimeException(
                'Cannot convert to utf8mb4: the following unique column(s) contain values that '
                . 'collide under utf8mb4_unicode_520_ci (often due to invisible Unicode characters '
                . "that this collation treats as equivalent):\n  - " . implode("\n  - ", $problems)
                . "\nClean up or merge the colliding rows before retrying this migration."
            );
        }
    }
}
