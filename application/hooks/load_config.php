<?php
//Loads configuration from database into global CI config
function load_config()
{
    $CI =& get_instance();

    foreach($CI->Appconfig->get_all()->result() as $app_config)
    {	
        $CI->config->set_item($CI->security->xss_clean($app_config->key), $CI->security->xss_clean($app_config->value));
    }
    
    //Set language from config database
	$language = $CI->config->item('language');
	
    //Loads all the language files from the language directory
    if(!empty($language))
    {
		// fallback to English if language folder does not exist        
        if(!file_exists('./application/language/' . $language)) 
        {
        	$language = 'en';
        }

        $CI->config->set_item('language', $language);

        $map = directory_map('./application/language/' . $language);
        foreach($map as $file)
        {
            if(!is_array($file) && substr(strrchr($file,'.'), 1) == "php")
            {
                $CI->lang->load(strtr($file, '', '_lang.php'), $language);    
            }
        }
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

    bcscale($CI->config->item('currency_decimals') + $CI->config->item('tax_decimals'));
}
?>