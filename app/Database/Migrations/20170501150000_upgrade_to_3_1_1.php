<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_Upgrade_To_3_1_1 extends Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up(): void
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.0.2_to_3.1.1.sql');
	}

	public function down(): void
	{

	}
}