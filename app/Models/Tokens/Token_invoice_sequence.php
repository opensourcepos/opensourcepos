<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_invoice_sequence class
 */

class Token_invoice_sequence extends Token
{

	public function __construct($value = '')
	{
		parent::__construct($value);
	}

	public function token_id()
	{
		return 'ISEQ';
	}

	public function get_value($save = TRUE)
	{
		return $this->CI->Appconfig->acquire_next_invoice_sequence($save);
	}
}
?>
