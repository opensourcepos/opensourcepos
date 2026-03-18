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
     * Only runs on fresh installs - skips if database already has tables.
     */
    public function up(): void
    {
        // Check if core application tables exist (existing install)
        // Note: migrations table may exist even on fresh DB due to migration tracking
        $tables = $this->db->listTables();
        
        // Check for a core application table, not just migrations table
        foreach ($tables as $table) {
            // Strip prefix if present for comparison
            $tableName = str_replace($this->db->getPrefix(), '', $table);
            if (in_array($tableName, ['app_config', 'items', 'employees', 'people'])) {
                // Database already populated - skip initial schema
                // This is an existing installation upgrading from older version
                return;
            }
        }
        
        // Fresh install - load initial schema
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/initial_schema.sql');
    }

    /**
     * Revert a migration step.
     * Cannot revert initial schema - would lose all data.
     */
    public function down(): void
    {
        // Cannot safely revert initial schema
        // Would require dropping all tables which would lose all data
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        
        foreach ($this->db->listTables() as $table) {
            $this->db->query('DROP TABLE IF EXISTS `' . $table . '`');
        }
        
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }
}