<?php

class Token_year_invoice_count extends Token
{
	private $CI;
	private static $token_id = 'YCO';

	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
		$this->CI->load->model('Sale');
	}

	public static function get_id()
	{
		return Token_year_invoice_count::$token_id;
	}

	public function get_value()
	{
		return $this->Sale->get_invoice_number_for_year();
	}
}