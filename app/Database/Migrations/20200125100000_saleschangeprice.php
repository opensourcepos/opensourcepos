<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_SalesChangePrice extends Migration
{
	public function up(): void
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.2_saleschangeprice.sql');
	}

	public function down(): void
	{

	}
}