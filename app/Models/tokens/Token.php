<?php

namespace App\Models\tokens;

use CodeIgniter\Model;

require_once(APPPATH . 'Models/tokens/Token.php');
require_once(APPPATH . 'Models/tokens/Token_customer.php');
require_once(APPPATH . 'Models/tokens/Token_invoice_count.php');
require_once(APPPATH . 'Models/tokens/Token_invoice_sequence.php');
require_once(APPPATH . 'Models/tokens/Token_quote_sequence.php');
require_once(APPPATH . 'Models/tokens/Token_year_quote_count.php');
require_once(APPPATH . 'Models/tokens/Token_work_order_sequence.php');
require_once(APPPATH . 'Models/tokens/Token_suspended_invoice_count.php');
require_once(APPPATH . 'Models/tokens/Token_year_invoice_count.php');
require_once(APPPATH . 'Models/tokens/Token_barcode_price.php');
require_once(APPPATH . 'Models/tokens/Token_barcode_weight.php');
require_once(APPPATH . 'Models/tokens/Token_barcode_ean.php');

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
		return array(new Token_barcode_price(), new Token_barcode_weight(), new Token_barcode_ean());
	}

	static function get_tokens()
	{
		return array(new Token_customer(), new Token_invoice_count(), new Token_invoice_sequence(),
			new Token_quote_sequence(), new Token_suspended_invoice_count(), new Token_quote_sequence(),
			new Token_work_order_sequence(), new Token_year_invoice_count(), new Token_year_quote_count());
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
