<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tax library
 *
 * Library with utilities to manage taxes
 */

class Tax_lib
{
	const TAX_TYPE_SALES = 1;
	const TAX_TYPE_SALES_BY_INVOICE = 2;
	const TAX_TYPE_VAT = 0;

	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('sale_lib');
	}

	public function get_tax_types()
	{
		return array(
			TAX_TYPE_SALES => $this->CI->lang->line('taxes_sales_tax'),
			TAX_TYPE_SALES_BY_INVOICE => $this->CI->lang->line('taxes_sales_tax_by_invoice'),
			TAX_TYPE_VAT => $this->CI->lang->line('taxes_vat_tax')
		);
	}

	/*
	 * Compute the tax basis and returns the tax amount
	 */
	public function get_item_sales_tax($quantity, $price, $discount_percentage, $tax_percentage, $rounding_code)
	{
		$decimals = tax_decimals();

		// The tax basis should be returned at the currency scale
		$tax_basis = $this->CI->sale_lib->get_item_total($quantity, $price, $discount_percentage, TRUE);

		return $this->get_sales_tax_for_amount($tax_basis, $tax_percentage, $rounding_code, $decimals);
	}

	/*
	 * Computes the item level sales tax amount for a given tax basis
	 */
	public function get_sales_tax_for_amount($tax_basis, $tax_percentage, $rounding_mode, $decimals)
	{
		$tax_fraction = bcdiv($tax_percentage, 100);

		$tax_amount = bcmul($tax_basis, $tax_fraction);
		$rounded_tax_amount = $tax_amount;

		return Rounding_mode::round_number($rounding_mode, $tax_amount, $decimals);
	}

	/*
 	* Updates the sales_tax array which is later saved to the `sales_taxes` table and used for printing taxes on receipts and invoices
 	*/
	public function update_sales_taxes(&$sales_taxes, $tax_type, $tax_group, $tax_rate, $tax_basis, $item_tax_amount, $tax_group_sequence, $rounding_code, $sale_id, $name='', $tax_code='')
	{
		$tax_group_index = $this->clean('X'.$tax_group);

		if(!array_key_exists($tax_group_index, $sales_taxes))
		{
			$insertkey = $tax_group_index;

			$sales_tax = array($insertkey => array(
				'sale_id' => $sale_id,
				'tax_type' => $tax_type,
				'tax_group' => $tax_group,
				'sale_tax_basis' => $tax_basis,
				'sale_tax_amount' => $item_tax_amount,
				'print_sequence' => $tax_group_sequence,
				'name' => $name,
				'tax_rate' => $tax_rate,
				'sales_tax_code' => $tax_code,
				'rounding_code' => $rounding_code
			));

			//add to existing array
			$sales_taxes += $sales_tax;
		}
		else
		{
			// Important ... the sales amounts are accumulated for the group at the maximum configurable scale value of 4
			// but the scale will in reality be the scale specified by the tax_decimal configuration value  used for sales_items_taxes
			$sales_taxes[$tax_group_index]['sale_tax_basis'] = bcadd($sales_taxes[$tax_group_index]['sale_tax_basis'], $tax_basis, 4);
			$sales_taxes[$tax_group_index]['sale_tax_amount'] = bcadd($sales_taxes[$tax_group_index]['sale_tax_amount'], $item_tax_amount, 4);
		}
	}

	/*
	* If invoice taxing (as opposed to invoice_item_taxing) rules apply then recalculate the sales tax after tax group totals are final
	*/
	public function apply_invoice_taxing(&$sales_taxes)
	{
		if(!empty($sales_taxes))
		{
			$sort = array();
			foreach($sales_taxes as $k => $v)
			{
				$sort['print_sequence'][$k] = $v['print_sequence'];
			}
			array_multisort($sort['print_sequence'], SORT_ASC, $sales_taxes);
		}

		$decimals = totals_decimals();

		foreach($sales_taxes as $row_number => $sales_tax)
		{
			$sales_taxes[$row_number]['sale_tax_amount'] = $this->get_sales_tax_for_amount($sales_tax['sale_tax_basis'], $sales_tax['tax_rate'], $sales_tax['rounding_code'], $decimals);
		}
	}

	/*
	 * Apply rounding rules to the accumulated sales tax amounts
	 */
	public function round_sales_taxes(&$sales_taxes)
	{
		if(!empty($sales_taxes))
		{
			$sort = array();
			foreach($sales_taxes as $k=>$v)
			{
				$sort['print_sequence'][$k] = $v['print_sequence'];
			}
			array_multisort($sort['print_sequence'], SORT_ASC, $sales_taxes);
		}

		$decimals = totals_decimals();

		foreach($sales_taxes as $row_number => $sales_tax)
		{
			$sale_tax_amount = $sales_tax['sale_tax_amount'];
			$rounding_code = $sales_tax['rounding_code'];
			$rounded_sale_tax_amount = $sale_tax_amount;

			if ($rounding_code == Rounding_mode::HALF_UP)
			{
				$rounded_sale_tax_amount = round($sale_tax_amount, $decimals, PHP_ROUND_HALF_UP);
			}
			elseif($rounding_code == Rounding_mode::HALF_DOWN)
			{
				$rounded_sale_tax_amount = round($sale_tax_amount, $decimals, PHP_ROUND_HALF_DOWN);
			}
			elseif($rounding_code == Rounding_mode::HALF_EVEN)
			{
				$rounded_sale_tax_amount = round($sale_tax_amount, $decimals, PHP_ROUND_HALF_EVEN);
			}
			elseif($rounding_code == Rounding_mode::HALF_ODD)
			{
				$rounded_sale_tax_amount = round($sale_tax_amount, $decimals, PHP_ROUND_HALF_UP);
			}
			elseif($rounding_code == Rounding_mode::ROUND_UP)
			{
				$fig = (int) str_pad('1', $decimals, '0');
				$rounded_sale_tax_amount = (ceil($sale_tax_amount * $fig) / $fig);
			}
			elseif($rounding_code == Rounding_mode::ROUND_DOWN)
			{
				$fig = (int) str_pad('1', $decimals, '0');
				$rounded_sale_tax_amount = (floor($sale_tax_amount * $fig) / $fig);
			}
			elseif($rounding_code == Rounding_mode::HALF_FIVE)
			{
				$rounded_sale_tax_amount = round($sale_tax_amount / 5) * 5;
			}

			$sales_taxes[$row_number]['sale_tax_amount'] = $rounded_sale_tax_amount;
		}
	}


	/**
	 * Determine the applicable tax code and then determine the tax amount to be applied.
	 * If a tax amount was identified then accumulate into the sales_taxes array
	 */
	public function apply_sales_tax(&$item, &$city, &$state, &$sales_tax_code, $register_mode, $sale_id, &$sales_taxes)
	{
		$tax_code = $this->get_applicable_tax_code($register_mode, $city, $state, $sales_tax_code);

		// If tax code cannot be determined or the price is zero then skip this item
		if($tax_code != '' && $item['price'] != 0)
		{
			$tax_rate = 0.0000;
			$rounding_code = Rounding_mode::HALF_UP;

			$tax_code_obj = $this->CI->Tax->get_info($tax_code);
			$tax_category_id = $item['tax_category_id'];

			if($tax_category_id != 0)
			{
				$tax_rate_info = $this->CI->Tax->get_rate_info($tax_code, $tax_category_id);
				if($tax_rate_info)
				{
					$tax_rate = $tax_rate_info->tax_rate;
					$rounding_code = $tax_rate_info->rounding_code;
				}
				else
				{
					$tax_rate = $tax_code_obj->tax_rate;
					$rounding_code = $tax_code_obj->rounding_code;
				}
			}

			if($tax_category_id != 0)
			{
				$tax_rate_info = $this->CI->Tax->get_rate_info($tax_code, $tax_category_id);
				$tax_rate = $tax_rate_info->tax_rate;
				$rounding_code = $tax_rate_info->rounding_code;
				$tax_group_sequence = $tax_rate_info->tax_group_sequence;
				$tax_category = $tax_rate_info->tax_category;
			}
			else
			{
				$tax_rate = $tax_code_obj->tax_rate;
				$rounding_code = $tax_code_obj->rounding_code;
				$tax_group_sequence = $tax_code_obj->tax_group_sequence;
				$tax_category = $tax_code_obj->tax_category;
			}

			$decimals = tax_decimals();

			// The tax basis should be returned at the currency scale
			$tax_basis = $this->CI->sale_lib->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE);
			$tax_amount = $this->get_sales_tax_for_amount($tax_basis, $tax_rate, $rounding_code, $decimals);

			$tax_group = (float)$tax_rate . '% ' . $tax_category;
			$tax_type = Tax_lib::TAX_TYPE_SALES;

			if($tax_amount != 0)
			{
				$this->update_sales_taxes($sales_taxes, $tax_type, $tax_group, $tax_rate, $tax_basis, $tax_amount, $tax_group_sequence, $rounding_code, $sale_id, $tax_category, $tax_code);
			}

			// input : register_mode
			// input : city
			// input : state
			// input : sales_tax_code
			// input : $item['price']
			// input : $item['tax_category_id']
			// input : $item['quantity']
			// input : $item['price']
			// input : $item['discount']
			// both : $sales_taxes
			// output : tax_details['tax_rate']
			// output : tax_details['rounding_code']
			// output : tax_details['tax_group_sequence']
			// output : tax_details['tax_code']

			$tax_details = array('item_tax_amount' => $tax_amount, 'tax_group' => $tax_group, 'tax_name' => $tax_category, 'tax_rate' => $tax_rate, 'rounding_code' => $rounding_code, 'tax_group_sequence' => $tax_group_sequence, 'tax_code' => $tax_code);

			return $tax_details;
		}
		else
		{
			$tax_details = array('item_tax_amount' => 0.0000, 'tax_group' => '', 'tax_name' => '', 'tax_rate' => 0.0000, 'rounding_code' => '0', 'tax_group_sequence' => '0', 'tax_code' => '');
			return $tax_details;
		}
	}

	public function get_applicable_tax_code($register_mode, $city, $state, $sales_tax_code)
	{
		if($register_mode == "sale")
		{
			$tax_code = $this->CI->config->config['default_origin_tax_code']; // overrides customer assigned code
		}
		else
		{
			if($sales_tax_code == '')
			{
				$tax_code = $this->CI->Tax->get_sales_tax_code($city, $state);
			}
			else
			{
				// Use the customer assigned tax rate code
				$tax_code = $sales_tax_code;
			}
		}

		return $tax_code;
	}

	public function clean($string)
	{
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

		return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}
}
?>
