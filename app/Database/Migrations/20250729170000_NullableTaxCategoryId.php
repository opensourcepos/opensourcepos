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
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.2_missing_config_keys.sql');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
