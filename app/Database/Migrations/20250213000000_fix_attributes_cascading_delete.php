<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\Attribute;
use CodeIgniter\Database\ResultInterface;

class Migration_Attributes_fix_cascading_delete extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        helper('migration');

        $this->db->query("ALTER TABLE `ospos_attribute_links` DROP INDEX `attribute_links_uq3`");
        $this->db->query("ALTER TABLE `ospos_attribute_links` DROP COLUMN `generated_unique_column`");

        dropForeignKeyConstraints(['ospos_attribute_links_ibfk_1', 'ospos_attribute_links_ibfk_2'], 'attribute_links');

        $this->db->query("ALTER TABLE `ospos_attribute_links` ADD CONSTRAINT `ospos_attribute_links_ibfk_1` FOREIGN KEY (`definition_id`) REFERENCES `ospos_attribute_definitions` (`definition_id`) ON DELETE CASCADE;");
        $this->db->query("ALTER TABLE `ospos_attribute_links` ADD CONSTRAINT `ospos_attribute_links_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `ospos_attribute_values` (`attribute_id`) ON DELETE CASCADE;");
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
