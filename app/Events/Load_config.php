<?php

namespace App\Events;

use app\Libraries\MY_Migration;
use app\Models\Appconfig;
use CodeIgniter\Session\Session;
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
    /**
     * Loads configuration from database into App CI config and then applies those settings
     */
    public function load_config()
    {
        //Migrations
        $migration_config = config('Migrations');
        $migration = new MY_Migration($migration_config);	//TODO: This errors out.  We need to figure out how to automatically check and run latest migrations in CI4... the CI3 method is different.

        $this->session = session();

        //Database Configuration
        $config = config('OSPOS');
        $appconfig = model(Appconfig::class);

        if (!$migration->is_latest())
        {
            $this->session->destroy();
        }

        foreach($appconfig->get_all()->getResult() as $app_config)
        {
            $config[$app_config->key] = $app_config->value;
        }

        //Language
        $language_exists = file_exists('../app/Language/' . current_language_code());
        if(current_language_code() == null || current_language() == null || !$language_exists)
        {
            $config->language = 'english';
            $config->language_code = 'en-US';
        }

        $language = Services::language();
        $language->setLocale($config->language_code);

        //Time Zone
        date_default_timezone_set($config->timezone ?? 'America/New_York');

        bcscale(max(2, totals_decimals() + tax_decimals()));
    }
}
