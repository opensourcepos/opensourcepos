<?php

namespace App\Models\Tokens;

use App\Libraries\Sale_lib;

use App\Models\Customer;

/**
 * Token_customer class
 **/
class Token_customer extends Token
{
	private string $customer_info;
	private Sale_lib $sale_lib;

	/**
	 * @param string $customer_info
	 */
	public function __construct(string $customer_info = '')
	{
		parent::__construct();
		$this->customer_info = $customer_info;
		$this->sale_lib = new Sale_lib();
	}

	/**
	 * @return string
	 */
	public function token_id(): string
	{
		return 'CU';
	}

	/**
	 * @return string
	 */
	public function get_value(): string
	{
		//substitute customer info
		$customer_id = $this->sale_lib->get_customer();
		if($customer_id != NEW_ITEM && empty($this->customer_info))
		{
			$customer = model(Customer::class);
			$customer_info = $customer->get_info($customer_id);

			if($customer_info != '')
			{
				return trim($customer_info->first_name . ' ' . $customer_info->last_name);
			}
		}

		return '';
	}
}
