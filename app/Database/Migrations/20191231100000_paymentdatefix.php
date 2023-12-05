<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_PaymentDateFix extends Migration
{
	/**
	 * Perform a migration step.
	 */
	public function up(): void
	{
		helper('migration');
		execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.3.2_paymentdatefix.sql');
	}

	/**
	 * Revert a migration step.
	 */
	public function down(): void
	{

	}
}
