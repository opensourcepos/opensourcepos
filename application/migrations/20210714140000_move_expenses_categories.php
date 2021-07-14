<?php

class Migration_move_expenses_categories extends CI_Migration {

	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		error_log('Migrating expense categories module');

		$this->db->simple_query("UPDATE ospos_grants SET menu_group = 'office' WHERE permission_id = 'expenses_categories'");

		error_log('Migrating expense categories module completed');
	}
}
