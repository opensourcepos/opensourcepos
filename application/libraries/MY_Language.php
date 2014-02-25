<?php
class MY_Language extends Lang
{
    function MY_Language()
    {
        parent::Lang();
    }
    
    function switch_to( $idiom )
    {
        $CI =& get_instance();
        if( is_string( $idiom ) )
        {
            $CI->config->set_item( 'language', $idiom );
            $loaded = $this->is_loaded;
            $this->is_loaded = array();
                
            foreach($loaded as $file)
            {
                $this->load( str_replace( '_lang.php', '', $file ) );    
            }
        }
    }
}

?>
