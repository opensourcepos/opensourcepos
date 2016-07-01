<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Lang extends CI_Lang
{

	function __construct()
	{
		parent::__construct();
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
                $this->load(strtr($file, '', '_lang.php'));    
            }
        }
    }
    
	/**
     * Fetch a single line of text from the language array. Takes variable number
     * of arguments and supports wildcards in the form of '%1', '%2', etc.
     * Overloaded function.
     *
     * @access public
     * @return mixed false if not found or the language string
     */
	function line($line = '', $log_errors = true)
    {
        //get the arguments passed to the function
        $args = func_get_args();
        
        //count the number of arguments
        $c = count($args);
        
        //if one or more arguments, perform the necessary processing
        if ($c)
        {
            //first argument should be the actual language line key
            //so remove it from the array (pop from front)
            $line = array_shift($args);
            
            //check to make sure the key is valid and load the line
            if ($line == '')
            {
            	$line = FALSE;
            }
            else 
            {
            	if (isset($this->language[$line]))
            	{
            		$line = $this->language[$line];
            		//if the line exists and more function arguments remain
            		//perform wildcard replacements
            		if ($args)
            		{
            			$i = 1;
            			foreach ($args as $arg)
            			{
            				$line = preg_replace('/\%'.$i.'/', $arg, $line);
            				++$i;
            			}
            		}
            	}
            	else
            	{
            		// just return label name (with TBD)
            		$line = $this->line_tbd($line);
            		log_message('error', 'Could not find the language line "'.$line.'"');
            	}
            }
        }
        else
        {
            //if no arguments given, no language line available
            $line = false;
        }
        
        return $line;
    }
    
    function line_tbd($line='')
    {
    	return $line . ' (TBD)';
    }
    
}

?>
