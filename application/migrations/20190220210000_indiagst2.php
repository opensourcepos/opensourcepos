<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_IndiaGST2 extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_indiagst2.sql');
	}

	public function down()
	{
	}

}
?>
