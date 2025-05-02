<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\Attribute;
use CodeIgniter\Database\ResultInterface;

class fix_duplicate_attributes extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $rows_to_keep = $this->get_all_duplicate_attributes();
        $this->remove_duplicate_attributes($rows_to_keep);

        helper('migration');

        $foreignKeys = [
            'ospos_attribute_links_ibfk_1',
            'ospos_attribute_links_ibfk_2',
            'ospos_attribute_links_ibfk_3',
            'ospos_attribute_links_ibfk_4',
            'ospos_attribute_links_ibfk_5'
        ];

        dropForeignKeyConstraints($foreignKeys, 'attribute_links');

        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.0_attribute_links_unique_constraint.sql');
    }

    /**
     * Retrieves from the database all rows where the item_id and definition_id are the same AND the sale_id/receiving_id is null.
     * It also excludes null item_id rows as those are dropdown items.
     *
     * @return ResultInterface Results containing item_id, definition_id and attribute_id in each row.
     */
    private function get_all_duplicate_attributes(): ResultInterface
    {
        $builder = $this->db->table('attribute_links');
        $builder->select('item_id, definition_id, MIN(attribute_id) as attribute_id');
        $builder->where('sale_id IS NULL');
        $builder->where('receiving_id IS NULL');
        $builder->where('item_id IS NOT NULL');
        $builder->groupBy('item_id, definition_id');
        $builder->having('COUNT(attribute_id) > 1');
        return $builder->get();
    }

    /**
     * Removes the duplicate attributes from the database.
     *
     * @param ResultInterface $rows_to_keep A multidimensional associative array containing item_id, definition_id and attribute_id in each row which should be kept in the database.
     * @return void
     */
    private function remove_duplicate_attributes(ResultInterface $rows_to_keep): void
    {
        $attribute = model(Attribute::class);
        foreach ($rows_to_keep->getResult() as $row) {
            $attribute->deleteAttributeLinks($row->item_id, $row->definition_id);    // Deletes all attribute links for the item_id/definition_id combination
            $attribute->saveAttributeLink($row->item_id, $row->definition_id, $row->attribute_id);
        }
    }


    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
