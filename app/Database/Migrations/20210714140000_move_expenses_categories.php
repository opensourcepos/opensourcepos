<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_move_expenses_categories extends Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up(): void
	{
		error_log('Migrating expense categories module');

		$this->db->simpleQuery("UPDATE ospos_grants SET menu_group = 'office' WHERE permission_id = 'expenses_categories'");

		error_log('Migrating expense categories module completed');
	}
}
