<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTopReportsPermissions extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        // Insert new permissions for Top 50 reports
        $this->db->query("INSERT INTO " . $this->db->prefixTable('permissions') . " (permission_id, module_id) VALUES ('reports_top_items', 'reports')");
        $this->db->query("INSERT INTO " . $this->db->prefixTable('permissions') . " (permission_id, module_id) VALUES ('reports_top_categories', 'reports')");

        // Grant permission to admin (person_id 1 is usually admin)
        $this->db->query("INSERT INTO " . $this->db->prefixTable('grants') . " (permission_id, person_id, menu_group) VALUES ('reports_top_items', 1, 'home')");
        $this->db->query("INSERT INTO " . $this->db->prefixTable('grants') . " (permission_id, person_id, menu_group) VALUES ('reports_top_categories', 1, 'home')");
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $this->db->query("DELETE FROM " . $this->db->prefixTable('grants') . " WHERE permission_id IN ('reports_top_items', 'reports_top_categories')");
        $this->db->query("DELETE FROM " . $this->db->prefixTable('permissions') . " WHERE permission_id IN ('reports_top_items', 'reports_top_categories')");
    }
}
