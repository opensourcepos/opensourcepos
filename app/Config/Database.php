<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
	/**
	 * The directory that holds the Migrations
	 * and Seeds directories.
	 *
	 * @var string
	 */
	public $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

	/**
	 * Lets you choose which connection group to
	 * use if no other is specified.
	 *
	 * @var string
	 */
	public $defaultGroup = 'default';

	/**
	 * The default database connection.
	 *
	 * @var array
	 */
	public $default;

	/**
	 * This database connection is used when
	 * running PHPUnit database tests.
	 *
	 * @var array
	 */
	public $tests;

	/**
	 * This database connection is used when
	 * developing against non-production data.
	 *
	 * @var array
	 */
	public $development;

	public function __construct()
	{
		parent::__construct();

		$this->default = [
			'DSN' => '',
			'hostname' => !empty(getenv('MYSQL_HOST_NAME')) ? getenv('MYSQL_HOST_NAME') : 'localhost',
			'username' => !empty(getenv('MYSQL_USERNAME')) ? getenv('MYSQL_USERNAME') : 'admin',
			'password' => !empty(getenv('MYSQL_PASSWORD')) ? getenv('MYSQL_PASSWORD') : 'pointofsale',
			'database' => !empty(getenv('MYSQL_DB_NAME')) ? getenv('MYSQL_DB_NAME') : 'ospos',
			'DBDriver' => 'MySQLi',
			'DBPrefix' => 'ospos_',
			'pConnect' => false,
			'DBDebug' => (ENVIRONMENT !== 'production'),
			'charset' => 'utf8',
			'DBCollat' => 'utf8_general_ci',
			'swapPre' => '',
			'encrypt' => false,
			'compress' => false,
			'strictOn' => false,
			'failover' => [],
			'port' => 3306
		];

		$this->development = [
			'DSN' => '',
			'hostname' => !empty(getenv('MYSQL_HOST_NAME')) ? getenv('MYSQL_HOST_NAME') : 'localhost',
			'username' => !empty(getenv('MYSQL_USERNAME')) ? getenv('MYSQL_USERNAME') : 'admin',
			'password' => !empty(getenv('MYSQL_PASSWORD')) ? getenv('MYSQL_PASSWORD') : 'pointofsale',
			'database' => !empty(getenv('MYSQL_DB_NAME')) ? getenv('MYSQL_DB_NAME') : 'ospos',
			'DBDriver' => 'MySQLi',
			'DBPrefix' => 'ospos_',
			'pConnect' => false,
			'DBDebug' => (ENVIRONMENT !== 'production'),
			'charset' => 'utf8',
			'DBCollat' => 'utf8_general_ci',
			'swapPre' => '',
			'encrypt' => false,
			'compress' => false,
			'strictOn' => false,
			'failover' => [],
			'port' => 3306
		];

		$this->tests = [
			'DSN' => '',
			'hostname' => !empty(getenv('MYSQL_HOST_NAME')) ? getenv('MYSQL_HOST_NAME') : 'localhost',
			'username' => !empty(getenv('MYSQL_USERNAME')) ? getenv('MYSQL_USERNAME') : 'admin',
			'password' => !empty(getenv('MYSQL_PASSWORD')) ? getenv('MYSQL_PASSWORD') : 'pointofsale',
			'database' => !empty(getenv('MYSQL_DB_NAME')) ? getenv('MYSQL_DB_NAME') : 'ospos',
			'DBDriver' => 'MySQLi',
			'DBPrefix' => 'ospos_',
			'pConnect' => false,
			'DBDebug' => (ENVIRONMENT !== 'production'),
			'charset' => 'utf8',
			'DBCollat' => 'utf8_general_ci',
			'swapPre' => '',
			'encrypt' => false,
			'compress' => false,
			'strictOn' => false,
			'failover' => [],
			'port' => 3306
		];

		// Ensure that we always set the database group to 'tests' if
		// we are currently running an automated test suite, so that
		// we don't overwrite live data on accident.
		switch(ENVIRONMENT)
		{
			case 'testing':
				$this->defaultGroup = 'tests';
				break;
			case 'development';
				$this->defaultGroup = 'development';
				break;
		}
	}
}
