<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_decimal_attribute_type extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_decimal_attribute_type.sql');
	}

	public function down()
	{

	}
}
?>
