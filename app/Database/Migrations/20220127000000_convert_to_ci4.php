<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Convert_to_ci4 extends Migration
{
	public function up(): void
	{
		error_log('Migrating database to CodeIgniter4 formats');

		helper('migration');
		execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.0_ci4_conversion.sql');

		error_log('Migrating to CodeIgniter4 formats completed');
	}

	public function down(): void
	{

	}
}
