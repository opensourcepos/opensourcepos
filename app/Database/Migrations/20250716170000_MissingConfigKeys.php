<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_MissingConfigKeys extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Starting transaction...');
        $db = db_connect();
        $db->transStart();

        helper('migration');

        // execute_script returns whether everything executed successfully
        if (execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.2_missing_config_keys.sql')) {
            error_log('Migrated config table.');
        }
        else {
            error_log('Failed to migrate config table.');
        }

        error_log('Transaction completed.');
        $db->transComplete();
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
