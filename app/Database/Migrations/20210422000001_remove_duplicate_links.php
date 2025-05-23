<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\Attribute;

class Migration_remove_duplicate_links extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Migrating remove_duplicate_links');

        $this->migrate_duplicate_attribute_links();

        error_log('Migrating remove_duplicate_links completed');
    }

    /**
     * Given the type of attribute, deletes any duplicates it finds in the attribute_values table and reassigns those
     *
     * @property attribute $attribute
     */
    private function migrate_duplicate_attribute_links(): void
    {
        $attribute = model(Attribute::class);

        // Remove duplicate attribute links
        $this->db->transStart();

        $builder = $this->db->table('attribute_links');
        $builder->select('item_id, definition_id, attribute_id, COUNT(*) as count');
        $builder->where('sale_id', null);
        $builder->where('receiving_id', null);
        $builder->where('item_id IS NOT NULL');
        $builder->groupBy('item_id');
        $builder->groupBy('definition_id');
        $builder->groupBy('attribute_id');
        $builder->having('count > 1');
        $duplicated_links = $builder->get();

        $builder = $this->db->table('attribute_links');

        foreach ($duplicated_links->getResultArray() as $duplicated_link) {
            $builder->where('sale_id', null);
            $builder->where('receiving_id', null);
            $builder->where('item_id', $duplicated_link['item_id']);
            $builder->where('definition_id', $duplicated_link['definition_id']);
            $builder->delete();

            $attribute->saveAttributeLink($duplicated_link['item_id'], $duplicated_link['definition_id'], $duplicated_link['attribute_id']);
        }

        $this->db->transComplete();
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
