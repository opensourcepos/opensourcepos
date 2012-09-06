<?php
//Loads configuration from database into global CI config
function load_config()
{
	$CI =& get_instance();
	foreach($CI->Appconfig->get_all()->result() as $app_config)
	{
		$CI->config->set_item($app_config->key,$app_config->value);
	}
	
	if ($CI->config->item('language'))
	{
		$CI->config->set_item( 'language', $CI->config->item( 'language' ) );
    $loaded = $CI->lang->is_loaded;
    $CI->lang->is_loaded = array();

    foreach($loaded as $file)
    {
        $CI->lang->load( str_replace( '_lang.php', '', $file ) );    
    }
	}
	
	if ($CI->config->item('timezone'))
	{
		date_default_timezone_set($CI->config->item('timezone'));
	}
	else
	{
		date_default_timezone_set('America/New_York');
	}
}
?>