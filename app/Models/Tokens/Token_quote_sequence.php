<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_quote_sequence class
 */

class Token_quote_sequence extends Token
{
	public function token_id()
	{
		return 'QSEQ';
	}

	public function get_value($save = TRUE)
	{
		return $this->CI->Appconfig->acquire_next_quote_sequence($save);
	}
}
?>
