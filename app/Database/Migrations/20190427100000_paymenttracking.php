<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_PaymentTracking extends Migration
{
	public function up(): void
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_paymenttracking.sql');
	}

	public function down(): void
	{

	}
}