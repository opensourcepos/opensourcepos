<?php

class Token_invoice_sequence extends Token
{
	private static $token_code = 'ISEQ';
	private $CI;

	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
	}

	public static function get_token_code()
	{
		return Token_invoice_sequence::$token_code;
	}

	public function get_value()
	{
		return $this->CI->Appconfig->acquire_save_next_invoice_sequence();
	}
}