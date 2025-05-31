<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PluginConfigTableCreate extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Migrating plugin_config table started');

        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.1_PluginConfigTableCreate.sql');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
