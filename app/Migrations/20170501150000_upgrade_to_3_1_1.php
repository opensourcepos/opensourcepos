<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Upgrade_To_3_1_1 extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.0.2_to_3.1.1.sql');
	}

	public function down()
	{

	}
}
?>
