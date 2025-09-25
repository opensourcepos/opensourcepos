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
        error_log('Migrating config keys...');
        helper('migration');

        if (executeScriptWithTransaction(APPPATH . 'Database/Migrations/sqlscripts/3.4.2_missing_config_keys.sql')) {
            error_log('Migrated config keys.');
        } else {
            error_log('Failed to migrate config keys.');
        }
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
