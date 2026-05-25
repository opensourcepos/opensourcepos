<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class AddPersonToAttributeLinks extends Migration
{
    public function up(): void
    {
        helper('migration');
        
        // First, modify the generated unique column to include person_id
        // Drop the existing unique constraint
        $this->db->query('ALTER TABLE `ospos_attribute_links` DROP INDEX `attribute_links_uq3`');
        $this->db->query('ALTER TABLE `ospos_attribute_links` DROP COLUMN `generated_unique_column`');
        
        // Add person_id column
        $this->db->query('ALTER TABLE `ospos_attribute_links` ADD COLUMN `person_id` INT(10) NULL AFTER `receiving_id`');
        
        // Add index for person_id
        $this->db->query('ALTER TABLE `ospos_attribute_links` ADD KEY `person_id` (`person_id`)');
        
        // Add foreign key constraint for person_id
        $this->db->query('ALTER TABLE `ospos_attribute_links` ADD CONSTRAINT `ospos_attribute_links_ibfk_6` FOREIGN KEY (`person_id`) REFERENCES `ospos_people` (`person_id`) ON DELETE CASCADE');
        
        // Recreate the generated unique column with person_id support
        // This ensures uniqueness for both item attributes and person attributes
        $this->db->query("ALTER TABLE `ospos_attribute_links`
            ADD COLUMN `generated_unique_column` VARCHAR(255) GENERATED ALWAYS AS (
                CASE
                    WHEN `sale_id` IS NULL AND `receiving_id` IS NULL AND `item_id` IS NOT NULL THEN CONCAT('item-', `definition_id`, '-', `item_id`)
                    WHEN `sale_id` IS NULL AND `receiving_id` IS NULL AND `item_id` IS NULL AND `person_id` IS NOT NULL THEN CONCAT('person-', `definition_id`, '-', `person_id`)
                    ELSE NULL
                END
            ) STORED");
        
        // Re-add unique constraint
        $this->db->query('ALTER TABLE `ospos_attribute_links` ADD UNIQUE INDEX `attribute_links_uq3` (`generated_unique_column`)');
    }

    public function down(): void
    {
        // Drop person_id related constraints and column
        $this->db->query('ALTER TABLE `ospos_attribute_links` DROP INDEX `attribute_links_uq3`');
        $this->db->query('ALTER TABLE `ospos_attribute_links` DROP COLUMN `generated_unique_column`');
        $this->db->query('ALTER TABLE `ospos_attribute_links` DROP FOREIGN KEY `ospos_attribute_links_ibfk_6`');
        $this->db->query('ALTER TABLE `ospos_attribute_links` DROP COLUMN `person_id`');
        
        // Restore original generated column
        $this->db->query("ALTER TABLE `ospos_attribute_links`
            ADD COLUMN `generated_unique_column` VARCHAR(255) GENERATED ALWAYS AS (
                CASE
                    WHEN `sale_id` IS NULL AND `receiving_id` IS NULL AND `item_id` IS NOT NULL THEN CONCAT(`definition_id`, '-', `item_id`)
                    ELSE NULL
                END
            ) STORED");
        $this->db->query('ALTER TABLE `ospos_attribute_links` ADD UNIQUE INDEX `attribute_links_uq3` (`generated_unique_column`)');
    }
}