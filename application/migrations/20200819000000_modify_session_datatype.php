<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_modify_session_datatype extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		error_log('Migrating modify_session_datatype');

		execute_script(APPPATH . 'migrations/sqlscripts/3.3.4_modify_session_datatype.sql');

		error_log('Migrating modify_session_datatype');
	}

	public function down()
	{
	}
}
?>