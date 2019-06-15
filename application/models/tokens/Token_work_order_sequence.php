<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_work_order_sequence class
 */

class Token_work_order_sequence extends Token
{
	public function token_id()
	{
		return 'WSEQ';
	}

	public function get_value()
	{
		return $this->CI->Appconfig->acquire_save_next_work_order_sequence();
	}
}
?>
