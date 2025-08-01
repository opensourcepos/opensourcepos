<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_NullableTaxCategoryId extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Migrating config table');

        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.2_missing_config_keys.sql');

        error_log('Migrating config table');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
