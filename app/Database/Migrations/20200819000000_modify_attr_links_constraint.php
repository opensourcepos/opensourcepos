<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_modify_attr_links_constraint extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		error_log('Migrating modify_attr_links_constraint');


		execute_script(APPPATH . 'migrations/sqlscripts/3.3.2_modify_attr_links_constraint.sql');

		error_log('Migrating modify_attr_links_constraint');
	}

	public function down()
	{
	}
}
?>