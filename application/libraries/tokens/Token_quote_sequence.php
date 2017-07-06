<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_quote_sequence class
 */

class Token_quote_sequence extends Token
{
	public function get_value()
	{
		return $this->CI->Appconfig->acquire_save_next_quote_sequence();
	}
}
?>
