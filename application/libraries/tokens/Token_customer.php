<?php

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 2/4/2017
 * Time: 7:57 PM
 */
class Token_customer extends Token
{
	private static $token_code = 'CU';
	private $CI;

	public static function get_token_code()
	{
		return Token_customer::$token_code;
	}
	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
		$this->CI->load->library('sale_lib');
	}

	public function get_value()
	{
		// substitute customer info
		$customer_id = $this->sale_lib->get_customer();
		if($customer_id != -1)
		{
			$customer_info = $this->Customer->get_info($customer_id);
			if($customer_info != '')
			{
				return trim($customer_info->first_name . ' ' . $customer_info->last_name);
			}
		}

		return 'Customer Unknown';

	}

}