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
        executeScript(APPPATH . 'Database/Migrations/sqlscripts/3.3.0_indiagst1.sql', false, $this->db);
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
