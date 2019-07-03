<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_SecondDisplay extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_SecondDisplay.sql');
	}

	public function down()
	{

	}
}
?>
