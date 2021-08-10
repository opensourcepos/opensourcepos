<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_decimal_attribute_type extends Migration
{
	public function up(): void
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_decimal_attribute_type.sql');
	}

	public function down(): void
	{

	}
}