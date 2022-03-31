<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_cashrounding extends Migration
{
	public function up(): void
	{
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_payments') . ' ADD COLUMN `cash_adjustment` tinyint NOT NULL DEFAULT 0 AFTER `cash_refund`');
	}

	public function down(): void
	{
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_payments') . ' DROP COLUMN `cash_adjustment`');
	}
}