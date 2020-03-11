<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_add_iso_4217 extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_add_iso_4217.sql');
	}

	public function down()
	{

	}
}
?>
