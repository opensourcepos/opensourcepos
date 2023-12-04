<?php

namespace app\Libraries;

use App\Models\Attribute;
use App\Models\Customer;
use App\Models\Dinner_table;
use App\Models\Item;
use App\Models\Item_kit_items;
use App\Models\Item_quantity;
use App\Models\Item_taxes;
use App\Models\Enums\Rounding_mode;
use App\Models\Sale;
use CodeIgniter\Session\Session;
use App\Models\Stock_location;
use Config\OSPOS;
use ReflectionException;

/**
 * Sale library
 *
 * Library with utilities to manage sales
 **/
class Sale_lib
{
	private Attribute $attribute;
	private Customer $customer;
	private Dinner_table $dinner_table;
	private Item $item;
	private Item_kit_items $item_kit_items;
	private Item_quantity $item_quantity;
	private Item_taxes $item_taxes;
	private Sale $sale;
	private Stock_location $stock_location;
	private Session $session;
	private array $config;

	public function __construct()
	{
		$this->session = session();

		$this->attribute = model(Attribute::class);
		$this->customer = model(Customer::class);
		$this->dinner_table = model(Dinner_table::class);
		$this->item = model(Item::class);
		$this->item_kit_items = model(Item_kit_items::class);
		$this->item_quantity = model(Item_quantity::class);
		$this->item_taxes = model(Item_taxes::class);
		$this->sale = model(Sale::class);
		$this->stock_location = model(Stock_location::class);
		$this->config = config(OSPOS::class)->settings;
	}

	public function get_line_sequence_options(): array
	{
		return [
			'0' => lang('Sales.entry'),
			'1' => lang('Sales.group_by_type'),
			'2' => lang('Sales.group_by_category')
		];
	}

	public function get_register_mode_options(): array
	{
		$register_modes = [];

		if(!$this->config['invoice_enable'])
		{
			$register_modes['sale'] = lang('Sales.sale');
		}
		else
		{
			$register_modes['sale'] = lang('Sales.receipt');
			$register_modes['sale_quote'] = lang('Sales.quote');

			if($this->config['work_order_enable'])
			{
				$register_modes['sale_work_order'] = lang('Sales.work_order');
			}

			$register_modes['sale_invoice'] = lang('Sales.invoice');
		}

		$register_modes['return'] = lang('Sales.return');

		return $register_modes;
	}

	public function get_invoice_type_options(): array
	{
		$invoice_types = [];
		$invoice_types['invoice'] = lang('Sales.invoice_type_invoice');
		$invoice_types['tax_invoice'] = lang('Sales.invoice_type_tax_invoice');
		$invoice_types['custom_invoice'] = lang('Sales.invoice_type_custom_invoice');
		$invoice_types['custom_tax_invoice'] = lang('Sales.invoice_type_custom_tax_invoice');
		return $invoice_types;
	}

	public function get_cart(): array
	{
		if(!$this->session->get('sales_cart'))
		{
			$this->set_cart ([]);
		}

		return $this->session->get('sales_cart');
	}

	public function sort_and_filter_cart(array $cart): array
	{
		if(empty($cart))
		{
			return $cart;
		}

		$filtered_cart = [];

		foreach($cart as $k => $v)	//TODO: We should not be using single-letter variable names for readability.  Several of these foreach loops should be refactored.
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

		//TODO: This set of if/elseif/else needs to be converted to a switch statement
		// Entry sequence (this will render kits in the expected sequence)
		if($this->config['line_sequence'] == '0')
		{
			$sort = [];
			foreach($filtered_cart as $k => $v)
			{
				$sort['line'][$k] = $v['line'];
			}
			array_multisort($sort['line'], SORT_ASC, $filtered_cart);
		}
		// Group by Stock Type (nonstock first - type 1, stock next - type 0)
		elseif($this->config['line_sequence'] == '1')	//TODO: Need to change these to constants
		{
			$sort = [];
			foreach($filtered_cart as $k => $v)
			{
				$sort['stock_type'][$k] = $v['stock_type'];
				$sort['description'][$k] = $v['description'];
				$sort['name'][$k] = $v['name'];
			}
			array_multisort($sort['stock_type'], SORT_DESC, $sort['description'], SORT_ASC, $sort['name'], SORT_ASC, $filtered_cart);
		}
		// Group by Item Category
		elseif($this->config['line_sequence'] == '2')	//TODO: Need to change these to constants
		{
			$sort = [];
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
			$sort = [];
			foreach($filtered_cart as $k => $v)
			{
				$sort['line'][$k] = $v['line'];
			}
			array_multisort($sort['line'], SORT_ASC, $filtered_cart);
		}

		return $filtered_cart;
	}

	public function set_cart(array $cart_data): void
	{
		$this->session->set('sales_cart', $cart_data);
	}

	public function empty_cart(): void
	{
		$this->session->remove('sales_cart');
	}

	public function remove_temp_items(): void
	{
		// Loop through the cart items and delete temporary items specific to this sale
		$cart = $this->get_cart();
		foreach($cart as $line=>$item)
		{
			if($item['item_type'] == ITEM_TEMP)	//TODO: === ?
			{
				$this->item->delete($item['item_id']);
			}
		}
	}

	public function get_comment(): string
	{
		// avoid returning a null that results in a 0 in the comment if nothing is set/available
		$comment = $this->session->get('sales_comment');

		return empty($comment) ? '' : $comment;
	}

	public function set_comment(string $comment): void
	{
		$this->session->set('sales_comment', $comment);
	}

	public function clear_comment(): void
	{
		$this->session->remove('sales_comment');
	}

	public function get_invoice_number(): ?string
	{
		return $this->session->get('sales_invoice_number');
	}

	public function get_quote_number(): ?string
	{
		return $this->session->get('sales_quote_number');
	}

	public function get_work_order_number(): ?string
	{
		return $this->session->get('sales_work_order_number');
	}

	public function get_sale_type(): int
	{
		return $this->session->get('sale_type');
	}

	public function set_invoice_number(int $invoice_number, bool $keep_custom = false): void
	{
		$current_invoice_number = $this->session->get('sales_invoice_number');

		if(!$keep_custom || empty($current_invoice_number))
		{
			$this->session->set('sales_invoice_number', $invoice_number);
		}
	}

	public function set_quote_number(string $quote_number, bool $keep_custom = false): void
	{
		$current_quote_number = $this->session->get('sales_quote_number');

		if(!$keep_custom || empty($current_quote_number))
		{
			$this->session->set('sales_quote_number', $quote_number);
		}
	}

	public function set_work_order_number(string $work_order_number, bool $keep_custom = false): void
	{
		$current_work_order_number = $this->session->get('sales_work_order_number');

		if(!$keep_custom || empty($current_work_order_number))
		{
			$this->session->set('sales_work_order_number', $work_order_number);
		}
	}

	public function set_sale_type(int $sale_type, bool $keep_custom = false): void
	{
		$current_sale_type = $this->session->get('sale_type');

		if(!$keep_custom || empty($current_sale_type))
		{
			$this->session->set('sale_type', $sale_type);
		}
	}

	public function clear_invoice_number(): void
	{
		$this->session->remove('sales_invoice_number');
	}

	public function clear_quote_number(): void
	{
		$this->session->remove('sales_quote_number');
	}

	public function clear_work_order_number(): void
	{
		$this->session->remove('sales_work_order_number');
	}

	public function clear_sale_type(): void
	{
		$this->session->remove('sale_type');
	}

	public function set_suspended_id(int $suspended_id): void
	{
		$this->session->set('suspended_id', $suspended_id);
	}

	public function get_suspended_id(): int
	{
		return $this->session->get('suspended_id');
	}

	public function is_invoice_mode(): bool
	{
		return ($this->session->get('sales_mode') == 'sale_invoice'	&& $this->config['invoice_enable']);
	}

	public function is_sale_by_receipt_mode(): bool	//TODO: This function is not called anywhere in the code.
	{
		return ($this->session->get('sales_mode') == 'sale');	//TODO: === ?
	}

	public function is_quote_mode(): bool
	{
		return ($this->session->get('sales_mode') == 'sale_quote');	//TODO: === ?
	}

	public function is_return_mode(): bool
	{
		return ($this->session->get('sales_mode') == 'return');	//TODO: === ?
	}

	public function is_work_order_mode(): bool
	{
		return ($this->session->get('sales_mode') == 'sale_work_order');	//TODO: === ?
	}

	public function set_price_work_orders(string $price_work_orders): void
	{
		$this->session->set('sales_price_work_orders', $price_work_orders);
	}

	public function is_price_work_orders(): bool
	{
		return ($this->session->get('sales_price_work_orders') == 'true'	//TODO: === ?
			|| $this->session->get('sales_price_work_orders') == '1');	//TODO: === ?
	}

	public function set_print_after_sale(bool $print_after_sale): void
	{
		$this->session->set('sales_print_after_sale', $print_after_sale);
	}

	public function is_print_after_sale(): bool
	{//TODO: this needs to be converted to a switch statement
		if($this->config['print_receipt_check_behaviour'] == 'always')	//TODO: 'behaviour' is the british spelling, but the rest of the code is in American English.  Not a big deal, but noticed. Also ===
		{
			return true;
		}
		elseif($this->config['print_receipt_check_behaviour'] == 'never')	//TODO: === ?
		{
			return false;
		}
		else // remember last setting, session based though
		{
			return ($this->session->get('sales_print_after_sale') == 'true'	//TODO: === ?
				|| $this->session->get('sales_print_after_sale') == '1');	//TODO: === ?
		}
	}

	public function set_email_receipt(string $email_receipt): void
	{
		$this->session->set('sales_email_receipt', $email_receipt);
	}

	public function clear_email_receipt(): void
	{
		$this->session->remove('sales_email_receipt');
	}

	public function is_email_receipt(): bool
	{//TODO: this needs to be converted to a switch statement
		if($this->config['email_receipt_check_behaviour'] == 'always')	//TODO: 'behaviour' is the british spelling, but the rest of the code is in American English.  Not a big deal, but noticed. Also ===
		{
			return true;
		}
		elseif($this->config['email_receipt_check_behaviour'] == 'never')	//TODO: === ?
		{
			return false;
		}
		else // remember last setting, session based though
		{
			return ($this->session->get('sales_email_receipt') == 'true'	//TODO: === ?
				|| $this->session->get('sales_email_receipt') == '1');	//TODO: === ?
		}
	}

	// Multiple Payments
	public function get_payments(): array
	{
		if(!$this->session->get('sales_payments'))
		{
			$this->set_payments ([]);
		}

		return $this->session->get('sales_payments');
	}

	// Multiple Payments
	public function set_payments(array $payments_data): void
	{
		$this->session->set('sales_payments', $payments_data);
	}

	/**
	 * Adds a new payment to the payments array or updates an existing one.
	 * It will also disable cash_mode if a non-qualifying payment type is added.
	 * @param string $payment_id
	 * @param string $payment_amount
	 * @param int $cash_adjustment
	 */
	public function add_payment(string $payment_id, string $payment_amount, int $cash_adjustment = CASH_ADJUSTMENT_FALSE): void
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
			$payment = [
				$payment_id => [
				'payment_type' => $payment_id,
				'payment_amount' => $payment_amount,
				'cash_refund' => 0,
				'cash_adjustment' => $cash_adjustment
				]
			];

			$payments += $payment;
		}

		if($this->session->get('cash_mode'))
		{
			if($this->session->get('cash_rounding') && $payment_id != lang('Sales.cash') && $payment_id != lang('Sales.cash_adjustment'))
			{
				$this->session->set('cash_mode', CASH_MODE_FALSE);
			}
		}

		$this->set_payments($payments);
	}

	// Multiple Payments
	public function edit_payment(string $payment_id, float $payment_amount): bool
	{
		$payments = $this->get_payments();
		if(isset($payments[$payment_id]))
		{
			$payments[$payment_id]['payment_type'] = $payment_id;
			$payments[$payment_id]['payment_amount'] = $payment_amount;
			$this->set_payments($payments);

			return true;
		}

		return false;
	}

	/**
	 * Delete the selected payment from the payment array and if cash rounding is enabled
	 * and the payment type is one of the cash types then automatically delete the other
	 * @param string $payment_id
	 */
	public function delete_payment(string $payment_id): void
	{
		$payments = $this->get_payments();
		$decoded_payment_id = urldecode($payment_id);

		unset($payments[$decoded_payment_id]);

		$cash_rounding = $this->reset_cash_rounding();

		if($cash_rounding)
		{
			if($decoded_payment_id == lang('Sales.cash'))	//TODO: === ?
			{
				unset($payments[lang('Sales.cash_adjustment')]);
			}

			if($decoded_payment_id == lang('Sales.cash_adjustment'))	//TODO: === ?
			{
				unset($payments[lang('Sales.cash')]);
			}
		}
		$this->set_payments($payments);
	}

	// Multiple Payments
	public function empty_payments(): void	//TODO: function verbs are very inconsistent in these libraries.
	{
		$this->session->remove('sales_payments');
	}

	/**
	 * Retrieve the total payments made, excluding any cash adjustments
	 * and establish if cash_mode is in play
	 */
	public function get_payments_total(): string
	{
		$subtotal = '0.0';
		$cash_mode_eligible = CASH_MODE_TRUE;

		foreach($this->get_payments() as $payments)
		{
			if(!$payments['cash_adjustment'])
			{
				$subtotal = bcadd($payments['payment_amount'], $subtotal);
			}
			if(lang('Sales.cash') != $payments['payment_type'] && lang('Sales.cash_adjustment') != $payments['payment_type'])
			{
				$cash_mode_eligible = CASH_MODE_FALSE;
			}
		}

		if($cash_mode_eligible && $this->session->get('cash_rounding'))	//TODO: $cache_mode_eligible will always evaluate to true
		{
			$this->session->set('cash_mode', CASH_MODE_TRUE);
		}

		return $subtotal;
	}

	/**
	 * Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'paid_in_full'
	 * 'subtotal', 'discounted_subtotal', 'tax_exclusive_subtotal', 'item_count', 'total_units', 'cash_adjustment_amount'
	 */
	public function get_totals(array $taxes): array
	{
		$totals = [];

		$prediscount_subtotal = '0.0';
		$subtotal = '0.0';
		$total = '0.0';
		$total_discount = '0.0';
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
			$prediscount_subtotal = bcadd($prediscount_subtotal, $extended_amount);
			$total = bcadd($total, $extended_discounted_amount);

			$subtotal = bcadd($subtotal, $extended_discounted_amount);
		}

		$totals['prediscount_subtotal'] = $prediscount_subtotal;
		$totals['total_discount'] = $total_discount;
		$sales_tax = '0';

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
		$cash_rounding = $this->session->get('cash_rounding');
		$cash_mode = $this->session->get('cash_mode');

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

		if($cash_mode)	//TODO: Convert to ternary notation
		{
			$current_due = $cash_amount_due;
		}
		else
		{
			$current_due = $amount_due;
		}

		// 0 decimal -> 1 / 2 = 0.5, 1 decimals -> 0.1 / 2 = 0.05, 2 decimals -> 0.01 / 2 = 0.005
		$threshold = bcpow('10', (string)-totals_decimals()) / 2;

		if($this->get_mode() == 'return')	//TODO: Convert to ternary notation.
		{
			$totals['payments_cover_total'] = $current_due > -$threshold;
		}
		else
		{
			$totals['payments_cover_total'] = $current_due < $threshold;
		}

		$totals['item_count'] = $item_count;
		$totals['total_units'] = $total_units;
		$totals['cash_adjustment_amount'] = 0.0;

		if($totals['payments_cover_total'])
		{
			$totals['cash_adjustment_amount'] = round($cash_total - $totals['total'], totals_decimals(), PHP_ROUND_HALF_UP);
		}

		$cash_mode = $this->session->get('cash_mode');	//TODO: This variable is never used.

		return $totals;
	}

	// Multiple Payments
	public function get_amount_due(): string
	{
		// Payment totals need to be identified first so that we know whether or not there is a non-cash payment involved
		$payment_total = $this->get_payments_total();
		$sales_total = $this->get_total();
		$amount_due = bcsub($sales_total, $payment_total);
		$precision = totals_decimals();
		$rounded_due = bccomp((string)round((float)$amount_due, $precision, PHP_ROUND_HALF_UP), '0', $precision);	//TODO: Is round() currency safe?

		// take care of rounding error introduced by round tripping payment amount to the browser
		return $rounded_due == 0 ? '0' : $amount_due;	//TODO: ===
	}

	public function get_customer(): int
	{
		if(!$this->session->get('sales_customer'))
		{
			$this->set_customer(-1);	//TODO: Replace -1 with a constant
		}

		return $this->session->get('sales_customer');
	}

	public function set_customer(int $customer_id): void
	{
		$this->session->set('sales_customer', $customer_id);
	}

	public function remove_customer(): void
	{
		$this->session->remove('sales_customer');
	}

	public function get_employee(): int
	{
		if(!$this->session->get('sales_employee'))
		{
			$this->set_employee(-1);	//TODO: Replace -1 with a constant
		}

		return $this->session->get('sales_employee');
	}

	public function set_employee(int $employee_id): void
	{
		$this->session->set('sales_employee', $employee_id);
	}

	public function remove_employee(): void
	{
		$this->session->remove('sales_employee');
	}

	public function get_mode(): string
	{
		if(!$this->session->get('sales_mode'))
		{
			$this->set_mode('sale');
		}
		return $this->session->get('sales_mode');
	}

	public function set_mode(string $mode): void
	{
		$this->session->set('sales_mode', $mode);
	}

	public function clear_mode(): void
	{
		$this->session->remove('sales_mode');
	}

	public function get_dinner_table(): ?int
	{
		if(!$this->session->get('dinner_table'))
		{
			if($this->config['dinner_table_enable'])
			{
				$this->set_dinner_table(1);	//TODO: Replace 1 with constant
			}
		}

		return $this->session->get('dinner_table');
	}

	public function set_dinner_table(int $dinner_table): void
	{
		$this->session->set('dinner_table', $dinner_table);
	}

	public function clear_table(): void
	{
		$this->session->remove('dinner_table');
	}

	public function get_sale_location(): int
	{
		if(!$this->session->get('sales_location'))
		{
			$this->set_sale_location($this->stock_location->get_default_location_id('sales'));
		}

		return $this->session->get('sales_location');
	}

	public function set_sale_location(int $location): void
	{
		$this->session->set('sales_location', $location);
	}

	public function set_payment_type(string $payment_type): void
	{
		$this->session->set('payment_type', $payment_type);
	}

	public function get_payment_type(): ?string
	{
		return $this->session->get('payment_type');
	}

	public function clear_sale_location(): void
	{
		$this->session->remove('sales_location');
	}

	public function set_giftcard_remainder(string $value): void
	{
		$this->session->set('sales_giftcard_remainder', $value);
	}

	public function get_giftcard_remainder(): ?string
	{
		return $this->session->get('sales_giftcard_remainder');
	}

	public function clear_giftcard_remainder(): void
	{
		$this->session->remove('sales_giftcard_remainder');
	}

	public function set_rewards_remainder(string $value): void
	{
		$this->session->set('sales_rewards_remainder', $value);
	}

	public function get_rewards_remainder(): ?string
	{
		return $this->session->get('sales_rewards_remainder');
	}

	public function clear_rewards_remainder(): void
	{
		$this->session->remove('sales_rewards_remainder');
	}

	//TODO: this function needs to be reworked... way too many parameters.  Also, optional parameters must go after mandatory parameters.
	public function add_item(int &$item_id, int $item_location, string $quantity = '1', string &$discount = '0.0', int $discount_type = 0, int $price_mode = PRICE_MODE_STANDARD, int $kit_price_option = null, int $kit_print_option = null, string $price_override = null, string $description = null, string $serialnumber = null, int $sale_id = null, bool $include_deleted = false, bool $print_option = null, bool $line = null): bool
	{
		$item_info = $this->item->get_info_by_id_or_number($item_id, $include_deleted);

		//make sure item exists
		if(empty($item_info))
		{
			$item_id = NEW_ENTRY;
			return false;
		}

		$applied_discount = $discount;
		$item_id = $item_info->item_id;
		$item_type = $item_info->item_type;
		$stock_type = $item_info->stock_type;

		$price = $item_info->unit_price;
		$cost_price = $item_info->cost_price;
		if($price_override != null)
		{
			$price = $price_override;
		}

		if($price_mode == PRICE_MODE_KIT)
		{
			if(!($kit_price_option == PRICE_OPTION_ALL
				|| $kit_price_option == PRICE_OPTION_KIT  && $item_type == ITEM_KIT
				|| $kit_price_option == PRICE_OPTION_KIT_STOCK && $stock_type == HAS_STOCK))	//TODO: === ?
			{
				$price = '0.00';
				$applied_discount = '0.00';
			}

			// If price is zero do not include a discount regardless of type
			if($price == '0.00')	//TODO: === ?
			{
				$applied_discount = '0.00';
			}

			// If fixed discount then apply no more than the item price
			if($discount_type == FIXED)	//TODO: === ?
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
		$itemalreadyinsale = false;        //We did not find the item yet.	//TODO: variable naming here does not match the convention
		$insertkey = 0;                    //Key to use for new entry.	//TODO: $insertkey is never used
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])	//TODO: variable naming here does not match the convention
			{
				$maxkey = $item['line'];
			}

			if($item['item_id'] == $item_id && $item['item_location'] == $item_location)	//TODO: === ?
			{
				$itemalreadyinsale = true;
				$updatekey = $item['line'];
				if(!$item_info->is_serialized)
				{
					$quantity = bcadd($quantity, $items[$updatekey]['quantity']);
				}
			}
		}

		$insertkey = $maxkey + 1;//TODO Does not follow naming conventions.
		//array/cart records are identified by $insertkey and item_id is just another field.

		if($price_mode == PRICE_MODE_KIT)	//TODO: === ?
		{
			if($kit_print_option == PRINT_ALL)	//TODO: === ?
			{
				$print_option_selected = PRINT_YES;
			}
			elseif($kit_print_option == PRINT_KIT && $item_type == ITEM_KIT)	//TODO: === ?
			{
				$print_option_selected = PRINT_YES;
			}
			elseif($kit_print_option == PRINT_PRICED && $price > 0)	//TODO: === ?
			{
				$print_option_selected = PRINT_YES;
			}
			else
			{
				$print_option_selected = PRINT_NO;
			}
		}
		else
		{	//TODO: Convert this to ternary notation
			if($print_option != null)	//TODO: === ?
			{
				$print_option_selected = $print_option;
			}
			else
			{
				$print_option_selected = PRINT_YES;
			}
		}

		$total = $this->get_item_total($quantity, $price, $applied_discount, $discount_type);
		$discounted_total = $this->get_item_total($quantity, $price, $applied_discount, $discount_type, true);

		if($this->config['multi_pack_enabled'])
		{
			$item_info->name .= NAME_SEPARATOR . $item_info->pack_name;
		}

		$attribute_links = $this->attribute->get_link_values($item_id, 'sale_id', $sale_id, Attribute::SHOW_IN_SALES)->getRowObject();

		//Item already exists and is not serialized, add to quantity
		if(!$itemalreadyinsale || $item_info->is_serialized)
		{
			$item = [
				$insertkey => [
					'item_id' => $item_id,
					'item_location' => $item_location,
					'stock_name' => $this->stock_location->get_location_name($item_location),
					'line' => $insertkey,
					'name' => $item_info->name,
					'item_number' => $item_info->item_number,
					'attribute_values' => $attribute_links->attribute_values,
					'attribute_dtvalues' => $attribute_links->attribute_dtvalues,
					'description' => $description != null ? $description : $item_info->description,
					'serialnumber' => $serialnumber != null ? $serialnumber : '',
					'allow_alt_description' => $item_info->allow_alt_description,
					'is_serialized' => $item_info->is_serialized,
					'quantity' => $quantity,
					'discount' => $applied_discount,
					'discount_type' => $discount_type,
					'in_stock' => $this->item_quantity->get_item_quantity($item_id, $item_location)->quantity,
					'price' => $price,
					'cost_price' => $cost_price,
					'total' => $total,
					'discounted_total' => $discounted_total,
					'print_option' => $print_option_selected,
					'stock_type' => $stock_type,
					'item_type' => $item_type,
					'hsn_code' => $item_info->hsn_code,
					'tax_category_id' => $item_info->tax_category_id
				]
			];

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

		return true;
	}

	public function out_of_stock(int $item_id, int $item_location): string
	{
		//make sure item exists
		if($item_id != -1)	//TODO: !== ?.  Also Replace -1 with a constant
		{
			$item_info = $this->item->get_info_by_id_or_number($item_id);

			if($item_info->stock_type == HAS_STOCK)	//TODO: === ?
			{
				$item_quantity = $this->item_quantity->get_item_quantity($item_id, $item_location)->quantity;
				$quantity_added = $this->get_quantity_already_added($item_id, $item_location);

				if($item_quantity - $quantity_added < 0)
				{
					return lang('Sales.quantity_less_than_zero');
				}
				elseif($item_quantity - $quantity_added < $item_info->reorder_level)
				{
					return lang('Sales.quantity_less_than_reorder_level');
				}
			}
		}

		return '';
	}

	public function get_quantity_already_added(int $item_id, int $item_location): string
	{
		$items = $this->get_cart();
		$quantity_already_added = '0.0';
		foreach($items as $item)
		{
			if($item['item_id'] == $item_id && $item['item_location'] == $item_location)	//TODO: === ?
			{
				$quantity_already_added += $item['quantity'];	//TODO: for precision we likely need to use bcadd() since we are using that everywhere else for quantity
			}
		}

		return $quantity_already_added;
	}

	public function get_item_id(string $line_to_get): int
	{
		$items = $this->get_cart();

		foreach($items as $line => $item)
		{
			if($line == $line_to_get)
			{
				return $item['item_id'];
			}
		}

		return -1;	//TODO: Replace -1 with constant
	}

	/* @param string $line
	 * @param string $description
	 * @param string $serialnumber
	 * @param string $quantity
	 * @param string $discount
	 * @param string|null $discount_type
	 * @param string|null $price
	 * @param string|null $discounted_total
	 * @return bool
	 */
	public function edit_item(string $line, string $description, string $serialnumber, string $quantity, string $discount, ?string $discount_type, ?string $price, ?string $discounted_total = null): bool
	{
		$items = $this->get_cart();
		if(isset($items[$line]))
		{
			$line = &$items[$line];
			if($discounted_total != null && $discounted_total != $line['discounted_total'])
			{
				// Note when entered the "discounted_total" is expected to be entered without a discount
				$quantity = $this->get_quantity_sold($discounted_total, $price);
			}
			$line['description'] = $description;
			$line['serialnumber'] = $serialnumber;
			$line['quantity'] = $quantity;
			$line['discount'] = $discount;

			if(!empty($discount_type))
			{
				$line['discount_type'] = $discount_type;
			}

			$line['price'] = $price;
			$line['total'] = $this->get_item_total($quantity, $price, $discount, $line['discount_type']);
			$line['discounted_total'] = $this->get_item_total($quantity, $price, $discount, $line['discount_type'], true);
			$this->set_cart($items);
		}

		return false;	//TODO: This function will always return false.
	}

	/**
	 * @param int $line
	 * @return void
	 * @throws ReflectionException
	 */
	public function delete_item(int $line): void
	{
		$items = $this->get_cart();
		$item_type = $items[$line]['item_type'];

		if($item_type == ITEM_TEMP)
		{
			$item_id = $items[$line]['item_id'];
			$this->item->delete($item_id);
		}

		unset($items[$line]);
		$this->set_cart($items);
	}

	public function return_entire_sale(string $receipt_sale_id): void
	{
		//POS #
		$pieces = explode(' ', $receipt_sale_id);
		$sale_id = $pieces[1];

		$this->empty_cart();
		$this->remove_customer();

		foreach($this->sale->get_sale_items_ordered($sale_id)->getResult() as $row)
		{
			$this->add_item($row->item_id, $row->item_location, -$row->quantity_purchased, $row->discount, $row->discount_type, PRICE_MODE_STANDARD, null, null, $row->item_unit_price, $row->description, $row->serialnumber, null, true);
		}

		$this->set_customer($this->sale->get_customer($sale_id)->person_id);
	}

	public function add_item_kit(string $external_item_kit_id, int $item_location, float $discount, string $discount_type, bool $kit_price_option, bool $kit_print_option, string &$stock_warning): bool
	{
		//KIT #
		$pieces = explode(' ', $external_item_kit_id);
		$item_kit_id = (count($pieces) > 1) ? $pieces[1] : $external_item_kit_id;
		$result = true;
		$applied_discount = $discount;

		foreach($this->item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			$result &= $this->add_item($item_kit_item['item_id'], $item_location, $item_kit_item['quantity'], $discount, $discount_type, PRICE_MODE_KIT, $kit_price_option, $kit_print_option);

			if($stock_warning == null)
			{
				$stock_warning = $this->out_of_stock($item_kit_item['item_id'], $item_location);
			}
		}

		return $result;
	}

	public function copy_entire_sale(int $sale_id): void
	{
		$this->empty_cart();
		$this->remove_customer();

		foreach($this->sale->get_sale_items_ordered($sale_id)->getResult() as $row)
		{
			$this->add_item($row->item_id, $row->item_location, $row->quantity_purchased, $row->discount, $row->discount_type, PRICE_MODE_STANDARD, null, null, $row->item_unit_price, $row->description, $row->serialnumber, $sale_id, true, $row->print_option);
		}

		$this->session->set('cash_mode', CASH_MODE_FALSE);

		// Establish cash_mode for this sale by inspecting the payments
		if($this->session->get('cash_rounding'))
		{
			$cash_types_only = true;
			foreach($this->sale->get_sale_payments($sale_id)->getResult() as $row)
			{
				if($row->payment_type != lang('Sales.cash') && $row->payment_type != lang('Sales.cash_adjustment'))
				{
					$cash_types_only = false;
				}

			}
			//TODO: Consider converting to ternary notation.
			//$cash_types_only
			// ? $this->session->set('cash_mode', CASH_MODE_TRUE)
			// : $this->session->set('cash_mode', CASH_MODE_FALSE);
			if($cash_types_only)
			{
				$this->session->set('cash_mode', CASH_MODE_TRUE);
			}
			else
			{
				$this->session->set('cash_mode', CASH_MODE_FALSE);
			}
		}

		// Now load payments
		foreach($this->sale->get_sale_payments($sale_id)->getResult() as $row)
		{
			$this->add_payment($row->payment_type, $row->payment_amount, $row->cash_adjustment);
		}

		$this->set_customer($this->sale->get_customer($sale_id)->person_id);
		$this->set_employee($this->sale->get_employee($sale_id)->person_id);
		$this->set_quote_number($this->sale->get_quote_number($sale_id));
		$this->set_work_order_number($this->sale->get_work_order_number($sale_id));
		$this->set_sale_type($this->sale->get_sale_type($sale_id));
		$this->set_comment($this->sale->get_comment($sale_id));
		$this->set_dinner_table($this->sale->get_dinner_table($sale_id));
		$this->session->set('sale_id', $sale_id);
	}

	public function get_sale_id(): int
	{
		return $this->session->get('sale_id');
	}

	public function clear_all(): void
	{
		$this->session->set('sale_id', -1);	//TODO: Replace -1 with constant
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

	public function clear_cash_flags(): void
	{
		$this->session->remove('cash_rounding');
		$this->session->remove('cash_mode');
		$this->session->remove('payment_type');
	}

	/**
	 * Determines if cash rounding should be a consideration for this site
	 * It also set resets the cash mode to disabled which will then be re-evaluated when
	 * retrieving payments.
	 */
	public function reset_cash_rounding(): int
	{
		$cash_rounding_code = $this->config['cash_rounding_code'];

		if(cash_decimals() < totals_decimals() || $cash_rounding_code == Rounding_mode::HALF_FIVE)	//TODO: convert to ternary notation.
		{
			$cash_rounding = 1;	//TODO: Replace with constant
		}
		else
		{
			$cash_rounding = 0;	//TODO: Replace with constant
		}
		$this->session->set('cash_rounding', $cash_rounding);
		$this->session->set('cash_mode', CASH_MODE_FALSE);

		return $cash_rounding;
	}

	public function is_customer_taxable(): bool	//TODO: This function is never called in the code
	{
		$customer_id = $this->get_customer();
		$customer = $this->customer->get_info($customer_id);

		//Do not charge sales tax if we have a customer that is not taxable
		return $customer->taxable or $customer_id == -1;	//TODO: Replace with constant.  Also, I'm not sure we should be using the or operator instead of || here. $a || $b guarantees that the result of those two get returned.  It's possible that return $a or $b could return just the result of $a since `or` has a lower precedence.
	}

	public function apply_customer_discount(string $discount, int $discount_type): void
	{
		// Get all items in the cart so far...
		$items = $this->get_cart();

		foreach($items as &$item)
		{
			$quantity = $item['quantity'];
			$price = $item['price'];

			// set a new discount only if the current one is 0
			if($item['discount'] == 0.0)	//TODO: === ?
			{
				$item['discount'] = $discount;
				$item['total'] = $this->get_item_total($quantity, $price, $discount, $discount_type);
				$item['discounted_total'] = $this->get_item_total($quantity, $price, $discount, $discount_type, true);
			}
		}

		$this->set_cart($items);
	}

	public function get_discount(): string
	{
		$discount = '0.0';
		foreach($this->get_cart() as $item)
		{
			if($item['discount'] > '0.0')
			{
				$item_discount = $this->get_item_discount($item['quantity'], $item['price'], $item['discount'], $item['discount_type']);
				$discount = bcadd($discount, $item_discount);
			}
		}

		return $discount;
	}

	public function get_subtotal(bool $include_discount = false, bool $exclude_tax = false): string
	{
		return $this->calculate_subtotal($include_discount, $exclude_tax);
	}

	public function get_item_total_tax_exclusive(int $item_id, string $quantity, string $price, string $discount, int $discount_type, bool $include_discount = false): string
	{
		$tax_info = $this->item_taxes->get_info($item_id);
		$item_total = $this->get_item_total($quantity, $price, $discount, $discount_type, $include_discount);

		// only additive tax here
		foreach($tax_info as $tax)
		{
			$tax_percentage = $tax['percent'];
			$item_total = bcsub($item_total, $this->get_item_tax($quantity, $price, $discount, $discount_type, $tax_percentage));
		}

		return $item_total;
	}

	//TODO: This function doesn't seem to be called anywhere in the code.
	public function get_extended_total_tax_exclusive(int $item_id, string $discounted_extended_amount, string $quantity, string $price, string $discount = '0.0', int $discount_type = 0): string
	{
		$tax_info = $this->item_taxes->get_info($item_id);

		// only additive tax here
		foreach($tax_info as $tax)
		{
			$tax_percentage = $tax['percent'];
			$discounted_extended_amount = bcsub($discounted_extended_amount, $this->get_item_tax($quantity, $price, $discount, $discount_type, $tax_percentage));
		}

		return $discounted_extended_amount;
	}

	public function get_item_total(string $quantity, string $price, string $discount, int $discount_type, bool $include_discount = false): string
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
	 * @param string $total
	 * @param string $price
	 * @return string
	 */
	public function get_quantity_sold(string $total, string $price): string
	{
		return bcdiv($total, $price, quantity_decimals());
	}

	public function get_extended_amount(string $quantity, string $price, string $discount_amount = '0.0'): string
	{
		$extended_amount = bcmul($quantity, $price);

		return bcsub($extended_amount, $discount_amount);
	}

	public function get_item_discount(string $quantity, string $price, string $discount, int $discount_type): string
	{
		$total = bcmul($quantity, $price);
		if($discount_type == PERCENT)	//TODO: === ?.  Also, ternary notation
		{
			$discount = bcmul($total, bcdiv($discount, '100'));
		}
		else
		{
			$discount = bcmul($quantity, $discount);
		}

		return (string)round((float)$discount, totals_decimals(), PHP_ROUND_HALF_UP);	//TODO: is this safe with monetary amounts?
	}

	public function get_item_tax(string $quantity, string $price, string $discount, int $discount_type, string $tax_percentage): string
	{
		$item_total = $this->get_item_total($quantity, $price, $discount, $discount_type, true);

		if($this->config['tax_included'])
		{
			$tax_fraction = bcdiv(bcadd('100', $tax_percentage), '100');
			$price_tax_excl = bcdiv($item_total, $tax_fraction);

			return bcsub($item_total, $price_tax_excl);
		}

		$tax_fraction = bcdiv($tax_percentage, '100');

		return bcmul($item_total, $tax_fraction);
	}

	public function calculate_subtotal(bool $include_discount = false, bool $exclude_tax = false): string
	{
		$subtotal = '0.0';
		foreach($this->get_cart() as $item)
		{
			if($exclude_tax && $this->config['tax_included'])
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
	 * @return string
	 */
	public function get_total(bool $include_cash_rounding = true): string
	{
		$total = $this->calculate_subtotal(true);

		$cash_mode = $this->session->get('cash_mode');

		if(!$this->config['tax_included'])
		{
			$cart = $this->get_cart();
			$tax_lib = new Tax_lib();

			foreach($tax_lib->get_taxes($cart)[0] as $tax)
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

	public function get_empty_tables(?int $current_dinner_table_id): array
	{
		return $this->dinner_table->get_empty_tables($current_dinner_table_id);
	}

	public function check_for_cash_rounding(string $total): string
	{
		$cash_decimals = cash_decimals();
		$cash_rounding_code = $this->config['cash_rounding_code'];

		return Rounding_mode::round_number($cash_rounding_code, (float)$total, $cash_decimals);
	}
}
