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
		$checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
		$foreignKeyExists = $this->db->query($checkSql)->getRow();

		if($foreignKeyExists)
		{
			$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP FOREIGN KEY ospos_sales_items_taxes_ibfk_1');
		}

		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
			. ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id, item_id, line) '
			. ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id, item_id, line)');

		$this->delete_index('customers', 'person_id');
		$this->delete_index('employees', 'person_id');
		$this->delete_index('suppliers', 'person_id');
	}

	/**
	 * Revert a migration step.
	 */
	public function down(): void
	{
		$checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
		$foreignKeyExists = $this->db->query($checkSql)->getRow();

		if($foreignKeyExists)
		{
			$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP CONSTRAINT ospos_sales_items_taxes_ibfk_1');
		}

		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
			. ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id) '
			. ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id)');
	}

	private function delete_index(string $table, string $index): void
	{
		$result = $this->db->query('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = \'' . $this->db->getPrefix() . "$table' AND index_name = '$index'");
		$index_exists = $result->getRowArray()['COUNT(*)'] > 0;

		if($index_exists)
		{
			$forge = Database::forge();
			$forge->dropKey($table, $index, false);
		}
	}
}
