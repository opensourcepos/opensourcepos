<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_sessions_migration extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.1_migrate_sessions_table.sql');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
