<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_add_item_kit_number extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Migrating add_item_kit_number');

        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.3.3_add_kits_item_number.sql');

        error_log('Migrating add_item_kit_number');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
