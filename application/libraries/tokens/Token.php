<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'libraries/tokens/Token.php');
require_once(APPPATH . 'libraries/tokens/Token_customer.php');
require_once(APPPATH . 'libraries/tokens/Token_invoice_count.php');
require_once(APPPATH . 'libraries/tokens/Token_invoice_sequence.php');
require_once(APPPATH . 'libraries/tokens/Token_quote_sequence.php');
require_once(APPPATH . 'libraries/tokens/Token_suspended_invoice_count.php');
require_once(APPPATH . 'libraries/tokens/Token_year_invoice_count.php');

/**
 * Token class
 */

class Token
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	static function get_tokens()
	{
		return array(new Token_customer(), new Token_invoice_count(), new Token_invoice_sequence(),
			new Token_quote_sequence(), new Token_suspended_invoice_count(), new Token_quote_sequence(), new Token_year_invoice_count());
	}

	function matches($token_id)
	{
		return false;
	}

	public function replace($token_id)
	{
		foreach(Token::get_tokens() as $token)
		{
			if ($token->token_id() == $token_id)
			{
				return $token->get_value();
			}
		}
		return '';
	}


}
?>
