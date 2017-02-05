<?php

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 2/4/2017
 * Time: 4:01 PM
 */
class Token_invoice_count extends Token
{
	private static $token_code = 'CO';
	private $CI;

	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
		$this->CI->load->model('Sale');
	}

	public static function get_token_code()
	{
		return Token_invoice_count::$token_code;
	}

	public function get_value()
	{
		return $this->Sale->get_invoice_count();
	}
}