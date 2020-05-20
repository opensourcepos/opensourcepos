<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_year_quote_count class
 */

class Token_year_quote_count extends Token
{
	public function __construct()
	{
		parent::__construct();

		$this->CI->load->model('Sale');
	}

	public function token_id()
	{
		return 'QCO';
	}

	public function get_value()
	{
		return $this->CI->Sale->get_quote_number_for_year();
	}
}
?>
