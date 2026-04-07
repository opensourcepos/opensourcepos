<?php

namespace App\Events;

use App\Libraries\MY_Migration;
use App\Models\Appconfig;
use CodeIgniter\Session\Handlers\DatabaseHandler;
use CodeIgniter\Session\Handlers\FileHandler;
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
        // Migrations
        $migration_config = config('Migrations');
        $migration = new MY_Migration($migration_config);

        // Use file-based session until database is migrated
        $this->session = session();

        // Database Configuration
        $config = config(OSPOS::class);

        if (!$migration->is_latest()) {
            $this->session->destroy();
        }

        // Check if configured language is valid
        // Set defaults if settings is empty (migrations not run) or language not set
        $language_code = $config->settings['language_code'] ?? null;
        $language = $config->settings['language'] ?? null;
        
        if (empty($config->settings) || $language_code === null) {
            $config->settings['language'] = 'english';
            $config->settings['language_code'] = 'en';
            $language_code = 'en';
            $language = 'english';
        }
        
        $language_exists = file_exists('../app/Language/' . $language_code);

        if (!$language_exists) {
            $config->settings['language'] = 'english';
            $config->settings['language_code'] = 'en';
        }

        $language = Services::language();
        $language->setLocale(current_language_code());

        // Time Zone
        date_default_timezone_set($config->settings['timezone'] ?? ini_get('date.timezone'));

        bcscale(max(2, totals_decimals() + tax_decimals()));
    }


}
