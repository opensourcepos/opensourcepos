<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Upgrade_To_3_3_0 extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.2.1_to_3.3.0.sql');
	}

	public function down()
	{

	}
}
?>
