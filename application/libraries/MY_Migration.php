<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Migration extends CI_Migration {

	public function is_latest()
	{
		$migrations = $this->find_migrations();
		$last_migration = basename(end($migrations));

		$last_version = $this->_get_migration_number($last_migration);
		$current_version = $this->_get_version();

		return $last_version == $current_version;
	}

}