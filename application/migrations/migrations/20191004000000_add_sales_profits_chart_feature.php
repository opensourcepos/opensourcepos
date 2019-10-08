<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Add_Sales_Profits_Chart_Feature extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/add_sales_profits_chart_feature.sql');
	}

	public function down()
	{

	}
}
?>
