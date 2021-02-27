<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Customer_Tags extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.4_customer_tags.sql');
	}

	public function down()
	{

	}
}
?>
