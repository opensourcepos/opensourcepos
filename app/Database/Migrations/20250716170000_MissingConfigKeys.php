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
        helper('migration');
        executeScript(APPPATH . 'Database/Migrations/sqlscripts/3.4.2_missing_config_keys.sql', true);
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
