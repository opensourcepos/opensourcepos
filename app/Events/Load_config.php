<?php

namespace App\Events;

use App\Libraries\MY_Migration;
use App\Models\Appconfig;
use CodeIgniter\Session\Session;
use Config\OSPOS;
use Config\Services;

/**
 * @property my_migration migration;
 * @property session session;
 * @property appconfig appconfig;
 * @property mixed $migration_config
 * @property mixed $config
 */
class Load_config
{
	public Session $session;

	/**
	 * Loads configuration from database into App CI config and then applies those settings
	 */
	public function load_config(): void
	{
		//Migrations
		$migration_config = config('Migrations');
		$migration = new MY_Migration($migration_config);

		$this->session = session();

		//Database Configuration
		$config = config(OSPOS::class);

		if (!$migration->is_latest())
		{
			$this->session->destroy();
		}

		//Language
		$language_exists = file_exists('../app/Language/' . current_language_code());

		if(current_language_code() == null || current_language() == null || !$language_exists)	//TODO: current_language() is undefined
		{
			$config->settings['language'] = 'english';
			$config->settings['language_code'] = 'en';
		}

		$language = Services::language();
		$language->setLocale($config->settings['language_code']);

		//Time Zone
		date_default_timezone_set($config->settings['timezone'] ?? ini_get('date.timezone'));

		bcscale(max(2, totals_decimals() + tax_decimals()));
	}
}
