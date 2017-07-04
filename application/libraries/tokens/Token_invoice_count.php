<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_invoice_count class
 */

class Token_invoice_count extends Token
{
	public function __construct()
	{
		parent::__construct();

		$this->CI->load->model('Sale');
	}

	public function token_id()
	{
		return 'CO';
	}

	public function get_value()
	{
		return $this->CI->Sale->get_invoice_count();
	}
}
?>
