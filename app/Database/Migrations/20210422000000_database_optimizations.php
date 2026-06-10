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
        log_message('info', 'Migrating database optimizations.');

        helper('migration');

        dropForeignKeyConstraints(['ospos_customers_ibfk_1'], 'customers');
        dropForeignKeyConstraints(['ospos_customers_points_ibfk_1'], 'customers_points');
        dropForeignKeyConstraints(['ospos_sales_ibfk_2'], 'sales');
        dropForeignKeyConstraints(['ospos_sales_payments_ibfk_2'], 'sales_payments');
        dropForeignKeyConstraints(['ospos_sales_ibfk_1'], 'sales');
        dropForeignKeyConstraints(['ospos_receivings_ibfk_1'], 'receivings');
        dropForeignKeyConstraints(['ospos_inventory_ibfk_2'], 'inventory');
        dropForeignKeyConstraints(['ospos_grants_ibfk_2'], 'grants');
        dropForeignKeyConstraints(['ospos_expenses_ibfk_2'], 'expenses');
        dropForeignKeyConstraints(['ospos_employees_ibfk_1'], 'employees');
        dropForeignKeyConstraints(['ospos_cash_up_ibfk_1'], 'cash_up');
        dropForeignKeyConstraints(['ospos_cash_up_ibfk_2'], 'cash_up');
        dropForeignKeyConstraints(['ospos_items_ibfk_1'], 'items');
        dropForeignKeyConstraints(['ospos_expenses_ibfk_3'], 'expenses');
        dropForeignKeyConstraints(['ospos_receivings_ibfk_2'], 'receivings');
        dropForeignKeyConstraints(['ospos_suppliers_ibfk_1'], 'suppliers');

        createPrimaryKey('customers', 'person_id');
        createPrimaryKey('employees', 'person_id');
        createPrimaryKey('suppliers', 'person_id');

        $attribute = model(Attribute::class);

        $attribute->deleteOrphanedValues();

        $this->migrateDuplicateAttributeValues(DECIMAL);
        $this->migrateDuplicateAttributeValues(DATE);

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
        $attributeValues = $builder->get();

        $this->db->transStart();

        // Clean up Attribute values table where there is an attribute value and an attribute_date/attribute_decimal
        foreach ($attributeValues->getResultArray() as $attributeValue) {
            $builder = $this->db->table('attribute_values');
            $builder->delete(['attribute_id' => $attributeValue['attribute_id']]);

            $builder = $this->db->table('attribute_links');
            $builder->select('links.definition_id, links.item_id, links.attribute_id, defs.definition_type');
            $builder->join('attribute_definitions defs', 'defs.definition_id = links.definition_id');
            $builder->where('attribute_id', $attributeValue['attribute_id']);
            $attributeLinks = $builder->get();

            if ($attributeLinks) {
                $builder = $this->db->table('attribute_links');
                $attributeLinks = $attributeLinks->getResultArray() ?: [];

                foreach ($attributeLinks as $attributeLink) {
                    $builder->where('attribute_id', $attributeLink['attribute_id']);
                    $builder->where('item_id', $attributeLink['item_id']);
                    $builder->delete();

                    switch ($attributeLink['definition_type']) {
                        case DECIMAL:
                            $value = $attributeValue['attribute_decimal'];
                            break;
                        case DATE:
                            $config = config(OSPOS::class)->settings;
                            $attributeDate = DateTime::createFromFormat('Y-m-d', (string) $attributeValue['attribute_date']);

                            if ($attributeDate === false) {
                                log_message('warning', 'Migration 20210422000000: unparseable attribute_date "' . $attributeValue['attribute_date'] . '" for attribute_id ' . $attributeValue['attribute_id'] . ' — preserving raw value.');
                                $value = (string) $attributeValue['attribute_date'];
                            } else {
                                $dateFormat = empty($config['dateformat']) ? 'Y-m-d' : $config['dateformat'];
                                if (empty($config['dateformat'])) {
                                    log_message('warning', 'Migration 20210422000000: dateformat config empty, falling back to Y-m-d for attribute_id ' . $attributeValue['attribute_id'] . '.');
                                }
                                $value = $attributeDate->format($dateFormat);
                            }
                            break;
                        default:
                            $value = $attributeValue['attribute_value'];
                            break;
                    }

                    $attribute->saveAttributeValue($value, $attributeLink['definition_id'], $attributeLink['item_id'], false, $attributeLink['definition_type']);
                }
            }
        }
        $this->db->transComplete();

        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.0_database_optimizations.sql');
        log_message('info', 'Finished migrating database optimizations.');
    }

    /**
     * Given the type of attribute, deletes any duplicates it finds in the attribute_values table and reassigns those
     */
    private function migrateDuplicateAttributeValues(string $attributeType): void
    {
        // Remove duplicate attribute values needed to make attribute_decimals and attribute_dates unique
        $this->db->transStart();

        $column = 'attribute_' . strtolower($attributeType);

        $builder = $this->db->table('attribute_values');
        $builder->select("$column");
        $builder->groupBy($column);
        $builder->having("COUNT($column) > 1");
        $duplicatedValues = $builder->get();

        foreach ($duplicatedValues->getResultArray() as $duplicatedValue) {
            $subqueryBuilder = $this->db->table('attribute_values');
            $subqueryBuilder->select('attribute_id');
            $subqueryBuilder->where($column, $duplicatedValue[$column]);
            $subquery = $subqueryBuilder->getCompiledSelect();

            $builder = $this->db->table('attribute_values');
            $builder->select('attribute_id');
            $builder->where($column, $duplicatedValue[$column]);
            $builder->where("attribute_id IN ($subquery)", null, false);
            $attributeIdsToFix = $builder->get();

            $this->reassignDuplicateAttributeValues($attributeIdsToFix);
        }

        $this->db->transComplete();
    }

    /**
     * Updates the attribute_id in all attribute_link rows with duplicated attributeIds then deletes unneeded rows from attributeValues
     *
     * @param ResultInterface $attributeIdsToFix All attributeIds that need to parsed
     */
    private function reassignDuplicateAttributeValues(ResultInterface $attributeIdsToFix): void
    {
        $attributeIds = $attributeIdsToFix->getResultArray();
        $retainAttributeId = $attributeIds[0]['attribute_id'];

        foreach ($attributeIds as $attributeId) {
            // Update attribute_link with the attribute_id we are keeping
            $builder = $this->db->table('attribute_links');
            $builder->where('attribute_id', $attributeId['attribute_id']);
            $builder->update(['attribute_id' => $retainAttributeId]);

            // Delete the row from attribute_values if it isn't our keeper
            if ($attributeId['attribute_id'] !== $retainAttributeId) {
                $builder = $this->db->table('attribute_values');
                $builder->delete(['attribute_id' => $attributeId['attribute_id']]);
            }
        }
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
