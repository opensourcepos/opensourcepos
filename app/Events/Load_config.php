<?php

namespace App\Events;

use app\Libraries\MY_Migration;

/**
 * @property my_migration migration;
 */
class Load_config
{
    /**
     * Loads configuration from database into global CI config
     */
    public function load_config()
    {
        $this->migration = new My_Migration();  //TODO: add the $config MigrationsConfig parameter

        if (!$this->migration->is_latest())
        {
            $this->session->sess_destroy();
        }

        foreach($this->Appconfig->get_all()->getResult() as $app_config)
        {
            $this->config->set_item($app_config->key, $app_config->value);
        }

        // fallback to English if language settings are not correct
        $file_exists = !file_exists('../application/language/' . current_language_code());
        if(current_language_code() == null || current_language() == null || $file_exists)
        {
            $this->config->set_item('language', 'english');
            $this->config->set_item('language_code', 'en-US');
        }

        $this->load_language_files('../vendor/codeigniter/framework/system/language', current_language(), FALSE);
        $this->load_language_files('../application/language', current_language_code(), TRUE);

        //Set timezone from config database
        if($this->config->get('timezone'))
        {
            date_default_timezone_set($this->config->get('timezone'));
        }
        else
        {
            date_default_timezone_set('America/New_York');
        }

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

                    $array = $this->lang->load($filename, $language, TRUE);
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
