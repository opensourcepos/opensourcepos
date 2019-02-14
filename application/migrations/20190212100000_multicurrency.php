<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_MultiCurrency extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/multi_currency_sales.sql');
	}

	public function down()
	{

	}
}
?>
