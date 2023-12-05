<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_receipttaxindicator extends Migration
{
	/**
	 * Perform a migration step.
	 */
	public function up(): void
	{
		$this->db->query('INSERT INTO ' . $this->db->prefixTable('app_config') . ' (`key`, `value`)
			VALUES (\'receipt_show_tax_ind\', \'0\')');
	}

	/**
	 * Revert a migration step.
	 */
	public function down(): void
	{
		$this->db->query('DELETE FROM ' . $this->db->prefixTable('app_config') . ' WHERE key = \'receipt_show_tax_ind\'');
	}
}
