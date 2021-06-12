<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_add_item_kit_number extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		error_log('Migrating add_item_kit_number');

		execute_script(APPPATH . 'migrations/sqlscripts/3.3.3_add_kits_item_number.sql');

		error_log('Migrating add_item_kit_number');
	}

	public function down()
	{
	}
}
?>