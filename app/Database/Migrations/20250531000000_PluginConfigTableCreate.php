<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PluginConfigTableCreate extends Migration
{
    public function up(): void
    {
        log_message('info', 'Migrating plugin_config table started');

        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.1_PluginConfigTableCreate.sql');
    }

    public function down(): void
    {
    }
}
