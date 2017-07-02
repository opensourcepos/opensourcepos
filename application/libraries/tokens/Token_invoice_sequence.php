<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_invoice_sequence class
 *
 * @link    github.com/jekkos/opensourcepos
 * @since   3.1
 * @author  SteveIreland
 */

class Token_invoice_sequence extends Token
{
	public function get_value()
	{
		return $this->CI->Appconfig->acquire_save_next_invoice_sequence();
	}
}
?>
