<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Upgrade_To_3_2_0 extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.1.1_to_3.2.0.sql');
	}

	public function down()
	{

	}
}
?>
