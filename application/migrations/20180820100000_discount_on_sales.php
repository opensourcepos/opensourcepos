<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Discount_on_sales extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/discount_on_sales.sql');
	}

	public function down()
	{

	}
}
?>
