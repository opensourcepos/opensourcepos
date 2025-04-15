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
        error_log('Migrating modify_attr_links_constraint');

        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.3.2_modify_attr_links_constraint.sql');

        error_log('Migrating modify_attr_links_constraint');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
