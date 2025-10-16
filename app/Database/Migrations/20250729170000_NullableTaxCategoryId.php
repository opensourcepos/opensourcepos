<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_NullableTaxCategoryId extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Migrating nullable tax category ID');

        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.2_nullable_tax_category_id.sql');

        error_log('Migrated nullable tax category ID');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
