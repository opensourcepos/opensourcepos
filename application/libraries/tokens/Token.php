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

	public static function get_token($token_id)
	{
		if($token_id == Token_customer::get_token_code())
		{
			return new Token_customer();
		}
		elseif($token_id == Token_invoice_count::get_token_code())
		{
			return new Token_invoice_count();
		}
		elseif($token_id == Token_invoice_sequence::get_token_code())
		{
			return new Token_invoice_sequence();
		}
		elseif($token_id == Token_invoice_sequence::get_token_code())
		{
			return new Token_invoice_sequence();
		}
		elseif($token_id == Token_quote_sequence::get_token_code())
		{
			return new Token_quote_sequence();
		}
		elseif($token_id == Token_suspended_invoice_count::get_token_code())
		{
			return new Token_suspended_invoice_count();
		}
		elseif($token_id == Token_year_invoice_count::get_token_code())
		{
			return new Token_year_invoice_count();
		}
	}

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	function get_value(){
	}

}