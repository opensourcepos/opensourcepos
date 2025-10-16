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
        helper('migration');
        $foreignKeys = [
            'ospos_attribute_links_ibfk_1',
            'ospos_attribute_links_ibfk_2',
        ];

        dropForeignKeyConstraints($foreignKeys, 'attribute_links');
        dropColumnIfExists('ospos_attribute_links', 'generated_unique_column');

        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.1_attribute_links_unique_constraint.sql');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
