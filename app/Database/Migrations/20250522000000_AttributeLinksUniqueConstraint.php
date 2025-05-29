<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class AttributeLinksUniqueConstraint extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Migrating attribute_links unique constraint started');

        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.1_attribute_links_unique_constraint.sql');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
