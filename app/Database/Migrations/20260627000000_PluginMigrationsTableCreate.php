<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PluginMigrationsTableCreate extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.5.1_PluginMigrationsTableCreate.sql');
    }

    public function down(): void
    {
        $this->forge->dropTable('plugin_migrations', true);
    }
}
