<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_fix_attribute_datetime extends Migration
{
	public function up(): void
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_fix_attribute_datetime.sql');
	}

	public function down(): void
	{

	}
}