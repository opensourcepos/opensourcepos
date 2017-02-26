<?php

class Token_customer extends Token
{
	private $CI;

	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
		$this->CI->load->library('sale_lib');
	}

	public function get_value()
	{
		// substitute customer info
		$customer_id = $this->CI->sale_lib->get_customer();
		if($customer_id != -1)
		{
			$customer_info = $this->CI->Customer->get_info($customer_id);
			if($customer_info != '')
			{
				return trim($customer_info->first_name . ' ' . $customer_info->last_name);
			}
		}

		return '';

	}

}