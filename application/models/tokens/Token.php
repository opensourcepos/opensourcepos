<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'models/tokens/Token.php');
require_once(APPPATH . 'models/tokens/Token_customer.php');
require_once(APPPATH . 'models/tokens/Token_invoice_count.php');
require_once(APPPATH . 'models/tokens/Token_invoice_sequence.php');
require_once(APPPATH . 'models/tokens/Token_quote_sequence.php');
require_once(APPPATH . 'models/tokens/Token_work_order_sequence.php');
require_once(APPPATH . 'models/tokens/Token_suspended_invoice_count.php');
require_once(APPPATH . 'models/tokens/Token_year_invoice_count.php');

/**
 * Token class
 */

abstract class Token
{
	protected $CI;

	protected $value = '';

	public function __construct($value = '')
	{
		$this->CI =& get_instance();
		$this->value = $value;
	}

	static function get_barcode_tokens()
	{
		return array();
	}

	static function get_tokens()
	{
		return array(new Token_customer(), new Token_invoice_count(), new Token_invoice_sequence(),
			new Token_quote_sequence(), new Token_suspended_invoice_count(), new Token_quote_sequence(),
			new Token_work_order_sequence(), new Token_year_invoice_count());
	}

	abstract public function token_id();

	abstract public function get_value();

	function matches($token_id)
	{
		return token_id() == $token_id;
	}

	function replace($text)
	{
		if(strstr($text, $this->token_id()))
		{
			return str_replace($this->token_id(), $this->get_value(), $text);
		}
		return $text;
	}

}
?>
