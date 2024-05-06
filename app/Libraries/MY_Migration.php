<?php

namespace App\Libraries;

use CodeIgniter\Database\MigrationRunner;
use Config\Database;
use stdClass;

class MY_Migration extends MigrationRunner
{
	/**
	 * @return bool
	 */
	public function is_latest(): bool
	{
		$latest_version = $this->get_latest_migration();
		$current_version = $this->get_current_version();

		return $latest_version === $current_version;
	}

	/**
	 * @return int
	 */
	public function get_latest_migration(): int
	{
		$migrations = $this->findMigrations();
		return basename(end($migrations)->version);
	}

	/**
	 * Gets the database version number
	 *
	 * @return int The version number of the last successfully run database migration.
	 */
	public static function get_current_version(): int
	{
		$db = Database::connect();
		if($db->tableExists('migrations'))
		{
			$builder = $db->table('migrations');
			$builder->select('version')->orderBy('version', 'DESC')->limit(1);
			return $builder->get()->getRow()->version;
		}

		return 0;
	}

	/**
	 * @return void
	 */
	public function migrate_to_ci4(): void
	{
		$ci3_migrations_version = $this->ci3_migrations_exists();
		if($ci3_migrations_version)
		{
			$this->migrate_table($ci3_migrations_version);
		}
	}

	/**
	 * Checks to see if a ci3 version of the migrations table exists
	 *
	 * @return bool|string The version number of the last CI3 migration to run or false if the table is CI4 or doesn't exist
	 */
	private function ci3_migrations_exists(): bool|string
	{
		if($this->db->tableExists('migrations') && !$this->db->fieldExists('id','migrations'))
		{
			$builder = $this->db->table('migrations');
			$builder->select('version');
			return $builder->get()->getRow()->version;
		}

		return false;
	}

	/**
	 * @param string $ci3_migrations_version
	 * @return void
	 */
	private function migrate_table(string $ci3_migrations_version): void
	{
		$this->convert_table();

		$available_migrations = $this->get_available_migrations();

		foreach($available_migrations as $version => $path)
		{
			if($version > (int)$ci3_migrations_version)
			{
				break;
			}

			$migration = new stdClass();
			$migration->version = $version;
			$migration->class = $path;
			$migration->namespace = 'App';

			$this->addHistory($migration, 1);
		}
	}

	/**
	 * @return void
	 */
	public function up(): void
	{
		// TODO: Implement up() method.
	}

	/**
	 * @return void
	 */
	public function down(): void
	{
		// TODO: Implement down() method.
	}

	/**
	 * @return array
	 */
	private function get_available_migrations(): array
	{
		$migrations = $this->findMigrations();
		$exploded_migrations = [];

		foreach($migrations as $migration)
		{
			$version = substr($migration->uid,0,14);
			$path = substr($migration->uid, 14);

			$exploded_migrations[$version] = $path;
		}

		ksort($exploded_migrations);

		return $exploded_migrations;
	}

	/**
	 * Converts the CI3 migrations database to CI4
	 * @return void
	 */
	public function convert_table(): void
	{
		$forge = Database::forge();
		$forge->dropTable('migrations');

		$this->ensureTable();
	}
}
