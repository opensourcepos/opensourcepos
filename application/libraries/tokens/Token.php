<?php

require_once(APPPATH . 'libraries/tokens/Token.php');
require_once(APPPATH . 'libraries/tokens/Token_customer.php');
require_once(APPPATH . 'libraries/tokens/Token_invoice_count.php');
require_once(APPPATH . 'libraries/tokens/Token_invoice_sequence.php');
require_once(APPPATH . 'libraries/tokens/Token_quote_sequence.php');
require_once(APPPATH . 'libraries/tokens/Token_suspended_invoice_count.php');
require_once(APPPATH . 'libraries/tokens/Token_year_invoice_count.php');

class Token
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function replace($token_id)
	{
		if($token_id == 'CU')
		{
			return (new Token_customer())->get_value();
		}
		elseif($token_id == 'CO')
		{
			return (new Token_invoice_count())->get_value();
		}
		elseif($token_id == 'ISEQ')
		{
			return (new Token_invoice_sequence())->get_value();
		}
		elseif($token_id == 'ISEQ')
		{
			return (new Token_invoice_sequence())->get_value();
		}
		elseif($token_id == 'QSEQ')
		{
			return (new Token_quote_sequence())->get_value();
		}
		elseif($token_id == 'SCO')
		{
			return (new Token_suspended_invoice_count())->get_value();
		}
		elseif($token_id == 'YCO')
		{
			return (new Token_year_invoice_count())->get_value();
		}
		return '';
	}

	function get_value(){
		return '';
	}
}

?>