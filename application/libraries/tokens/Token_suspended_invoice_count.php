<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_suspended_invoice_count class
 *
 * @link    github.com/jekkos/opensourcepos
 * @since   3.1
 * @author  SteveIreland
 */

class Token_suspended_invoice_count extends Token
{
	public function __construct()
	{
		parent::__construct();

		$this->CI->load->model('Sale');
	}

	public function get_value()
	{
		return $this->CI->Sale->get_suspended_invoice_count();
	}
}
?>
