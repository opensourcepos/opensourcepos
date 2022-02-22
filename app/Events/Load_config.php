<?php

namespace App\Events;

use app\Libraries\MY_Migration;
use app\Models\Appconfig;
use CodeIgniter\Session\Session;

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
     * Loads configuration from database into App CI config
     */
    public function load_config()
    {
        $this->migration_config = config('Migrations');
        $this->migration = new My_Migration($this->migration_config);

        $this->session = session();

        $this->config = config('App');  //TODO: is this the best place to dump the database app configuration values?
        $this->appconfig = model('Appconfig');

        if (!$this->migration->is_latest())
        {
            $this->session->sess_destroy();
        }

        foreach($this->appconfig->get_all()->getResult() as $app_config)
        {
            $this->config[$app_config->key] = $app_config->value;
        }

        // fallback to English if language settings are not correct
        $file_exists = !file_exists('../app/Language/' . current_language_code());
        if(current_language_code() == null || current_language() == null || $file_exists)
        {
            $this->config->language = 'english';
            $this->config->language_code = 'en-US';
        }

        $this->load_language_files('../vendor/codeigniter4/framework/system/Language', current_language(), FALSE);
        $this->load_language_files('../app/Language', current_language_code(), TRUE);

        //Set timezone from config database
        date_default_timezone_set($this->config->timezone ?? 'America/New_York');

        bcscale(max(2, totals_decimals() + tax_decimals()));
    }

    /**
     * @param $path
     * @param $language
     * @param $fallback
     */
    private function load_language_files($path, $language, $fallback)
    {
        $map = directory_map($path . DIRECTORY_SEPARATOR . $language);

        foreach($map as $file)
        {
            if(!is_array($file) && substr(strrchr($file, '.'), 1) == 'php')
            {
                $filename = strtr($file, '', '.php');
                if ($fallback)
                {
                    $this->lang->load($filename, 'en-US');

                    $array = $this->lang->load($filename, $language, TRUE); //TODO: need to figure out how to do this now or if it's even necessary
                    foreach($array as $lang_key => $lang_value)
                    {
                        if ($lang_value !== '') {
                            $this->lang->language[$lang_key] = $lang_value;
                        }
                    }
                }
                else
                {
                    $this->lang->load($filename, $language);
                }
            }
        }
    }
}
?>
