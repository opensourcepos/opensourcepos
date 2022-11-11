<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Sale library
 *
 * Library with utilities to manage sales
 */

class Sale_lib
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('tax_lib');
		$this->CI->load->model('enums/Rounding_mode');
	}

	public function get_line_sequence_options()
	{
		return array(
			'0' => $this->CI->lang->line('sales_entry'),
			'1' => $this->CI->lang->line('sales_group_by_type'),
			'2' => $this->CI->lang->line('sales_group_by_category')
		);
	}

	public function get_register_mode_options()
	{
		$register_modes = array();
		if($this->CI->config->item('invoice_enable') == '0')
		{
			$register_modes['sale'] = $this->CI->lang->line('sales_sale');
		}
		else
		{
			$register_modes['sale'] = $this->CI->lang->line('sales_receipt');
			$register_modes['sale_quote'] = $this->CI->lang->line('sales_quote');
			if($this->CI->config->item('work_order_enable') == '1')
			{
				$register_modes['sale_work_order'] = $this->CI->lang->line('sales_work_order');
			}
			$register_modes['sale_invoice'] = $this->CI->lang->line('sales_invoice');
		}
		$register_modes['return'] = $this->CI->lang->line('sales_return');
		return $register_modes;
	}

	public function get_invoice_type_options()
	{
		$invoice_types = array();
		$invoice_types['invoice'] = $this->CI->lang->line('sales_invoice_type_invoice');
		$invoice_types['tax_invoice'] = $this->CI->lang->line('sales_invoice_type_tax_invoice');
		$invoice_types['custom_invoice'] = $this->CI->lang->line('sales_invoice_type_custom_invoice');
		$invoice_types['custom_tax_invoice'] = $this->CI->lang->line('sales_invoice_type_custom_tax_invoice');
		return $invoice_types;
	}

	public function get_cart()
	{
		if(!$this->CI->session->userdata('sales_cart'))
		{
			$this->set_cart(array());
		}

		return $this->CI->session->userdata('sales_cart');
	}

	public function sort_and_filter_cart($cart)
	{
		if(empty($cart))
		{
			return $cart;
		}

		$filtered_cart = array();

		foreach($cart as $k => $v)
		{
			if($v['print_option'] == PRINT_YES)
			{
				if($v['price'] == 0.0)
				{
					$v['discount'] = 0.0;
				}
				$filtered_cart[] = $v;
			}
		}

		// Entry sequence (this will render kits in the expected sequence)
		if($this->CI->config->item('line_sequence') == '0')
		{
			$sort = array();
			foreach($filtered_cart as $k => $v)
			{
				$sort['line'][$k] = $v['line'];
			}
			array_multisort($sort['line'], SORT_ASC, $filtered_cart);
		}
		// Group by Stock Type (nonstock first - type 1, stock next - type 0)
		elseif($this->CI->config->item('line_sequence') == '1')
		{
			$sort = array();
			foreach($filtered_cart as $k => $v)
			{
				$sort['stock_type'][$k] = $v['stock_type'];
				$sort['description'][$k] = $v['description'];
				$sort['name'][$k] = $v['name'];
			}
			array_multisort($sort['stock_type'], SORT_DESC, $sort['description'], SORT_ASC, $sort['name'], SORT_ASC . $filtered_cart);
		}
		// Group by Item Category
		elseif($this->CI->config->item('line_sequence') == '2')
		{
			$sort = array();
			foreach($filtered_cart as $k => $v)
			{
				$sort['category'][$k] = $v['stock_type'];
				$sort['description'][$k] = $v['description'];
				$sort['name'][$k] = $v['name'];
			}
			array_multisort($sort['category'], SORT_DESC, $sort['description'], SORT_ASC, $sort['name'], SORT_ASC, $filtered_cart);
		}
		// Group by entry sequence in descending sequence (the Standard)
		else
		{
			$sort = array();
			foreach($filtered_cart as $k => $v)
			{
				$sort['line'][$k] = $v['line'];
			}
			array_multisort($sort['line'], SORT_ASC, $filtered_cart);
		}

		return $filtered_cart;
	}

	public function set_cart($cart_data)
	{
		$this->CI->session->set_userdata('sales_cart', $cart_data);
	}

	public function empty_cart()
	{
		$this->CI->session->unset_userdata('sales_cart');
	}

	public function remove_temp_items()
	{
		// Loop through the cart items and delete temporary items specific to this sale
		$cart = $this->get_cart();
		foreach($cart as $line=>$item)
		{
			if($item['item_type'] == ITEM_TEMP)
			{
				$this->CI->Item->delete($item['item_id']);
			}
		}
	}

	public function get_comment()
	{
		// avoid returning a NULL that results in a 0 in the comment if nothing is set/available
		$comment = $this->CI->session->userdata('sales_comment');

		return empty($comment) ? '' : $comment;
	}

	public function set_comment($comment)
	{
		$this->CI->session->set_userdata('sales_comment', $comment);
	}

	public function clear_comment()
	{
		$this->CI->session->unset_userdata('sales_comment');
	}

	public function get_invoice_number()
	{
		return $this->CI->session->userdata('sales_invoice_number');
	}

	public function get_quote_number()
	{
		return $this->CI->session->userdata('sales_quote_number');
	}

	public function get_work_order_number()
	{
		return $this->CI->session->userdata('sales_work_order_number');
	}

	public function get_sale_type()
	{
		return $this->CI->session->userdata('sale_type');
	}

	public function set_invoice_number($invoice_number, $keep_custom = FALSE)
	{
		$current_invoice_number = $this->CI->session->userdata('sales_invoice_number');
		if(!$keep_custom || empty($current_invoice_number))
		{
			$this->CI->session->set_userdata('sales_invoice_number', $invoice_number);

		}
	}

	public function set_quote_number($quote_number, $keep_custom = FALSE)
	{
		$current_quote_number = $this->CI->session->userdata('sales_quote_number');
		if(!$keep_custom || empty($current_quote_number))
		{
			$this->CI->session->set_userdata('sales_quote_number', $quote_number);
		}
	}

	public function set_work_order_number($work_order_number, $keep_custom = FALSE)
	{
		$current_work_order_number = $this->CI->session->userdata('sales_work_order_number');
		if(!$keep_custom || empty($current_work_order_number))
		{
			$this->CI->session->set_userdata('sales_work_order_number', $work_order_number);
		}
	}

	public function set_sale_type($sale_type, $keep_custom = FALSE)
	{
		$current_sale_type = $this->CI->session->userdata('sale_type');
		if(!$keep_custom || empty($current_sale_type))
		{
			$this->CI->session->set_userdata('sale_type', $sale_type);
		}
	}

	public function clear_invoice_number()
	{
		$this->CI->session->unset_userdata('sales_invoice_number');
	}

	public function clear_quote_number()
	{
		$this->CI->session->unset_userdata('sales_quote_number');
	}

	public function clear_work_order_number()
	{
		$this->CI->session->unset_userdata('sales_work_order_number');
	}

	public function clear_sale_type()
	{
		$this->CI->session->unset_userdata('sale_type');
	}

	public function set_suspended_id($suspended_id)
	{
		$this->CI->session->set_userdata('suspended_id', $suspended_id);
	}

	public function get_suspended_id()
	{
		return $this->CI->session->userdata('suspended_id');
	}

	public function is_invoice_mode()
	{
		return ($this->CI->session->userdata('sales_mode') == 'sale_invoice' &&
					$this->CI->config->item('invoice_enable') == TRUE);
	}

	public function is_sale_by_receipt_mode()
	{
		return ($this->CI->session->userdata('sales_mode') == 'sale');
	}

	public function is_quote_mode()
	{
		return ($this->CI->session->userdata('sales_mode') == 'sale_quote');
	}

	public function is_return_mode()
	{
		return ($this->CI->session->userdata('sales_mode') == 'return');
	}

	public function is_work_order_mode()
	{
		return ($this->CI->session->userdata('sales_mode') == 'sale_work_order');
	}

	public function set_price_work_orders($price_work_orders)
	{
		return $this->CI->session->set_userdata('sales_price_work_orders', $price_work_orders);
	}

	public function is_price_work_orders()
	{
		return ($this->CI->session->userdata('sales_price_work_orders') == 'true' ||
				$this->CI->session->userdata('sales_price_work_orders') == '1');
	}

	public function set_print_after_sale($print_after_sale)
	{
		return $this->CI->session->set_userdata('sales_print_after_sale', $print_after_sale);
	}

	public function is_print_after_sale()
	{
		if($this->CI->config->item('print_receipt_check_behaviour') == 'always')
		{
			return TRUE;
		}
		elseif($this->CI->config->item('print_receipt_check_behaviour') == 'never')
		{
			return FALSE;
		}
		else // remember last setting, session based though
		{
			return ($this->CI->session->userdata('sales_print_after_sale') == 'true' ||
					$this->CI->session->userdata('sales_print_after_sale') == '1');
		}
	}

	public function set_email_receipt($email_receipt)
	{
		$this->CI->session->set_userdata('sales_email_receipt', $email_receipt);
	}

	public function clear_email_receipt()
	{
		$this->CI->session->unset_userdata('sales_email_receipt');
	}

	public function is_email_receipt()
	{
		if($this->CI->config->item('email_receipt_check_behaviour') == 'always')
		{
			return TRUE;
		}
		elseif($this->CI->config->item('email_receipt_check_behaviour') == 'never')
		{
			return FALSE;
		}
		else // remember last setting, session based though
		{
			return ($this->CI->session->userdata('sales_email_receipt') == 'true' ||
					$this->CI->session->userdata('sales_email_receipt') == '1');
		}
	}

	// Multiple Payments
	public function get_payments()
	{
		if(!$this->CI->session->userdata('sales_payments'))
		{
			$this->set_payments(array());
		}

		return $this->CI->session->userdata('sales_payments');
	}

	// Multiple Payments
	public function set_payments($payments_data)
	{
		$this->CI->session->set_userdata('sales_payments', $payments_data);
	}

	/**
	 * Adds a new payment to the payments array or updates an existing one.
	 * It will also disable cash_mode if an non-qualifying payment type is added.
	 * @param $payment_id
	 * @param $payment_amount
	 * @param int $cash_adjustment
	 */
	public function add_payment($payment_id, $payment_amount, $cash_adjustment = CASH_ADJUSTMENT_FALSE)
	{
		$payments = $this->get_payments();
		if(isset($payments[$payment_id]))
		{
			//payment_method already exists, add to payment_amount
			$payments[$payment_id]['payment_amount'] = bcadd($payments[$payment_id]['payment_amount'], $payment_amount);
		}
		else
		{
			//add to existing array
			$payment = array($payment_id => array('payment_type' => $payment_id, 'payment_amount' => $payment_amount,
				'cash_refund' => 0, 'cash_adjustment' => $cash_adjustment));

			$payments += $payment;
		}

		if($this->CI->session->userdata('cash_mode'))
		{
			if($this->CI->session->userdata('cash_rounding') && $payment_id != $this->CI->lang->line('sales_cash') && $payment_id != $this->CI->lang->line('sales_cash_adjustment'))
			{
				$this->CI->session->set_userdata('cash_mode', CASH_MODE_FALSE);
			}
		}

		$this->set_payments($payments);
	}

	// Multiple Payments
	public function edit_payment($payment_id, $payment_amount)
	{
		$payments = $this->get_payments();
		if(isset($payments[$payment_id]))
		{
			$payments[$payment_id]['payment_type'] = $payment_id;
			$payments[$payment_id]['payment_amount'] = $payment_amount;
			$this->set_payments($payments);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete the selected payment from the payment array and if cash rounding is enabled
	 * and the payment type is one of the cash types then automatically delete the other
	 * @param $payment_id
	 */
	public function delete_payment($payment_id)
	{
		$payments = $this->get_payments();
		$decoded_payment_id = urldecode($payment_id);
		unset($payments[$decoded_payment_id]);
		$cash_rounding = $this->reset_cash_rounding();
		if($cash_rounding)
		{
			if($decoded_payment_id == $this->CI->lang->line('sales_cash'))
			{
				unset($payments[$this->CI->lang->line('sales_cash_adjustment')]);
			}
			if($decoded_payment_id == $this->CI->lang->line('sales_cash_adjustment'))
			{
				unset($payments[$this->CI->lang->line('sales_cash')]);
			}
		}
		$this->set_payments($payments);
	}

	// Multiple Payments
	public function empty_payments()
	{
		$this->CI->session->unset_userdata('sales_payments');
	}

	/**
	 * Retrieve the total payments made, excluding any cash adjustments
	 * and establish if cash_mode is in play
	 */
	public function get_payments_total()
	{
		$subtotal = 0.0;
		$cash_mode_eligible = CASH_MODE_TRUE;

		foreach($this->get_payments() as $payments)
		{
			if(!$payments['cash_adjustment'])
			{
				$subtotal = bcadd($payments['payment_amount'], $subtotal);
			}
			if($this->CI->lang->line('sales_cash') != $payments['payment_type'] && $this->CI->lang->line('sales_cash_adjustment') != $payments['payment_type'])
			{
				$cash_mode_eligible = CASH_MODE_FALSE;
			}
		}

		if($cash_mode_eligible && $this->CI->session->userdata('cash_rounding'))
		{
			$this->CI->session->set_userdata('cash_mode', CASH_MODE_TRUE);
		}

		return $subtotal;
	}

	/**
	 * Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'paid_in_full'
	 * 'subtotal', 'discounted_subtotal', 'tax_exclusive_subtotal', 'item_count', 'total_units', 'cash_adjustment_amount'
	 */
	public function get_totals($taxes)
	{
		$totals = array();

		$prediscount_subtotal = 0.0;
		$subtotal = 0.0;
		$total = 0.0;
		$total_discount = 0.0;
		$item_count = 0;
		$total_units = 0.0;

		foreach($this->get_cart() as $item)
		{
			if($item['stock_type'] == HAS_STOCK)
			{
				$item_count++;
				$total_units += $item['quantity'];
			}
			$discount_amount = $this->get_item_discount($item['quantity'], $item['price'], $item['discount'], $item['discount_type']);
			$total_discount = bcadd($total_discount, $discount_amount);

			$extended_amount = $this->get_extended_amount($item['quantity'], $item['price']);
			$extended_discounted_amount = $this->get_extended_amount($item['quantity'], $item['price'], $discount_amount);
			$prediscount_subtotal= bcadd($prediscount_subtotal, $extended_amount);
			$total = bcadd($total, $extended_discounted_amount);

			$subtotal = bcadd($subtotal, $extended_discounted_amount);
		}

		$totals['prediscount_subtotal'] = $prediscount_subtotal;
		$totals['total_discount'] = $total_discount;
		$sales_tax = 0;

		foreach($taxes as $tax)
		{
			if($tax['tax_type'] === Tax_lib::TAX_TYPE_EXCLUDED)
			{
				$total = bcadd($total, $tax['sale_tax_amount']);
				$sales_tax = bcadd($sales_tax, $tax['sale_tax_amount']);
			}
			else
			{
				$subtotal = bcsub($subtotal, $tax['sale_tax_amount']);
			}
		}

		$totals['subtotal'] = $subtotal;
		$totals['total'] = $total;
		$totals['tax_total'] = $sales_tax;

		$payment_total = $this->get_payments_total();
		$totals['payment_total'] = $payment_total;
		$cash_rounding = $this->CI->session->userdata('cash_rounding');
		$cash_mode = $this->CI->session->userdata('cash_mode');

		if($cash_rounding)
		{
			$cash_total = $this->check_for_cash_rounding($total);
			$totals['cash_total'] = $cash_total;
		}
		else
		{
			$cash_total = $total;
			$totals['cash_total'] = $cash_total;
		}

		$amount_due = bcsub($total, $payment_total);
		$totals['amount_due'] = $amount_due;

		$cash_amount_due = bcsub($cash_total, $payment_total);
		$totals['cash_amount_due'] = $cash_amount_due;

		if($cash_mode)
		{
			$current_due = $cash_amount_due;
		}
		else
		{
			$current_due = $amount_due;
		}

		// 0 decimal -> 1 / 2 = 0.5, 1 decimals -> 0.1 / 2 = 0.05, 2 decimals -> 0.01 / 2 = 0.005
		$threshold = bcpow(10, -totals_decimals()) / 2;

		if($this->get_mode() == 'return')
		{
			$totals['payments_cover_total'] = $current_due > -$threshold;
		}
		else
		{
			$totals['payments_cover_total'] = $current_due < $threshold;
		}

		$totals['item_count'] = $item_count;
		$totals['total_units'] = $total_units;
		$totals['cash_adjustment_amount'] = 0;

		if($totals['payments_cover_total'])
		{
			$totals['cash_adjustment_amount'] = round($cash_total - $totals['total'], totals_decimals(), PHP_ROUND_HALF_UP);
		}

		$cash_mode = $this->CI->session->userdata('cash_mode');

		return $totals;
	}

	// Multiple Payments
	public function get_amount_due()
	{
		// Payment totals need to be identified first so that we know whether or not there is a non-cash payment involved
		$payment_total = $this->get_payments_total();
		$sales_total = $this->get_total();
		$amount_due = bcsub($sales_total, $payment_total);
		$precision = totals_decimals();
		$rounded_due = bccomp(round($amount_due, $precision, PHP_ROUND_HALF_UP), 0, $precision);
		// take care of rounding error introduced by round tripping payment amount to the browser
		return $rounded_due == 0.0 ? 0.0 : $amount_due;
	}

	public function get_customer()
	{
		if(!$this->CI->session->userdata('sales_customer'))
		{
			$this->set_customer(-1);
		}

		return $this->CI->session->userdata('sales_customer');
	}

	public function set_customer($customer_id)
	{
		$this->CI->session->set_userdata('sales_customer', $customer_id);
	}

	public function remove_customer()
	{
		$this->CI->session->unset_userdata('sales_customer');
	}

	public function get_employee()
	{
		if(!$this->CI->session->userdata('sales_employee'))
		{
			$this->set_employee(-1);
		}

		return $this->CI->session->userdata('sales_employee');
	}

	public function set_employee($employee_id)
	{
		$this->CI->session->set_userdata('sales_employee', $employee_id);
	}

	public function remove_employee()
	{
		$this->CI->session->unset_userdata('sales_employee');
	}

	public function get_mode()
	{
		if(!$this->CI->session->userdata('sales_mode'))                                                                                                                                                                                                                     
		{                                                                                                                                                                                                                                                                   
			$this->set_mode('sale');                                                                                                                                                                                                                            
		}      
		return $this->CI->session->userdata('sales_mode');
	}

	public function set_mode($mode)
	{
		$this->CI->session->set_userdata('sales_mode', $mode);
	}

	public function clear_mode()
	{
		$this->CI->session->unset_userdata('sales_mode');
	}

	public function get_dinner_table()
	{
		if(!$this->CI->session->userdata('dinner_table'))
		{
			if($this->CI->config->item('dinner_table_enable') == TRUE)
			{
				$this->set_dinner_table(1);
			}
		}

		return $this->CI->session->userdata('dinner_table');
	}

	public function set_dinner_table($dinner_table)
	{
		$this->CI->session->set_userdata('dinner_table', $dinner_table);
	}

	public function clear_table()
	{
		$this->CI->session->unset_userdata('dinner_table');
	}

	public function get_sale_location()
	{
		if(!$this->CI->session->userdata('sales_location'))
		{
			$this->set_sale_location($this->CI->Stock_location->get_default_location_id('sales'));
		}

		return $this->CI->session->userdata('sales_location');
	}

	public function set_sale_location($location)
	{
		$this->CI->session->set_userdata('sales_location', $location);
	}

	public function set_payment_type($payment_type)
	{
		$this->CI->session->set_userdata('payment_type', $payment_type);
	}

	public function get_payment_type()
	{
		return $this->CI->session->userdata('payment_type');
	}

	public function clear_sale_location()
	{
		$this->CI->session->unset_userdata('sales_location');
	}

	public function set_giftcard_remainder($value)
	{
		$this->CI->session->set_userdata('sales_giftcard_remainder', $value);
	}

	public function get_giftcard_remainder()
	{
		return $this->CI->session->userdata('sales_giftcard_remainder');
	}

	public function clear_giftcard_remainder()
	{
		$this->CI->session->unset_userdata('sales_giftcard_remainder');
	}

	public function set_rewards_remainder($value)
	{
		$this->CI->session->set_userdata('sales_rewards_remainder', $value);
	}

	public function get_rewards_remainder()
	{
		return $this->CI->session->userdata('sales_rewards_remainder');
	}

	public function clear_rewards_remainder()
	{
		$this->CI->session->unset_userdata('sales_rewards_remainder');
	}

	public function add_item(&$item_id, $quantity = 1, $item_location, &$discount = 0.0, $discount_type = 0, $price_mode = PRICE_MODE_STANDARD, $kit_price_option = NULL, $kit_print_option = NULL, $price_override = NULL, $description = NULL, $serialnumber = NULL, $sale_id = NULL, $include_deleted = FALSE, $print_option = NULL, $line = NULL)
	{
		$item_info = $this->CI->Item->get_info_by_id_or_number($item_id, $include_deleted);
		//make sure item exists
		if(empty($item_info))
		{
			$item_id = -1;
			return FALSE;
		}

		$applied_discount = $discount;
		$item_id = $item_info->item_id;
		$item_type = $item_info->item_type;
		$stock_type = $item_info->stock_type;

		$price = $item_info->unit_price;
		$cost_price = $item_info->cost_price;
		if($price_override != NULL)
		{
			$price = $price_override;
		}

		if($price_mode == PRICE_MODE_KIT)
		{
			if(!($kit_price_option == PRICE_OPTION_ALL
				|| $kit_price_option == PRICE_OPTION_KIT  && $item_type == ITEM_KIT
				|| $kit_price_option == PRICE_OPTION_KIT_STOCK && $stock_type == HAS_STOCK))
			{
				$price = 0.00;
				$applied_discount = 0.00;
			}
			// If price is zero do not include a discount regardless of type
			if($price == 0.00)
			{
				$applied_discount = 0.00;
			}
			// If fixed discount then apply no more than the item price
			if($discount_type == FIXED)
			{
				if($applied_discount > $price)
				{
					$applied_discount = $price;
					$discount -= $applied_discount;
				}
				else
				{
					$discount = 0;
				}
			}
		}

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_cart();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			if($item['item_id'] == $item_id && $item['item_location'] == $item_location)
			{
				$itemalreadyinsale = TRUE;
				$updatekey = $item['line'];
				if(!$item_info->is_serialized)
				{
					$quantity = bcadd($quantity, $items[$updatekey]['quantity']);
				}
			}
		}

		$insertkey = $maxkey + 1;
		//array/cart records are identified by $insertkey and item_id is just another field.

		if($price_mode == PRICE_MODE_KIT)
		{
			if($kit_print_option == PRINT_ALL)
			{
				$print_option_selected = PRINT_YES;
			}
			elseif($kit_print_option == PRINT_KIT && $item_type == ITEM_KIT)
			{
				$print_option_selected = PRINT_YES;
			}
			elseif($kit_print_option == PRINT_PRICED && $price > 0)
			{
				$print_option_selected = PRINT_YES;
			}
			else
			{
				$print_option_selected = PRINT_NO;
			}
		}
		else
		{
			if($print_option != NULL)
			{
				$print_option_selected = $print_option;
			}
			else
			{
				$print_option_selected = PRINT_YES;
			}
		}

		$total = $this->get_item_total($quantity, $price, $applied_discount, $discount_type);
		$discounted_total = $this->get_item_total($quantity, $price, $applied_discount, $discount_type, TRUE);

		if($this->CI->config->item('multi_pack_enabled') == '1')
		{
			$item_info->name .= NAME_SEPARATOR . $item_info->pack_name;
		}

		$attribute_links = $this->CI->Attribute->get_link_values($item_id, 'sale_id', $sale_id, Attribute::SHOW_IN_SALES)->row_object();

		//Item already exists and is not serialized, add to quantity
		if(!$itemalreadyinsale || $item_info->is_serialized)
		{
			$item = array($insertkey => array(
					'item_id' => $item_id,
					'item_location' => $item_location,
					'stock_name' => $this->CI->Stock_location->get_location_name($item_location),
					'line' => $insertkey,
					'name' => $item_info->name,
					'item_number' => $item_info->item_number,
					'attribute_values' => $attribute_links->attribute_values,
					'attribute_dtvalues' => $attribute_links->attribute_dtvalues,
					'description' => $description != NULL ? $description : $item_info->description,
					'serialnumber' => $serialnumber != NULL ? $serialnumber : '',
					'allow_alt_description' => $item_info->allow_alt_description,
					'is_serialized' => $item_info->is_serialized,
					'quantity' => $quantity,
					'discount' => $applied_discount,
					'discount_type' => $discount_type,
					'in_stock' => $this->CI->Item_quantity->get_item_quantity($item_id, $item_location)->quantity,
					'price' => $price,
					'cost_price' => $cost_price,
					'total' => $total,
					'discounted_total' => $discounted_total,
					'print_option' => $print_option_selected,
					'stock_type' => $stock_type,
					'item_type' => $item_type,
					'hsn_code' => $item_info->hsn_code,
					'tax_category_id' => $item_info->tax_category_id
				)
			);
			//add to existing array
			$items += $item;
		}
		else
		{
			$line = &$items[$updatekey];
			$line['quantity'] = $quantity;
			$line['total'] = $total;
			$line['discounted_total'] = $discounted_total;
		}

		$this->set_cart($items);

		return TRUE;
	}

	public function out_of_stock($item_id, $item_location)
	{
		//make sure item exists
		if($item_id != -1)
		{
			$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);

			if($item_info->stock_type == HAS_STOCK)
			{
				$item_quantity = $this->CI->Item_quantity->get_item_quantity($item_id, $item_location)->quantity;
				$quantity_added = $this->get_quantity_already_added($item_id, $item_location);

				if($item_quantity - $quantity_added < 0)
				{
					return $this->CI->lang->line('sales_quantity_less_than_zero');
				}
				elseif($item_quantity - $quantity_added < $item_info->reorder_level)
				{
					return $this->CI->lang->line('sales_quantity_less_than_reorder_level');
				}
			}
		}

		return '';
	}

	public function get_quantity_already_added($item_id, $item_location)
	{
		$items = $this->get_cart();
		$quantity_already_added = 0;
		foreach($items as $item)
		{
			if($item['item_id'] == $item_id && $item['item_location'] == $item_location)
			{
				$quantity_already_added += $item['quantity'];
			}
		}

		return $quantity_already_added;
	}

	public function get_item_id($line_to_get)
	{
		$items = $this->get_cart();

		foreach($items as $line=>$item)
		{
			if($line == $line_to_get)
			{
				return $item['item_id'];
			}
		}

		return -1;
	}

	public function edit_item($line, $description, $serialnumber, $quantity, $discount, $discount_type, $price, $discounted_total=NULL)
	{
		$items = $this->get_cart();
		if(isset($items[$line]))
		{
			$line = &$items[$line];
			if($discounted_total != NULL && $discounted_total != $line['discounted_total'])
			{
				// Note when entered the "discounted_total" is expected to be entered without a discount
				$quantity = $this->get_quantity_sold($discounted_total, $price);
			}
			$line['description'] = $description;
			$line['serialnumber'] = $serialnumber;
			$line['quantity'] = $quantity;
			$line['discount'] = $discount;
			if(!is_null($discount_type))
			{
				$line['discount_type'] = $discount_type;
			}
			$line['price'] = $price;
			$line['total'] = $this->get_item_total($quantity, $price, $discount, $line['discount_type']);
			$line['discounted_total'] = $this->get_item_total($quantity, $price, $discount, $line['discount_type'], TRUE);
			$this->set_cart($items);
		}

		return FALSE;
	}

	public function delete_item($line)
	{
		$items = $this->get_cart();
		$item_type = $items[$line]['item_type'];
		if($item_type == ITEM_TEMP)
		{
			$item_id = $items[$line]['item_id'];
			$this->CI->Item->delete($item_id);
		}
		unset($items[$line]);
		$this->set_cart($items);
	}

	public function return_entire_sale($receipt_sale_id)
	{
		//POS #
		$pieces = explode(' ', $receipt_sale_id);
		$sale_id = $pieces[1];

		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale->get_sale_items_ordered($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id, -$row->quantity_purchased, $row->item_location, $row->discount, $row->discount_type, PRICE_MODE_STANDARD, NULL, NULL, $row->item_unit_price, $row->description, $row->serialnumber, NULL, TRUE);
		}

		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
	}

	public function add_item_kit($external_item_kit_id, $item_location, $discount, $discount_type, $kit_price_option, $kit_print_option, &$stock_warning)
	{
		//KIT #
		$pieces = explode(' ', $external_item_kit_id);
		$item_kit_id = (count($pieces) > 1) ? $pieces[1] : $external_item_kit_id;
		$result = TRUE;
		$applied_discount = $discount;

		foreach($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			$result &= $this->add_item($item_kit_item['item_id'], $item_kit_item['quantity'], $item_location, $discount, $discount_type, PRICE_MODE_KIT, $kit_price_option, $kit_print_option);

			if($stock_warning == NULL)
			{
				$stock_warning = $this->out_of_stock($item_kit_item['item_id'], $item_location);
			}
		}

		return $result;
	}

	public function copy_entire_sale($sale_id)
	{
		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale->get_sale_items_ordered($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_location, $row->discount, $row->discount_type, PRICE_MODE_STANDARD, NULL, NULL, $row->item_unit_price, $row->description, $row->serialnumber, $sale_id, TRUE, $row->print_option);
		}

		$this->CI->session->set_userdata('cash_mode', CASH_MODE_FALSE);

		// Establish cash_mode for this sale by inspecting the payments
		if($this->CI->session->userdata('cash_rounding'))
		{
			$cash_types_only = true;
			foreach($this->CI->Sale->get_sale_payments($sale_id)->result() as $row)
			{
				if($row->payment_type != $this->CI->lang->line('sales_cash') && $row->payment_type != $this->CI->lang->line('sales_cash_adjustment'))
				{
					$cash_types_only = FALSE;
				}

			}
			if($cash_types_only)
			{
				$this->CI->session->set_userdata('cash_mode', CASH_MODE_TRUE);
			}
			else
			{
				$this->CI->session->set_userdata('cash_mode', CASH_MODE_FALSE);
			}
		}

		// Now load payments
		foreach($this->CI->Sale->get_sale_payments($sale_id)->result() as $row)
		{
			$this->add_payment($row->payment_type, $row->payment_amount, $row->cash_adjustment);
		}

		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
		$this->set_employee($this->CI->Sale->get_employee($sale_id)->person_id);
		$this->set_quote_number($this->CI->Sale->get_quote_number($sale_id));
		$this->set_work_order_number($this->CI->Sale->get_work_order_number($sale_id));
		$this->set_sale_type($this->CI->Sale->get_sale_type($sale_id));
		$this->set_comment($this->CI->Sale->get_comment($sale_id));
		$this->set_dinner_table($this->CI->Sale->get_dinner_table($sale_id));
		$this->CI->session->set_userdata('sale_id', $sale_id);
	}

	public function get_sale_id()
	{
		return $this->CI->session->userdata('sale_id');
	}

	public function clear_all()
	{
		$this->CI->session->set_userdata('sale_id', -1);
		$this->clear_mode();
		$this->clear_table();
		$this->empty_cart();
		$this->clear_comment();
		$this->clear_email_receipt();
		$this->clear_invoice_number();
		$this->clear_quote_number();
		$this->clear_work_order_number();
		$this->clear_sale_type();
		$this->clear_giftcard_remainder();
		$this->empty_payments();
		$this->remove_customer();
		$this->clear_cash_flags();
	}

	public function clear_cash_flags()
	{
		$this->CI->session->unset_userdata('cash_rounding');
		$this->CI->session->unset_userdata('cash_mode');
		$this->CI->session->unset_userdata('payment_type');
	}

	/**
	 * Determines if cash rounding should be a consideration for this site
	 * It also set resets the cash mode to disabled which will then be re-evaluated when
	 * retrieving payments.
	 */
	public function reset_cash_rounding()
	{
		$cash_rounding_code = $this->CI->config->item('cash_rounding_code');

		if(cash_decimals() < totals_decimals() || $cash_rounding_code == Rounding_mode::HALF_FIVE)
		{
			$cash_rounding = 1;
		}
		else
		{
			$cash_rounding = 0;
		}
		$this->CI->session->set_userdata('cash_rounding', $cash_rounding);
		$this->CI->session->set_userdata('cash_mode', CASH_MODE_FALSE);

		return $cash_rounding;
	}

	public function is_customer_taxable()
	{
		$customer_id = $this->get_customer();
		$customer = $this->CI->Customer->get_info($customer_id);

		//Do not charge sales tax if we have a customer that is not taxable
		return $customer->taxable or $customer_id == -1;
	}

	public function apply_customer_discount($discount, $discount_type)
	{
		// Get all items in the cart so far...
		$items = $this->get_cart();

		foreach($items as &$item)
		{
			$quantity = $item['quantity'];
			$price = $item['price'];

			// set a new discount only if the current one is 0
			if($item['discount'] == 0.0)
			{
				$item['discount'] = $discount;
				$item['total'] = $this->get_item_total($quantity, $price, $discount, $discount_type);
				$item['discounted_total'] = $this->get_item_total($quantity, $price, $discount, $discount_type, TRUE);
			}
		}

		$this->set_cart($items);
	}

	public function get_discount()
	{
		$discount = 0.0;
		foreach($this->get_cart() as $item)
		{
			if($item['discount'] > 0.0)
			{
				$item_discount = $this->get_item_discount($item['quantity'], $item['price'], $item['discount'], $item['discount_type']);
				$discount = bcadd($discount, $item_discount);
			}
		}

		return $discount;
	}

	public function get_subtotal($include_discount = FALSE, $exclude_tax = FALSE)
	{
		return $this->calculate_subtotal($include_discount, $exclude_tax);
	}

	public function get_item_total_tax_exclusive($item_id, $quantity, $price, $discount, $discount_type, $include_discount = FALSE)
	{
		$tax_info = $this->CI->Item_taxes->get_info($item_id);
		$item_total = $this->get_item_total($quantity, $price, $discount, $discount_type, $include_discount);
		// only additive tax here
		foreach($tax_info as $tax)
		{
			$tax_percentage = $tax['percent'];
			$item_total = bcsub($item_total, $this->get_item_tax($quantity, $price, $discount, $discount_type, $tax_percentage));
		}

		return $item_total;
	}

	public function get_extended_total_tax_exclusive($item_id, $discounted_extended_amount, $quantity, $price, $discount = 0.0, $discount_type = 0)
	{
		$tax_info = $this->CI->Item_taxes->get_info($item_id);
		// only additive tax here
		foreach($tax_info as $tax)
		{
			$tax_percentage = $tax['percent'];
			$discounted_extended_amount = bcsub($discounted_extended_amount, $this->get_item_tax($quantity, $price, $discount, $discount_type, $tax_percentage));
		}

		return $discounted_extended_amount;
	}

	public function get_item_total($quantity, $price, $discount, $discount_type, $include_discount = FALSE)
	{
		$total = bcmul($quantity, $price);
		if($include_discount)
		{
			$discount_amount = $this->get_item_discount($quantity, $price, $discount, $discount_type);

			return bcsub($total, $discount_amount);
		}

		return $total;
	}

	/**
	 * Derive the quantity sold based on the new total entered, returning the quanitity rounded to the
	 * appropriate decimal positions.
	 * @param $total
	 * @param $price
	 * @return string
	 */
	public function get_quantity_sold($total, $price)
	{
		$quantity = bcdiv($total, $price, quantity_decimals());

		return $quantity;
	}

	public function get_extended_amount($quantity, $price, $discount_amount = 0.0)
	{
		$extended_amount = bcmul($quantity, $price);

		return bcsub($extended_amount, $discount_amount);
	}

	public function get_item_discount($quantity, $price, $discount, $discount_type)
	{
		$total = bcmul($quantity, $price);
		if($discount_type == PERCENT)
		{
			$discount = bcmul($total, bcdiv($discount, 100));
		}
		else
		{
			$discount = bcmul($quantity, $discount);
		}

		return round($discount, totals_decimals(), PHP_ROUND_HALF_UP);
	}

	public function get_item_tax($quantity, $price, $discount, $discount_type, $tax_percentage)
	{
		$item_total = $this->get_item_total($quantity, $price, $discount, $discount_type, TRUE);

		if($this->CI->config->item('tax_included'))
		{
			$tax_fraction = bcdiv(bcadd(100, $tax_percentage), 100);
			$price_tax_excl = bcdiv($item_total, $tax_fraction);

			return bcsub($item_total, $price_tax_excl);
		}

		$tax_fraction = bcdiv($tax_percentage, 100);

		return bcmul($item_total, $tax_fraction);
	}

	public function calculate_subtotal($include_discount = FALSE, $exclude_tax = FALSE)
	{
		$subtotal = 0.0;
		foreach($this->get_cart() as $item)
		{
			if($exclude_tax && $this->CI->config->item('tax_included'))
			{
				$subtotal = bcadd($subtotal, $this->get_item_total_tax_exclusive($item['item_id'], $item['quantity'], $item['price'], $item['discount'], $item['discount_type'], $include_discount));
			}
			else
			{
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], $item['discount_type'], $include_discount));
			}
		}

		return $subtotal;
	}

	/**
	 * Calculates the total sales amount with the default option to include cash rounding
	 * @param bool $include_cash_rounding
	 * @return float|int|string
	 */
	public function get_total($include_cash_rounding = TRUE)
	{
		$total = $this->calculate_subtotal(TRUE);

		$cash_mode = $this->CI->session->userdata('cash_mode');

		if(!$this->CI->config->item('tax_included'))
		{
			$cart = $this->get_cart();
			foreach($this->CI->tax_lib->get_taxes($cart)[0] as $tax)
			{
				$total = bcadd($total, $tax['sale_tax_amount']);
			}
		}

		if($include_cash_rounding && $cash_mode)
		{
			$total = $this->check_for_cash_rounding($total);
		}

		return $total;
	}

	public function get_empty_tables($current_dinner_table_id)
	{
		return $this->CI->Dinner_table->get_empty_tables($current_dinner_table_id);
	}

	public function check_for_cash_rounding($total)
	{
		$cash_decimals = cash_decimals();
		$cash_rounding_code = $this->CI->config->item('cash_rounding_code');

		return Rounding_mode::round_number($cash_rounding_code, $total, $cash_decimals);
	}
}

?>
