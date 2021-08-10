<?php

namespace app\Libraries;

use CodeIgniter\Database\MigrationRunner;

class MY_Migration extends MigrationRunner {

	public function get_last_migration(): string
	{
		$migrations = $this->findMigrations();
		return basename(end($migrations));
	}

	public function get_current_version(): string
	{
		$builder = $this->db->table('migrations');
		$builder->select('version');
		return $builder->get()->getRow()->version;
	}

	public function is_latest(): bool
	{
		$last_migration = $this->get_last_migration();
		$last_version = $this->getMigrationNumber($last_migration);
		$current_version = $this->get_current_version();

		return $last_version == $current_version;
	}

	public function up(): void
	{
		// TODO: Implement up() method.
	}

	public function down(): void
	{
		// TODO: Implement down() method.
	}
}