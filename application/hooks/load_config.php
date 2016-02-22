<?php
//Loads configuration from database into global CI config
function load_config()
{
    $CI =& get_instance();
    foreach( $CI->Appconfig->get_all()->result() as $app_config )
    {
        $CI->config->set_item( $app_config->key, $app_config->value );
    }
    
    //Set language from config database
    //Loads all the language files from the language directory
    
    if ( $CI->config->item( 'language' ) )
    {
        $CI->config->set_item( 'language', $CI->config->item( 'language' ) );
        
        $map = directory_map('./application/language/' . $CI->config->item( 'language' ));

        foreach($map as $file)
        {
            if ( substr(strrchr($file,'.'),1) == "php")
            {
                $CI->lang->load( str_replace( '_lang.php', '', $file ) );    
            }
        }
    }
    
    //Set timezone from config database
    if ( $CI->config->item( 'timezone' ) )
    {
        date_default_timezone_set( $CI->config->item( 'timezone' ) );
    }
    else
    {
        date_default_timezone_set( 'America/New_York' );
    }
}
?>