<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_customer class
 *
 * @link    github.com/jekkos/opensourcepos
 * @since   3.1
 * @author  SteveIreland
 */

class Token_customer extends Token
{
	public function __construct()
	{
		parent::__construct();

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
?>
