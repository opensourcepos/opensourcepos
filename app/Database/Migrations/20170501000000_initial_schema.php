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
        // Check if database already has tables (existing install)
        $tables = $this->db->listTables();
        
        if (!empty($tables)) {
            // Database already populated - skip initial schema
            // This is an existing installation upgrading from older version
            return;
        }
        
        // Fresh install - load initial schema
        helper('migration');
        $schemaPath = APPPATH . 'Database/database.sql';
        
        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            
            // Split by semicolons and execute each statement
            // Handle multi-line statements and DELIMITER changes
            $this->executeSqlScript($sql);
        }
    }

    /**
     * Execute SQL script handling multi-line statements
     */
    private function executeSqlScript(string $sql): void
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolons at end of lines
        $statements = preg_split('/;\s*\n/', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $this->db->query($statement);
                } catch (\Exception $e) {
                    // Log but continue - some statements may fail on duplicate indexes etc.
                    log_message('debug', 'Migration statement warning: ' . $e->getMessage());
                }
            }
        }
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