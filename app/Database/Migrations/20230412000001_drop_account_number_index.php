<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_drop_account_number_index extends Migration
{
	public function up(): void
	{
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('customers') . ' DROP INDEX account_number');
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('customers') . ' ADD INDEX account_number (account_number)');
	}

	public function down(): void
	{
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('customers') . ' DROP INDEX account_number');
		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('customers') . ' ADD UNIQUE account_number (account_number)');
	}
}
