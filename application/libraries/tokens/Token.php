<?php

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 2/4/2017
 * Time: 4:01 PM
 */
abstract class Token
{
	public static function get_token($token_id)
	{
		if ($token_id == Token_customer::get_token_code())
		{
			return new Token_customer();
		}
		elseif ($token_id == Token_invoice_count::get_token_code())
		{
			return new Token_invoice_count();
		}
		elseif ($token_id == Token_invoice_sequence::get_token_code())
		{
			return new Token_invoice_sequence();
		}
		elseif ($token_id == Token_invoice_sequence::get_token_code())
		{
			return new Token_invoice_sequence();
		}
		elseif ($token_id == Token_quote_sequence::get_token_code())
		{
			return new Token_quote_sequence();
		}
		elseif ($token_id == Token_suspended_invoice_count::get_token_code())
		{
			return new Token_suspended_invoice_count();
		}
		elseif ($token_id == Token_year_invoice_count::get_token_code())
		{
			return new Token_year_invoice_count();
		}
	}

	public function __construct()
	{
	}

	abstract function get_value();

}