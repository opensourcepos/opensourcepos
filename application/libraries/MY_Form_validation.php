<?php 

class MY_Form_validation extends CI_Form_validation
{
 	function MY_Form_validation($rules = array())
    {
        parent::__construct($rules);
    }
    
    function get_error_message() 
    {
    	return $this->error_string;
    }
    
    function get_error_messages() 
    {
    	return $this->_error_array;
    }
	
}

?>