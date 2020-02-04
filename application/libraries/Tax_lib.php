<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tax library
 *
 * Library with utilities to manage taxes
 */

class Tax_lib
{
	const TAX_TYPE_EXCLUDED = 1;
	const TAX_TYPE_INCLUDED = 0;

	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function get_tax_types()
	{
		return array(
			Tax_lib::TAX_TYPE_EXCLUDED => $this->CI->lang->line('taxes_tax_excluded'),
			Tax_lib::TAX_TYPE_INCLUDED => $this->CI->lang->line('taxes_tax_included')
		);
	}

	/*
	 * Compute the tax basis and returns the tax amount
	 */
	public function get_item_sales_tax($quantity, $price, $discount, $discount_type, $tax_percentage, $rounding_code)
	{
		$decimals = tax_decimals();

		// The tax basis should be returned at the currency scale
		$tax_basis = $this->CI->sale_lib->get_item_total($quantity, $price, $discount, $discount_type, TRUE);

		return $this->get_tax_for_amount($tax_basis, $tax_percentage, $rounding_code, $decimals);
	}

	/*
	 * Computes the item level sales tax amount for a given tax basis
	 */
	public function get_tax_for_amount($tax_basis, $tax_percentage, $rounding_mode, $decimals)
	{
		$tax_amount = bcmul($tax_basis, bcdiv($tax_percentage, 100));

		return Rounding_mode::round_number($rounding_mode, $tax_amount, $decimals);
	}

	/**
	 * Compute taxes for all items in the cart
	 */
	public function get_taxes(&$cart)
	{
		$register_mode = $this->CI->sale_lib->get_mode();
		$tax_decimals = tax_decimals();
		$customer_id = $this->CI->sale_lib->get_customer();
		$customer_info = $this->CI->Customer->get_info($customer_id);
		$taxes = array();
		$item_taxes = array();

		// Charge sales tax if customer is not selected (walk-in) or customer is flagged as taxable
		if($customer_id == -1 || $customer_info->taxable)
		{
			foreach($cart as $line => $item)
			{
				$taxed = FALSE;

				if(!($this->CI->config->item('use_destination_based_tax')))
				{
					// Start of current Base System tax calculations

					$tax_info = $this->CI->Item_taxes->get_info($item['item_id']);
					$tax_group_sequence = 0;
					$cascade_level = 0;
					$cascade_basis_level = 0;

					foreach($tax_info as $tax)
					{
						// This computes tax for each line item and adds it to the tax type total
						$tax_basis = $this->CI->sale_lib->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE);
						$tax_amount = 0.0;

						if($this->CI->config->item('tax_included'))
						{
							$tax_type = Tax_lib::TAX_TYPE_INCLUDED;
							$tax_amount = $this->get_included_tax($item['quantity'], $item['price'], $item['discount'], $item['discount_type'], $tax['percent'], $tax_decimals, Rounding_mode::HALF_UP);
						}
						else
						{
							$tax_type = Tax_lib::TAX_TYPE_EXCLUDED;
							$tax_amount = $this->get_tax_for_amount($tax_basis, $tax['percent'], Rounding_mode::HALF_UP, $tax_decimals);
						}

						if($tax_amount <> 0)
						{
							$tax_group_sequence++;
							$this->update_taxes($taxes, $tax_type, $tax['name'], $tax['percent'], $tax_basis, $tax_amount, $tax_group_sequence, Rounding_mode::HALF_UP, -1, $tax['name']);
							$tax_group_sequence += 1;
							$taxed = TRUE;
						}
						$items_taxes_detail = array();
						$items_taxes_detail['item_id'] = $item['item_id'];
						$items_taxes_detail['line'] = $item['line'];
						$items_taxes_detail['name'] = $tax['name'];
						$items_taxes_detail['percent'] = $tax['percent'];
						$items_taxes_detail['tax_type'] = $tax_type;
						$items_taxes_detail['rounding_code'] = Rounding_mode::HALF_UP;
						$items_taxes_detail['cascade_sequence'] = 0;
						$items_taxes_detail['item_tax_amount'] = $tax_amount;
						$items_taxes_detail['sales_tax_code_id'] = NULL;
						$items_taxes_detail['jurisdiction_id'] = NULL;
						$items_taxes_detail['tax_category_id'] = NULL;

						$item_taxes[] = $items_taxes_detail;
					}
				}
				else
				{
					// Start of destination based tax calculations

					if($item['tax_category_id'] == NULL)
					{
						$item['tax_category_id'] = $this->CI->config->config['default_tax_category'];
					}

					$taxed = $this->apply_destination_tax($item, $customer_info->city, $customer_info->state, $customer_info->sales_tax_code_id, $register_mode, 0, $taxes, $item_taxes, $item['line']);
				}

				if($taxed)
				{
					$cart[$line]['taxed_flag'] = $this->CI->lang->line('sales_taxed_ind');
				}
				else
				{
					$cart[$line]['taxed_flag'] = $this->CI->lang->line('sales_nontaxed_ind');
				}
			}
			$this->round_taxes($taxes);
		}

		$tax_details = array();
		$tax_details[0] = $taxes;
		$tax_details[1] = $item_taxes;

		return $tax_details;
	}

	public function get_included_tax($quantity, $price, $discount_percentage, $discount_type, $tax_percentage, $tax_decimal, $rounding_code)
	{
		$tax_amount = $this->CI->sale_lib->get_item_tax($quantity, $price, $discount_percentage, $discount_type, $tax_percentage);

		return Rounding_mode::round_number($rounding_code, $tax_amount, $tax_decimal);
	}

	/*
	* Updates the sales_tax array which is later saved to the `sales_taxes` table and used for printing taxes on receipts and invoices
	*/
	public function update_taxes(&$taxes, $tax_type, $tax_group, $tax_rate, $tax_basis, $item_tax_amount, $tax_group_sequence, $rounding_code, $sale_id, $name = '', $tax_code_id = NULL, $jurisdiction_id = NULL,$tax_category_id = NULL  )
	{
		$tax_group_index = $this->clean('X' . (float)$tax_rate . '% ' . $tax_group);

		if(!array_key_exists($tax_group_index, $taxes))
		{
			$insertkey = $tax_group_index;

			$tax = array($insertkey => array(
				'sale_id' => $sale_id,
				'tax_type' => $tax_type,
				'tax_group' => $tax_group,
				'sale_tax_basis' => $tax_basis,
				'sale_tax_amount' => $item_tax_amount,
				'print_sequence' => $tax_group_sequence,
				'name' => $name,
				'tax_rate' => $tax_rate,
				'sales_tax_code_id' => $tax_code_id,
				'jurisdiction_id' => $jurisdiction_id,
				'tax_category_id' => $tax_category_id,
				'rounding_code' => $rounding_code
			));

			//add to existing array
			$taxes += $tax;
		}
		else
		{
			// Important ... the sales amounts are accumulated for the group at the maximum configurable scale value of 4
			// but the scale will in reality be the scale specified by the tax_decimal configuration value  used for sales_items_taxes
			$taxes[$tax_group_index]['sale_tax_basis'] = bcadd($taxes[$tax_group_index]['sale_tax_basis'], $tax_basis, 4);
			$taxes[$tax_group_index]['sale_tax_amount'] = bcadd($taxes[$tax_group_index]['sale_tax_amount'], $item_tax_amount, 4);
		}
	}

	/*
	* If invoice taxing (as opposed to invoice_item_taxing) rules apply then recalculate the sales tax after tax group totals are final
	* This is currently used ONLY for the original sales tax migration.
	*/
	public function apply_invoice_taxing(&$taxes)
	{
		if(!empty($taxes))
		{
			$sort = array();
			foreach($taxes as $k => $v)
			{
				$sort['print_sequence'][$k] = $v['print_sequence'];
			}
			array_multisort($sort['print_sequence'], SORT_ASC, $taxes);
		}

		$decimals = totals_decimals();

		foreach($taxes as $row_number => $tax)
		{
			$taxes[$row_number]['sale_tax_amount'] = $this->get_tax_for_amount($tax['sale_tax_basis'], $tax['tax_rate'], $tax['rounding_code'], $decimals);
		}
	}

	/*
	 * Apply rounding rules to the accumulated sales tax amounts
	 */
	public function round_taxes(&$taxes)
	{
		if(!empty($taxes))
		{
			$sort = array();
			foreach($taxes as $k => $v)
			{
				$sort['print_sequence'][$k] = $v['print_sequence'];
			}
			array_multisort($sort['print_sequence'], SORT_ASC, $taxes);
		}

		// If tax included then round decimal to tax decimals, otherwise round it to currency_decimals
		if($this->CI->config->item('tax_included'))
		{
			$decimals = tax_decimals();
		}
		else
		{
			$decimals = totals_decimals();
		}

		foreach($taxes as $row_number => $sales_tax)
		{
			$tax_amount = $sales_tax['sale_tax_amount'];
			$rounding_code = $sales_tax['rounding_code'];
			$rounded_tax_amount = $tax_amount;

			if($rounding_code == Rounding_mode::HALF_UP)
			{
				$rounded_tax_amount = round($tax_amount, $decimals, PHP_ROUND_HALF_UP);
			}
			elseif($rounding_code == Rounding_mode::HALF_DOWN)
			{
				$rounded_tax_amount = round($tax_amount, $decimals, PHP_ROUND_HALF_DOWN);
			}
			elseif($rounding_code == Rounding_mode::HALF_EVEN)
			{
				$rounded_tax_amount = round($tax_amount, $decimals, PHP_ROUND_HALF_EVEN);
			}
			elseif($rounding_code == Rounding_mode::HALF_ODD)
			{
				$rounded_tax_amount = round($tax_amount, $decimals, PHP_ROUND_HALF_UP);
			}
			elseif($rounding_code == Rounding_mode::ROUND_UP)
			{
				$fig = (int)str_pad('1', $decimals, '0');
				$rounded_tax_amount = ceil($tax_amount * $fig) / $fig;
			}
			elseif($rounding_code == Rounding_mode::ROUND_DOWN)
			{
				$fig = (int)str_pad('1', $decimals, '0');
				$rounded_tax_amount = floor($tax_amount * $fig) / $fig;
			}
			elseif($rounding_code == Rounding_mode::HALF_FIVE)
			{
				$rounded_tax_amount = round($tax_amount / 5) * 5;
			}

			$taxes[$row_number]['sale_tax_amount'] = $rounded_tax_amount;
		}
	}

	/**
	 * Determine the applicable tax code and then determine the tax amount to be applied.
	 * If a tax amount was identified then accumulate into the sales_taxes array
	 */
	public function apply_destination_tax(&$item, $city, $state, $sales_tax_code_id, $register_mode, $sale_id, &$taxes, &$item_taxes, $line)
	{
		$taxed = FALSE;

		$tax_code_id = $this->get_applicable_tax_code($register_mode, $city, $state, $sales_tax_code_id);

		// If tax code cannot be determined or the price is zero then skip this item
		if($tax_code_id != -1 && $item['price'] != 0)
		{
			$tax_decimals = tax_decimals();

			$tax_definition = $this->CI->Tax->get_taxes($tax_code_id, $item['tax_category_id']);

			// The tax basis should be returned at the currency scale
			$tax_basis = $this->CI->sale_lib->get_item_total($item['quantity'], $item['price'], $item['discount'], $item['discount_type'], TRUE);

			$row = 0;

			$last_cascade_sequence = 0;
			$cascade_tax_amount = 0.0;

			foreach($tax_definition as $tax)
			{
				$cascade_sequence = $tax['cascade_sequence'];
				if($cascade_sequence != $last_cascade_sequence)
				{
					$last_cascade_sequence = $cascade_sequence;
					$tax_basis = $tax_basis + $cascade_tax_amount;
				}

				$tax_rate = $tax['tax_rate'];
				$rounding_code = $tax['tax_rounding_code'];

				// This computes tax for each line item and adds it to the tax type total

				$tax_type = $tax['tax_type'];

				if($tax_type == Tax_lib::TAX_TYPE_INCLUDED)
				{
					$tax_amount = $this->get_included_tax($item['quantity'], $item['price'], $item['discount'], $item['discount_type'], $tax_rate, $tax_decimals, $rounding_code);
				}
				else
				{
					$tax_amount = $this->get_tax_for_amount($tax_basis, $tax_rate, $rounding_code, $tax_decimals);
					$cascade_tax_amount = $cascade_tax_amount + $tax_amount;
				}

				if($tax_amount != 0)
				{
					$taxed = TRUE;
					$this->update_taxes($taxes, $tax_type, $tax['tax_group'], $tax_rate, $tax_basis, $tax_amount, $tax['tax_group_sequence'], $rounding_code, $sale_id, $tax['tax_group'], $tax_code_id, $tax['rate_jurisdiction_id'], $item['tax_category_id']);
				}

				$item_taxes_detail = array();
				$item_taxes_detail['line'] = $line;
				$item_taxes_detail['item_id'] = $item['item_id'];
				$item_taxes_detail['name'] = $tax['tax_group'];
				$item_taxes_detail['percent'] = $tax['tax_rate'];
				$item_taxes_detail['tax_type'] = $tax_type;
				$item_taxes_detail['rounding_code'] = $rounding_code;
				$item_taxes_detail['cascade_sequence'] = $cascade_sequence;
				$item_taxes_detail['item_tax_amount'] = $tax_amount;
				$item_taxes_detail['sales_tax_code_id'] = $tax_code_id;
				$item_taxes_detail['jurisdiction_id'] = $tax['rate_jurisdiction_id'];
				$item_taxes_detail['tax_category_id'] = $tax['rate_tax_category_id'];
				$item_taxes_detail['tax_group_sequence'] = $tax['tax_group_sequence'];

				$item_taxes[] = $item_taxes_detail;
			}
		}

		return $taxed;
	}

	public function get_applicable_tax_code($register_mode, $city, $state, $sales_tax_code_id)
	{
		if($register_mode == "sale")
		{
			$sales_tax_code_id = $this->CI->config->config['default_tax_code']; // overrides customer assigned code
		}
		else
		{
			if($sales_tax_code_id == NULL || $sales_tax_code_id == 0)
			{
				$sales_tax_code_id = $this->CI->Tax_code->get_sales_tax_code($city, $state);

				if($sales_tax_code_id == NULL || $sales_tax_code_id == 0)
				{
					$sales_tax_code_id = $this->CI->config->config['default_tax_code']; // overrides customer assigned code
				}
			}
		}

		return $sales_tax_code_id;
	}

	public function clean($string)
	{
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

		return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}

	public function get_tax_code_options()
	{
		$tax_codes = $this->CI->Tax_code->get_all()->result_array();
		$tax_code_options = array();
		$tax_code_options[''] = '';
		foreach($tax_codes as $tax_code)
		{
			$a = $tax_code['tax_code_id'];
			$b = $tax_code['tax_code_name'];
			$tax_code_options[$a] = $b;
		}

		return $tax_code_options;
	}

	public function get_tax_jurisdiction_options()
	{
		$tax_jurisdictions = $this->CI->Tax_jurisdiction->get_all()->result_array();
		$tax_jurisdiction_options = array();
		$tax_jurisdiction_options[0] = '';
		foreach($tax_jurisdictions as $tax_jurisdiction)
		{
			$a = $tax_jurisdiction['jurisdiction_id'];
			$b = $tax_jurisdiction['jurisdiction_name'];
			$tax_jurisdiction_options[$a] = $b;
		}

		return $tax_jurisdiction_options;
	}

	public function get_tax_category_options()
	{
		$tax_categories = $this->CI->Tax_category->get_all()->result_array();
		$tax_category_options = array();
		$tax_category_options[0] = '';
		foreach($tax_categories as $tax_category)
		{
			$a = $tax_category['tax_category_id'];
			$b = $tax_category['tax_category'];

			$tax_category_options[$a] = $b;
		}

		return $tax_category_options;
	}

	public function get_tax_type_options($selected_tax_type)
	{
		$selected = 'selected=\"selected\" ';

		$s1 = '';
		$s2 = '';

		if($selected_tax_type == Tax_lib::TAX_TYPE_EXCLUDED)
		{
			$s1 = $selected;
		}
		else if($selected_tax_type == Tax_lib::TAX_TYPE_INCLUDED)
		{
			$s2 = $selected;
		}

		return '<option value=\"' . Tax_lib::TAX_TYPE_EXCLUDED . '\" ' . $s1 . '> ' . $this->CI->lang->line('taxes_sales_tax')
			. '</option><option value=\"' . Tax_lib::TAX_TYPE_INCLUDED . '\" ' . $s2 . '> ' . $this->CI->lang->line('taxes_vat_tax') . '</option>';
	}
}
?>
