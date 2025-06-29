<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_move_expenses_categories extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Migrating expense categories module');

        $this->db->simpleQuery("UPDATE ospos_grants SET menu_group = 'office' WHERE permission_id = 'expenses_categories'");

        error_log('Migrating expense categories module completed');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
