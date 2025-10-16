<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_modify_attr_links_constraint extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.3.2_modify_attr_links_constraint.sql');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
