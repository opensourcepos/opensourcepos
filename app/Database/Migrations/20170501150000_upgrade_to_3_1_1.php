<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_Upgrade_To_3_1_1 extends Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Perform a migration step.
	 */
	public function up(): void
	{
		helper('migration');
		execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.0.2_to_3.1.1.sql');
	}

	/**
	 * Revert a migration step.
	 */
	public function down(): void
	{

	}
}
