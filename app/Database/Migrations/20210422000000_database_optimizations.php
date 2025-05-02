<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\ResultInterface;
use App\Models\Attribute;
use Config\Database;
use Config\OSPOS;
use DateTime;

class Migration_database_optimizations extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Migrating database_optimizations');

        $attribute = model(Attribute::class);

        $attribute->delete_orphaned_values();

        $this->migrate_duplicate_attribute_values(DECIMAL);
        $this->migrate_duplicate_attribute_values(DATE);

        // Select all attributes that have data in more than one column
        $builder = $this->db->table('attribute_values');
        $builder->select('attribute_id, attribute_value, attribute_decimal, attribute_date');
        $builder->groupStart();
        $builder->where('attribute_value IS NOT NULL');
        $builder->where('attribute_date IS NOT NULL');
        $builder->groupEnd();
        $builder->orGroupStart();
        $builder->where('attribute_value IS NOT NULL');
        $builder->where('attribute_decimal IS NOT NULL');
        $builder->groupEnd();
        $attribute_values = $builder->get();

        $this->db->transStart();

        // Clean up Attribute values table where there is an attribute value and an attribute_date/attribute_decimal
        foreach ($attribute_values->getResultArray() as $attribute_value) {
            $builder = $this->db->table('attribute_values');
            $builder->delete(['attribute_id' => $attribute_value['attribute_id']]);

            $builder = $this->db->table('attribute_links');
            $builder->select('links.definition_id, links.item_id, links.attribute_id, defs.definition_type');
            $builder->join('attribute_definitions defs', 'defs.definition_id = links.definition_id');
            $builder->where('attribute_id', $attribute_value['attribute_id']);
            $attribute_links = $builder->get();

            if ($attribute_links) {
                $builder = $this->db->table('attribute_links');
                $attribute_links = $attribute_links->getResultArray() ?: [];

                foreach ($attribute_links->getResultArray() as $attribute_link) {
                    $builder->where('attribute_id', $attribute_link['attribute_id']);
                    $builder->where('item_id', $attribute_link['item_id']);
                    $builder->delete();

                    switch ($attribute_link['definition_type']) {
                        case DECIMAL:
                            $value = $attribute_value['attribute_decimal'];
                            break;
                        case DATE:
                            $config = config(OSPOS::class)->settings;
                            $attribute_date = DateTime::createFromFormat('Y-m-d', $attribute_value['attribute_date']);
                            $value = $attribute_date->format($config['dateformat']);
                            break;
                        default:
                            $value = $attribute_value['attribute_value'];
                            break;
                    }

                    $attribute->saveAttributeValue($value, $attribute_link['definition_id'], $attribute_link['item_id'], false, $attribute_link['definition_type']);
                }
            }
        }
        $this->db->transComplete();

        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.0_database_optimizations.sql');
        error_log('Migrating database_optimizations completed');
    }

    /**
     * Given the type of attribute, deletes any duplicates it finds in the attribute_values table and reassigns those
     */
    private function migrate_duplicate_attribute_values($attribute_type): void
    {
        // Remove duplicate attribute values needed to make attribute_decimals and attribute_dates unique
        $this->db->transStart();

        $column = 'attribute_' . strtolower($attribute_type);

        $builder = $this->db->table('attribute_values');
        $builder->select("$column");
        $builder->groupBy($column);
        $builder->having("COUNT($column) > 1");
        $duplicated_values = $builder->get();

        foreach ($duplicated_values->getResultArray() as $duplicated_value) {
            $subquery_builder = $this->db->table('attribute_values');
            $subquery_builder->select('attribute_id');
            $subquery_builder->where($column, $duplicated_value[$column]);
            $subquery = $subquery_builder->getCompiledSelect();

            $builder = $this->db->table('attribute_values');
            $builder->select('attribute_id');
            $builder->where($column, $duplicated_value[$column]);
            $builder->where("attribute_id IN ($subquery)", null, false);
            $attribute_ids_to_fix = $builder->get();

            $this->reassign_duplicate_attribute_values($attribute_ids_to_fix, $duplicated_value);
        }

        $this->db->transComplete();
    }

    /**
     * Updates the attribute_id in all attribute_link rows with duplicated attribute_ids then deletes unneeded rows from attribute_values
     *
     * @param ResultInterface $attribute_ids_to_fix All attribute_ids that need to parsed
     * @param array $attribute_value The attribute value in question.
     */
    private function reassign_duplicate_attribute_values(ResultInterface $attribute_ids_to_fix, array $attribute_value): void
    {
        $attribute_ids = $attribute_ids_to_fix->getResultArray();
        $retain_attribute_id = $attribute_ids[0]['attribute_id'];

        foreach ($attribute_ids as $attribute_id) {
            // Update attribute_link with the attribute_id we are keeping
            $builder = $this->db->table('attribute_links');
            $builder->where('attribute_id', $attribute_id['attribute_id']);
            $builder->update(['attribute_id' => $retain_attribute_id]);

            // Delete the row from attribute_values if it isn't our keeper
            if ($attribute_id['attribute_id'] !== $retain_attribute_id) {
                $builder = $this->db->table('attribute_values');
                $builder->delete(['attribute_id' => $attribute_id['attribute_id']]);
            }
        }
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
