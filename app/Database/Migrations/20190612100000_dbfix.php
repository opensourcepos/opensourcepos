<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_DBFix extends Migration
{
	public function up(): void
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_dbfix.sql');
	}

	public function down(): void
	{

	}
}