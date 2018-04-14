<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Loads configuration from database into global CI config
 */
function load_config()
{
    $CI =& get_instance();

    foreach($CI->Appconfig->get_all()->result() as $app_config)
    {	
        $CI->config->set_item($CI->security->xss_clean($app_config->key), $CI->security->xss_clean($app_config->value));
    }
    
    //Loads all the language files from the language directory
    if(!empty(current_language()))
    {
        // fallback to English if language folder does not exist
        if(!file_exists('../application/language/' . current_language_code()))
        {
            $CI->config->set_item('language', 'english');
            $CI->config->set_item('language_code', 'en-US');
        }

        _load_language_files($CI, '../vendor/codeigniter/framework/system/language', current_language());
        _load_language_files($CI, '../application/language', current_language_code());
    }
    
    //Set timezone from config database
    if($CI->config->item('timezone'))
    {
        date_default_timezone_set($CI->config->item('timezone'));
    }
    else
    {
        date_default_timezone_set('America/New_York');
    }

    bcscale(max(2, totals_decimals() + tax_decimals()));
}

/**
 * @param $CI
 * @param $path
 * @param $language
 */
function _load_language_files($CI, $path, $language)
{
    $map = directory_map($path . DIRECTORY_SEPARATOR . $language);

    foreach($map as $file)
	{
        if(!is_array($file) && substr(strrchr($file, '.'), 1) == 'php')
		{
            $CI->lang->load(strtr($file, '', '_lang.php'), $language);
        }
    }
}

?>
