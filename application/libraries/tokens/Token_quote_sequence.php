<?php

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 2/4/2017
 * Time: 4:01 PM
 */
class Token_quote_sequence extends Token
{
	private static $token_code = 'QSEQ';
	private $CI;

	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
	}

	public static function get_token_code()
	{
		return Token_quote_sequence::$token_code;
	}

	public function get_value()
	{
		return $this->CI->Appconfig->get_next_quote_sequence();
	}

}