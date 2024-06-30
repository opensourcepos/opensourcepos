<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_fix_keys_for_db_upgrade extends Migration
{
	/**
	 * Perform a migration step.
	 */
	public function up(): void
	{
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP FOREIGN KEY ospos_sales_items_taxes_ibfk_1');
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
			. ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id, item_id, line) '
			. ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id, item_id, line)');
	}

	/**
	 * Revert a migration step.
	 */
	public function down(): void
	{
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP CONSTRAINT ospos_sales_items_taxes_ibfk_1');
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
			. ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id) '
			. ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id)');
	}
}
