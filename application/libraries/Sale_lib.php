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
		return array(
			'sale' => $this->CI->lang->line('sales_receipt'),
			'sale_invoice' => $this->CI->lang->line('sales_invoice'),
			'sale_quote' => $this->CI->lang->line('sales_quote')
		);
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

		foreach($cart as $k=>$v)
		{
			if($v['print_option'] == '0')
			{
				$filtered_cart[] = $v;
			}
		}

		// Entry sequence (this will render kits in the expected sequence)
		if($this->CI->config->item('line_sequence') == '0')
		{
			$sort = array();
			foreach($filtered_cart as $k=>$v)
			{
				$sort['line'][$k] = $v['line'];
			}
			array_multisort($sort['line'], SORT_ASC, $filtered_cart);
		}
		// Group by Stock Type (nonstock first - type 1, stock next - type 0)
		elseif($this->CI->config->item('line_sequence') == '1')
		{
			$sort = array();
			foreach($filtered_cart as $k=>$v)
			{
				$sort['stock_type'][$k] = $v['stock_type'];
				$sort['description'][$k] = $v['description'];
				$sort['name'][$k] = $v['name'];
			}
			array_multisort($sort['stock_type'], SORT_DESC, $sort['description'], SORT_ASC, $sort['name'], SORT_ASC. $filtered_cart);
		}
		// Group by Item Category
		elseif($this->CI->config->item('line_sequence') == '2')
		{
			$sort = array();
			foreach($filtered_cart as $k=>$v)
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
			foreach($filtered_cart as $k=>$v)
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

	public function clear_invoice_number()
	{
		$this->CI->session->unset_userdata('sales_invoice_number');
	}

	public function clear_quote_number()
	{
		$this->CI->session->unset_userdata('sales_quote_number');
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
		return ($this->CI->session->userdata('sales_invoice_number_enabled') == 'true' ||
				$this->CI->session->userdata('sales_mode') == 'sale_invoice' ||
				($this->CI->session->userdata('sales_invoice_number_enabled') == '1') &&
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

	public function set_invoice_number_enabled($invoice_number_enabled)
	{
		return $this->CI->session->set_userdata('sales_invoice_number_enabled', $invoice_number_enabled);
	}

	public function is_print_after_sale()
	{
		return ($this->CI->session->userdata('sales_print_after_sale') == 'true' ||
				$this->CI->session->userdata('sales_print_after_sale') == '1');
	}

	public function set_print_after_sale($print_after_sale)
	{
		return $this->CI->session->set_userdata('sales_print_after_sale', $print_after_sale);
	}

	public function get_email_receipt()
	{
		return $this->CI->session->userdata('sales_email_receipt');
	}

	public function set_email_receipt($email_receipt)
	{
		$this->CI->session->set_userdata('sales_email_receipt', $email_receipt);
	}

	public function clear_email_receipt()
	{
		$this->CI->session->unset_userdata('sales_email_receipt');
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

	// Multiple Payments
	public function add_payment($payment_id, $payment_amount)
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
			$payment = array($payment_id => array('payment_type' => $payment_id, 'payment_amount' => $payment_amount));

			$payments += $payment;
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

	// Multiple Payments
	public function delete_payment($payment_id)
	{
		$payments = $this->get_payments();
		unset($payments[urldecode($payment_id)]);
		$this->set_payments($payments);
	}

	// Multiple Payments
	public function empty_payments()
	{
		$this->CI->session->unset_userdata('sales_payments');
	}

	// Multiple Payments
	public function get_payments_total()
	{
		$subtotal = 0;
		$this->reset_cash_flags();
		foreach($this->get_payments() as $payments)
		{
			$subtotal = bcadd($payments['payment_amount'], $subtotal);
			if($this->CI->session->userdata('cash_rounding') && $this->CI->lang->line('sales_cash') != $payments['payment_type'])
			{
				$this->CI->session->set_userdata('cash_rounding', 0);
			}
		}

		return $subtotal;
	}

	/**
	 * Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'paid_in_full'
	 * 'subtotal', 'discounted_subtotal', 'tax_exclusive_subtotal'
	 */
	public function get_totals()
	{
		$cash_rounding = $this->CI->session->userdata('cash_rounding');

		$totals = array();

		$prediscount_subtotal = 0;
		$subtotal = 0;
		$total = 0;
		$total_discount = 0;

		foreach($this->get_cart() as $item)
		{
			$discount_amount = $this->get_item_discount($item['quantity'], $item['price'], $item['discount']);
			$total_discount = bcadd($total_discount, $discount_amount);

			$extended_amount = $this->get_extended_amount($item['quantity'], $item['price']);
			$extended_discounted_amount = $this->get_extended_amount($item['quantity'], $item['price'], $discount_amount);
			$prediscount_subtotal= bcadd($prediscount_subtotal, $extended_amount);
			$total = bcadd($total, $extended_discounted_amount);

			if($this->CI->config->item('tax_included'))
			{
				$subtotal = bcadd($subtotal, $this->get_extended_total_tax_exclusive($item['item_id'], $extended_discounted_amount, $item['quantity'], $item['price'], $item['discount']));
			}
			else
			{
				$subtotal = bcadd($subtotal, $extended_discounted_amount);
			}
		}

		$totals['prediscount_subtotal'] = $prediscount_subtotal;
		$totals['total_discount'] = $total_discount;
		$totals['subtotal'] = $subtotal;

		if($this->CI->config->item('tax_included'))
		{
			$totals['total'] = $total;
		}
		else
		{
			foreach($this->get_taxes() as $sales_tax)
			{
				$total = bcadd($total, $sales_tax['sale_tax_amount']);
			}
			$totals['total'] = $total;
		}

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

		$payment_total = $this->get_payments_total();
		$totals['payment_total'] = $payment_total;

		$amount_due = bcsub($total, $payment_total);
		$totals['amount_due'] = $amount_due;

		$cash_amount_due = bcsub($cash_total, $payment_total);
		$totals['cash_amount_due'] = $cash_amount_due;

		if($cash_rounding)
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
		$rounded_due = bccomp(round($amount_due, $precision, PHP_ROUND_HALF_EVEN), 0, $precision);
		// take care of rounding error introduced by round tripping payment amount to the browser
		return $rounded_due == 0 ? 0 : $amount_due;
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
			if($this->CI->config->item('invoice_enable') == '1')
			{
				$this->set_mode($this->CI->config->item('default_register_mode'));
			}
			else
			{
				$this->set_mode('sale');
			}
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
			$this->set_sale_location($this->CI->Stock_location->get_default_location_id());
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

	public function add_item(&$item_id, $quantity = 1, $item_location, $discount = 0, $price = NULL, $description = NULL, $serialnumber = NULL, $include_deleted = FALSE, $print_option = '0', $stock_type = HAS_STOCK)
	{
		$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);

		//make sure item exists
		if(empty($item_info))
		{
			$item_id = -1;
			return FALSE;
		}

		$item_id = $item_info->item_id;

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

		if(is_null($price))
		{
			$price = $item_info->unit_price;
		}
		elseif($price == 0)
		{
			$price = 0.00;
			$discount = 0.00;
		}

		// For print purposes this simpifies line selection
		// 0 will print, 2 will not print.   The decision about 1 is made here
		if($print_option == PRINT_PRICED)
		{
			$print_option = ($price == 0) ? PRINT_KIT : PRINT_ALL;
		}

		$total = $this->get_item_total($quantity, $price, $discount);
		$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
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
					'description' => $description != NULL ? $description : $item_info->description,
					'serialnumber' => $serialnumber != NULL ? $serialnumber : '',
					'allow_alt_description' => $item_info->allow_alt_description,
					'is_serialized' => $item_info->is_serialized,
					'quantity' => $quantity,
					'discount' => $discount,
					'in_stock' => $this->CI->Item_quantity->get_item_quantity($item_id, $item_location)->quantity,
					'price' => $price,
					'total' => $total,
					'discounted_total' => $discounted_total,
					'print_option' => $print_option,
					'stock_type' => $stock_type,
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
		$quanity_already_added = 0;
		foreach($items as $item)
		{
			if($item['item_id'] == $item_id && $item['item_location'] == $item_location)
			{
				$quanity_already_added+=$item['quantity'];
			}
		}

		return $quanity_already_added;
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

	public function edit_item($line, $description, $serialnumber, $quantity, $discount, $price)
	{
		$items = $this->get_cart();
		if(isset($items[$line]))
		{
			$line = &$items[$line];
			$line['description'] = $description;
			$line['serialnumber'] = $serialnumber;
			$line['quantity'] = $quantity;
			$line['discount'] = $discount;
			$line['price'] = $price;
			$line['total'] = $this->get_item_total($quantity, $price, $discount);
			$line['discounted_total'] = $this->get_item_total($quantity, $price, $discount, TRUE);
			$this->set_cart($items);
		}

		return FALSE;
	}

	public function delete_item($line)
	{
		$items = $this->get_cart();
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
			$this->add_item($row->item_id, -$row->quantity_purchased, $row->item_location, $row->discount_percent, $row->item_unit_price, $row->description, $row->serialnumber, TRUE, $row->print_option, $row->print_option);
		}

		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
	}

	public function add_item_kit($external_item_kit_id, $item_location, $discount, $price_option, $kit_print_option, &$stock_warning)
	{
		//KIT #
		$pieces = explode(' ', $external_item_kit_id);
		$item_kit_id = $pieces[1];
		$result = TRUE;

		foreach($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			if($price_option == PRICE_ALL) // all
			{
				$price = null;
			}
			elseif($price_option == PRICE_KIT) // item kit only
			{
				$price = 0;
			}
			elseif($price_option == PRICE_KIT_ITEMS) // item kit plus stock items (assuming materials)
			{
				if($item_kit_item['stock_type'] == ITEM) // stock item
				{
					$price = null;
				}
				else
				{
					$price = 0;
				}
			}

			if($kit_print_option == PRINT_ALL)
			{
				$print_option = PRINT_ALL;
			}
			elseif($kit_print_option == PRINT_PRICED) // priced
			{
				$print_option = PRINT_PRICED; // print if price not zero
			}
			elseif($kit_print_option == PRINT_KIT) // kit only if price is not zero
			{
				$print_option = PRINT_KIT; // Do not include in list
			}

			$result &= $this->add_item($item_kit_item['item_id'], $item_kit_item['quantity'], $item_location, $discount, $price, null, null, null, $print_option, $item_kit_item['stock_type']);

			if($stock_warning == null)
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
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_location, $row->discount_percent, $row->item_unit_price, $row->description, $row->serialnumber, TRUE, $row->print_option);
		}

		foreach($this->CI->Sale->get_sale_payments($sale_id)->result() as $row)
		{
			$this->add_payment($row->payment_type, $row->payment_amount);
		}

		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
		$this->set_employee($this->CI->Sale->get_employee($sale_id)->person_id);
		$this->set_quote_number($this->CI->Sale->get_quote_number($sale_id));
		$this->set_comment($this->CI->Sale->get_comment($sale_id));
		$this->set_dinner_table($this->CI->Sale->get_dinner_table($sale_id));
	}

	public function get_cart_reordered($sale_id)
	{
		$this->empty_cart();
		foreach($this->CI->Sale->get_sale_items_ordered($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_location, $row->discount_percent, $row->item_unit_price,
				$row->description, $row->serialnumber, TRUE, $row->print_option, $row->stock_type);
		}

		return $this->CI->session->userdata('sales_cart');
	}

	public function clear_all()
	{
		$this->set_invoice_number_enabled(FALSE);
		$this->clear_table();
		$this->empty_cart();
		$this->clear_comment();
		$this->clear_email_receipt();
		$this->clear_invoice_number();
		$this->clear_quote_number();
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

	public function reset_cash_flags()
	{
		if($this->CI->lang->line('payment_options_order') != 'cashdebitcredit')
		{
			$cash_mode = 1;
		}
		else
		{
			$cash_mode = 0;
		}
		$this->CI->session->set_userdata('cash_mode', $cash_mode);

		if(cash_decimals() < totals_decimals())
		{
			$cash_rounding = 1;
		}
		else
		{
			$cash_rounding = 0;
		}
		$this->CI->session->set_userdata('cash_rounding', $cash_rounding);
	}

	public function is_customer_taxable()
	{
		$customer_id = $this->get_customer();
		$customer = $this->CI->Customer->get_info($customer_id);

		//Do not charge sales tax if we have a customer that is not taxable
		return $customer->taxable or $customer_id == -1;
	}

	/*
	 * This returns taxes for VAT taxes and for pre 3.1.0 sales taxes.
	 */
	public function get_taxes()
	{
		$register_mode = $this->CI->config->item('default_register_mode');
		$tax_decimals = tax_decimals();
		$customer_id = $this->get_customer();
		$customer = $this->CI->Customer->get_info($customer_id);
		$sales_taxes = array();
		//Do not charge sales tax if we have a customer that is not taxable
		if($customer->taxable or $customer_id == -1)
		{
			foreach($this->get_cart() as $line => $item)
			{
				// Start of current VAT tax apply
				$tax_info = $this->CI->Item_taxes->get_info($item['item_id']);
				$tax_group_sequence = 0;
				foreach($tax_info as $tax)
				{
					// This computes tax for each line item and adds it to the tax type total
					$tax_group = (float)$tax['percent'] . '% ' . $tax['name'];
					$tax_type = Tax_lib::TAX_TYPE_VAT;
					$tax_basis = $this->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE);
					$tax_amount = 0;

					if($this->CI->config->item('tax_included'))
					{
						$tax_amount = $this->get_item_tax($item['quantity'], $item['price'], $item['discount'], $tax['percent']);
					}
					elseif($this->CI->config->item('customer_sales_tax_support') == '0')
					{
						$tax_amount = $this->CI->tax_lib->get_sales_tax_for_amount($tax_basis, $tax['percent'], '0', $tax_decimals);
					}

					if($tax_amount <> 0)
					{
						$this->CI->tax_lib->update_sales_taxes($sales_taxes, $tax_type, $tax_group, $tax['percent'], $tax_basis, $tax_amount, $tax_group_sequence, '0', -1);
						$tax_group_sequence += 1;
					}
				}

				$tax_category = '';
				$tax_rate = '';
				$rounding_code = Rounding_mode::HALF_UP;
				$tax_group_sequence = 0;
				$tax_code = '';

				if($this->CI->config->item('customer_sales_tax_support') == '1')
				{
					// Now calculate what the sales taxes should be (storing them in the $sales_taxes array
					$this->CI->tax_lib->apply_sales_tax($item, $customer->city, $customer->state, $customer->sales_tax_code, $register_mode, 0, $sales_taxes, $tax_category, $tax_rate, $rounding_code, $tax_group_sequence, $tax_code);
				}

			}

			$this->CI->tax_lib->round_sales_taxes($sales_taxes);
		}

		return $sales_taxes;
	}

	public function apply_customer_discount($discount_percent)
	{
		// Get all items in the cart so far...
		$items = $this->get_cart();

		foreach($items as &$item)
		{
			$quantity = $item['quantity'];
			$price = $item['price'];

			// set a new discount only if the current one is 0
			if($item['discount'] == 0)
			{
				$item['discount'] = $discount_percent;
				$item['total'] = $this->get_item_total($quantity, $price, $discount_percent);
				$item['discounted_total'] = $this->get_item_total($quantity, $price, $discount_percent, TRUE);
			}
		}

		$this->set_cart($items);
	}

	public function get_discount()
	{
		$discount = 0;
		foreach($this->get_cart() as $item)
		{
			if($item['discount'] > 0)
			{
				$item_discount = $this->get_item_discount($item['quantity'], $item['price'], $item['discount']);
				$discount = bcadd($discount, $item_discount);
			}
		}

		return $discount;
	}

	public function get_subtotal($include_discount = FALSE, $exclude_tax = FALSE)
	{
		return $this->calculate_subtotal($include_discount, $exclude_tax);
	}

	public function get_item_total_tax_exclusive($item_id, $quantity, $price, $discount_percentage, $include_discount = FALSE)
	{
		$tax_info = $this->CI->Item_taxes->get_info($item_id);
		$item_total = $this->get_item_total($quantity, $price, $discount_percentage, $include_discount);
		// only additive tax here
		foreach($tax_info as $tax)
		{
			$tax_percentage = $tax['percent'];
			$item_total = bcsub($item_total, $this->get_item_tax($quantity, $price, $discount_percentage, $tax_percentage));
		}

		return $item_total;
	}

	public function get_extended_total_tax_exclusive($item_id, $discounted_extended_amount, $quantity, $price, $discount_percentage = 0)
	{
		$tax_info = $this->CI->Item_taxes->get_info($item_id);
		// only additive tax here
		foreach($tax_info as $tax)
		{
			$tax_percentage = $tax['percent'];
			$discounted_extended_amount = bcsub($discounted_extended_amount, $this->get_item_tax($quantity, $price, $discount_percentage, $tax_percentage));
		}

		return $discounted_extended_amount;
	}

	public function get_item_total($quantity, $price, $discount_percentage, $include_discount = FALSE)
	{
		$total = bcmul($quantity, $price);
		if($include_discount)
		{
			$discount_amount = $this->get_item_discount($quantity, $price, $discount_percentage);

			return bcsub($total, $discount_amount);
		}

		return $total;
	}

	public function get_extended_amount($quantity, $price, $discount_amount = 0)
	{
		$extended_amount = bcmul($quantity, $price);

		return bcsub($extended_amount, $discount_amount);
	}

	public function get_item_discount($quantity, $price, $discount_percentage)
	{
		$total = bcmul($quantity, $price);
		$discount_fraction = bcdiv($discount_percentage, 100);

		return bcmul($total, $discount_fraction);
	}

	public function get_item_tax($quantity, $price, $discount_percentage, $tax_percentage)
	{
		$price = $this->get_item_total($quantity, $price, $discount_percentage, TRUE);
		if($this->CI->config->item('tax_included'))
		{
			$tax_fraction = bcadd(100, $tax_percentage);
			$tax_fraction = bcdiv($tax_fraction, 100);
			$price_tax_excl = bcdiv($price, $tax_fraction);

			return bcsub($price, $price_tax_excl);
		}
		$tax_fraction = bcdiv($tax_percentage, 100);

		return bcmul($price, $tax_fraction);
	}

	public function calculate_subtotal($include_discount = FALSE, $exclude_tax = FALSE)
	{
		$subtotal = 0;
		foreach($this->get_cart() as $item)
		{
			if($exclude_tax && $this->CI->config->item('tax_included'))
			{
				$subtotal = bcadd($subtotal, $this->get_item_total_tax_exclusive($item['item_id'], $item['quantity'], $item['price'], $item['discount'], $include_discount));
			}
			else
			{
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], $include_discount));
			}
		}

		return $subtotal;
	}

	public function get_total()
	{
		$total = $this->calculate_subtotal(TRUE);

		$cash_rounding = $this->CI->session->userdata('cash_rounding');

		foreach($this->get_taxes() as $sales_tax)
		{
			$total = bcadd($total, $sales_tax['sale_tax_amount']);
		}

		if($cash_rounding)
		{
			$total = $this->check_for_cash_rounding($total);
		}

		return $total;
	}

    public function get_empty_tables()
    {
    	return $this->CI->Dinner_table->get_empty_tables();
    }

	public function check_for_cash_rounding($total)
	{
		$cash_decimals = cash_decimals();
		$cash_rounding_code = $this->CI->config->item('cash_rounding_code');
		$rounded_total = $total;

		return Rounding_mode::round_number($cash_rounding_code, $total, $cash_decimals);
	}
}

?>
