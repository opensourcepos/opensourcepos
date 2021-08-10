<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_PaymentDateFix extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.2_paymentdatefix.sql');
	}

	public function down()
	{

	}
}
?>
