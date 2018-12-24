<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Migration extends CI_Migration {

	public function get_last_migration()
	{
		$migrations = $this->find_migrations();
		return basename(end($migrations));
	}

	public function is_latest()
	{
		$last_migration = $this->get_last_migration();
		$last_version = $this->_get_migration_number($last_migration);
		$current_version = $this->_get_version();

		return $last_version == $current_version;
	}

}