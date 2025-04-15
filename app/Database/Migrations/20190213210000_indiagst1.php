<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_IndiaGST1 extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.3.0_indiagst1.sql');

        error_log('Fix definition of Supplier.Tax Id');

        error_log('Definition of Supplier.Tax Id corrected');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
