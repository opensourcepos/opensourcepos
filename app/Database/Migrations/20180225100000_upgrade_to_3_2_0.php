<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_Upgrade_To_3_2_0 extends Migration
{
	public function up(): void
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.1.1_to_3.2.0.sql');
	}

	public function down(): void
	{

	}
}