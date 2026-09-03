<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_Initial_Schema extends Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Perform a migration step.
     *
     * Deterministically (re)applies the base 3.0.2 schema. Down() is
     * responsible for clearing the application tables first, so this method
     * always runs the initial schema script. We intentionally do NOT skip on
     * "tables already exist": CodeIgniter's listTables() result is cached on
     * the connection, so a prior down()/regress() cycle in the same process
     * can leave a stale table list and a naive "skip if present" check would
     * refuse to rebuild tables that were just dropped.
     */
    public function up(): void
    {
        helper('migration');
        executeScript(APPPATH . 'Database/Migrations/sqlscripts/initial_schema.sql');
    }

    /**
     * Revert a migration step.
     *
     * Drops the base application tables (and the migrations tracking table)
     * so the next up() re-creates a clean 3.0.2 baseline. Disables FK checks
     * so drop order doesn't matter.
     */
    public function down(): void
    {
        // Query the table list straight from the database. We must NOT use
        // $this->db->listTables(): CI4 caches the result in dataCache and, in a
        // long-lived test process, that cached list is stale (populated by a
        // prior test class's connection), so drops would be skipped and the
        // next up() would hit "Table ... already exists".
        $db    = $this->db;
        $result = $db->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?', [$db->database]);

        $tables = $result ? array_column($result->getResultArray(), 'TABLE_NAME') : [];

        // Preserve the migrations tracking table: MigrationRunner::regress()
        // calls removeHistory() AFTER down(), so dropping it here would raise
        // "Table '...ospos_migrations' doesn't exist".
        $migrationsTable = $db->getPrefix() . 'migrations';

        $db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            if ($table === $migrationsTable) {
                continue;
            }
            $db->query('DROP TABLE IF EXISTS `' . $table . '`');
        }
        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        // Invalidate the stale in-connection table-name cache so a subsequent
        // listTables() in the same process sees the fresh state.
        $db->dataCache['table_names'] = [];
    }
}
