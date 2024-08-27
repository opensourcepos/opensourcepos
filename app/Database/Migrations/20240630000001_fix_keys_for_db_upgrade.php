<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class Migration_fix_keys_for_db_upgrade extends Migration {
	/**
	 * Perform a migration step.
	 */
	public function up(): void
	{
		$this->db->query("ALTER TABLE `ospos_tax_codes` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;");

		if (!$this->index_exists('ospos_customers', 'company_name'))
		{
			$this->db->query("ALTER TABLE `ospos_customers` ADD INDEX(`company_name`)");
		}

		$checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
		$foreignKeyExists = $this->db->query($checkSql)->getRow();

		if ($foreignKeyExists)
		{
			$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP FOREIGN KEY ospos_sales_items_taxes_ibfk_1');
		}

		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
			. ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id, item_id, line) '
			. ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id, item_id, line)');


		$this->create_primary_key('customers', 'person_id');
		$this->create_primary_key('employees', 'person_id');
		$this->create_primary_key('suppliers', 'person_id');
	}

	/**
	 * Revert a migration step.
	 */
	public function down(): void
	{
		$checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
		$foreignKeyExists = $this->db->query($checkSql)->getRow();

		if ($foreignKeyExists)
		{
			$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP CONSTRAINT ospos_sales_items_taxes_ibfk_1');
		}

		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
			. ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id) '
			. ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id)');
	}

	private function create_primary_key(string $table, string $index): void
	{
		$result = $this->db->query('SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name= \'' . $this->db->getPrefix() . "$table' AND column_key = '$index'");

		if ( ! $result->getRowArray())
		{
			$this->delete_index($table, $index);
			$forge = Database::forge();
			$forge->addPrimaryKey($table, '');

		}
	}

	private function index_exists(string $table, string $index): bool
	{
		$result = $this->db->query('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = \'' . $this->db->getPrefix() . "$table' AND index_name = '$index'");
		$row_array = $result->getRowArray();
		return $row_array && $row_array['COUNT(*)'] > 0;
	}

	private function delete_index(string $table, string $index): void
	{

		if ($this->index_exists($table, $index))
		{
			$forge = Database::forge();
			$forge->dropKey($table, $index, FALSE);
		}
	}
}
