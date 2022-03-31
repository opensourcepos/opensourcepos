<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_IndiaGST2 extends Migration
{
	public function up(): void
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_indiagst2.sql');
	}

	public function down(): void
	{
	}

}