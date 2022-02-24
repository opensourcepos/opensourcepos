<?php

namespace App\Events;

use app\Libraries\MY_Migration;
use app\Models\Appconfig;
use CodeIgniter\HTTP\IncomingRequest;
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
        $this->migration_config = config('Migrations');
        $this->migration = new My_Migration($this->migration_config);

        $this->session = session();

        //Database Configuration
        $this->config = config('OSPOS');
        $this->appconfig = model('Appconfig');

        if (!$this->migration->is_latest())
        {
            $this->session->sess_destroy();
        }

        foreach($this->appconfig->get_all()->getResult() as $app_config)
        {
            $this->config[$app_config->key] = $app_config->value;
        }

        //Language
        $language_exists = file_exists('../app/Language/' . current_language_code());
        if(current_language_code() == null || current_language() == null || !$language_exists)
        {
            $this->config->language = 'english';
            $this->config->language_code = 'en-US';
        }

        $language = Services::language();
        $language->setLocale($this->config->language_code);

        //Time Zone
        date_default_timezone_set($this->config->timezone ?? 'America/New_York');

        bcscale(max(2, totals_decimals() + tax_decimals()));
    }
}
