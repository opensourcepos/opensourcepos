<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class Migration_fix_keys_for_db_upgrade extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        helper('migration');

        $forge = Database::forge();
        $fields = [
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ],
        ];
        $forge->modifyColumn('tax_codes', $fields);

        if (!indexExists('customers', 'company_name')) {
            $forge->addKey('company_name', false, false, 'company_name');
            $forge->processIndexes('customers');
        }

        $checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
        $foreignKeyExists = $this->db->query($checkSql)->getRow();

        if ($foreignKeyExists) {
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP FOREIGN KEY ospos_sales_items_taxes_ibfk_1');
        }

        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
            . ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id, item_id, line) '
            . ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id, item_id, line)');

        createPrimaryKey('customers', 'person_id');
        createPrimaryKey('employees', 'person_id');
        createPrimaryKey('suppliers', 'person_id');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
        $foreignKeyExists = $this->db->query($checkSql)->getRow();

        if ($foreignKeyExists) {
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP CONSTRAINT ospos_sales_items_taxes_ibfk_1');
        }

        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
            . ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id) '
            . ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id)');
    }
}
