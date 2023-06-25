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
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to
     * use if no other is specified.
     */
    public string $defaultGroup = 'default';

	/**
	 * The default database connection.
	 */
	public array $default = [
			'DSN' => '',
			'hostname' => 'localhost',
			'username' => 'admin',
			'password' => 'pointofsale',
			'database' => 'ospos',
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

	/**
	 * This database connection is used when
	 * running PHPUnit database tests.
	 */
	public array $tests = [
		'DSN' => '',
		'hostname' => 'localhost',
		'username' => 'admin',
		'password' => 'pointofsale',
		'database' => 'ospos',
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
		'port' => 3306,
		'foreignKeys' => true,
		'busyTimeout' => 1000,
	];

	/**
	 * This database connection is used when
	 * developing against non-production data.
	 *
	 * @var array
	 */
	public $development = [
		'DSN' => '',
		'hostname' => 'localhost',
		'username' => 'admin',
		'password' => 'pointofsale',
		'database' => 'ospos',
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
		'port' => 3306,
		'foreignKeys' => true,
		'busyTimeout' => 1000,
	];

    public function __construct()
    {
        parent::__construct();

		if(!getenv('database.development.hostname'))
		{
			$this->development['hostname'] =  getenv('database.development.hostname');
		}

		if(!getenv('database.development.hostname'))
		{
			$this->development['username'] =  getenv('database.development.username');
		}

		if(!getenv('database.development.hostname'))
		{
			$this->development['password'] =  getenv('database.development.password');
		}

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
