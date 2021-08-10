<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_Upgrade_To_3_2_1 extends Migration
{
	public function up(): void
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.2.0_to_3.2.1.sql');
	}

	public function down(): void
	{

	}
}