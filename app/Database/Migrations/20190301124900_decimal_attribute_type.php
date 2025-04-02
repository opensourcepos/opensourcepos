<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_decimal_attribute_type extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.3.0_decimal_attribute_type.sql');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
