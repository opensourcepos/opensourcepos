<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class Migration_Mysql84Utf8mb4Conversion extends Migration
{
    public function up(): void
    {
        helper('migration');

        // Drop all foreign keys to avoid collation mismatches during conversion
        $foreignKeys = $this->getAllForeignKeys();
        foreach ($foreignKeys as $fk) {
            try {
                $this->db->query('ALTER TABLE `' . $fk['TABLE_NAME'] . '` DROP FOREIGN KEY `' . $fk['CONSTRAINT_NAME'] . '`');
            } catch (\Exception $e) {
                log_message('error', 'Failed to drop FK ' . $fk['CONSTRAINT_NAME'] . ': ' . $e->getMessage());
            }
        }

        // Convert all tables to utf8mb4
        $script = APPPATH . 'Database/Migrations/sqlscripts/3.5.0_mysql84_utf8mb4_conversion.sql';
        if (!execute_script($script)) {
            throw new RuntimeException('Failed to execute utf8mb4 conversion migration: ' . $script);
        }

        // Rebuild composite index on ospos_people with utf8mb4-safe prefix lengths
        if (indexExists('people', 'first_name')) {
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable('people') . ' DROP INDEX first_name');
        }
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('people')
            . ' ADD INDEX(`first_name`(191), `last_name`(191), `email`(191), `phone_number`(191))');

        // Recreate all foreign keys
        foreach ($foreignKeys as $fk) {
            try {
                $onDelete = $fk['DELETE_RULE'] !== 'NO ACTION' ? ' ON DELETE ' . $fk['DELETE_RULE'] : '';
                $onUpdate = $fk['UPDATE_RULE'] !== 'NO ACTION' ? ' ON UPDATE ' . $fk['UPDATE_RULE'] : '';
                $this->db->query('ALTER TABLE `' . $fk['TABLE_NAME'] . '` ADD CONSTRAINT `' . $fk['CONSTRAINT_NAME']
                    . '` FOREIGN KEY (`' . $fk['COLUMN_NAME'] . '`) REFERENCES `' . $fk['REFERENCED_TABLE_NAME']
                    . '` (`' . $fk['REFERENCED_COLUMN_NAME'] . '`)' . $onDelete . $onUpdate);
            } catch (\Exception $e) {
                log_message('error', 'Failed to recreate FK ' . $fk['CONSTRAINT_NAME'] . ': ' . $e->getMessage());
            }
        }
    }

    // Intentionally irreversible: converting back to utf8 could cause data loss for utf8mb4 characters.
    public function down(): void
    {
    }

    private function getAllForeignKeys(): array
    {
        $result = $this->db->query("
            SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME,
                   REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME,
                   rc.DELETE_RULE, rc.UPDATE_RULE
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
            WHERE kcu.CONSTRAINT_SCHEMA = DATABASE()
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            ORDER BY kcu.TABLE_NAME, kcu.CONSTRAINT_NAME
        ");
        return $result->getResultArray();
    }
}
