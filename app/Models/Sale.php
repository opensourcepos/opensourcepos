<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use App\Libraries\Sale_lib;
use Config\OSPOS;
use ReflectionException;

/**
 * Sale class
 */
class Sale extends Model
{
	protected $table = 'sales';
	protected $primaryKey = 'sale_id';
	protected $useAutoIncrement = true;
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'sale_time',
		'customer_id',
		'employee_id',
		'comment',
		'quote_number',
		'sale_status',
		'invoice_number',
		'dinner_table_id',
		'work_order_number',
		'sale_type'
	];

	public function __construct()
	{
		parent::__construct();
		helper('text');
	}

	/**
	 * Get sale info
	 */
	public function get_info(int $sale_id): ResultInterface
	{
		$config = config(OSPOS::class)->settings;
		$this->create_temp_table (['sale_id' => $sale_id]);

		$decimals = totals_decimals();
		$sales_tax = 'IFnull(SUM(sales_items_taxes.sales_tax), 0)';
		$cash_adjustment = 'IFnull(SUM(payments.sale_cash_adjustment), 0)';
		$sale_price = 'CASE WHEN sales_items.discount_type = ' . PERCENT
			. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals) "
			. 'ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END';

		$sale_total = $config['tax_included']
			? "ROUND(SUM($sale_price), $decimals) + $cash_adjustment"
			: "ROUND(SUM($sale_price), $decimals) + $sales_tax + $cash_adjustment";

		$sql = 'sales.sale_id AS sale_id,
				MAX(DATE(sales.sale_time)) AS sale_date,
				MAX(sales.sale_time) AS sale_time,
				MAX(sales.comment) AS comment,
				MAX(sales.sale_status) AS sale_status,
				MAX(sales.invoice_number) AS invoice_number,
				MAX(sales.quote_number) AS quote_number,
				MAX(sales.employee_id) AS employee_id,
				MAX(sales.customer_id) AS customer_id,
				MAX(CONCAT(customer_p.first_name, " ", customer_p.last_name)) AS customer_name,
				MAX(customer_p.first_name) AS first_name,
				MAX(customer_p.last_name) AS last_name,
				MAX(customer_p.email) AS email,
				MAX(customer_p.comments) AS comments,
				MAX(IFnull(payments.sale_cash_adjustment, 0)) AS cash_adjustment,
				MAX(IFnull(payments.sale_cash_refund, 0)) AS cash_refund,
				' . "
				$sale_total AS amount_due,
				MAX(IFnull(payments.sale_payment_amount, 0)) AS amount_tendered,
				(MAX(payments.sale_payment_amount)) - ($sale_total) AS change_due,
				" . '
				MAX(payments.payment_type) AS payment_type';

		$builder = $this->db->table('sales_items AS sales_items');
		$builder->select($sql);

		$builder->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$builder->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'LEFT');
		$builder->join('customers AS customer', 'sales.customer_id = customer.person_id', 'LEFT');
		$builder->join('sales_payments_temp AS payments', 'sales.sale_id = payments.sale_id', 'LEFT OUTER');
		$builder->join('sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'LEFT OUTER');

		$builder->where('sales.sale_id', $sale_id);

		$builder->groupBy('sales.sale_id');
		$builder->orderBy('sales.sale_time', 'asc');

		return $builder->get();
	}

	/**
	 * Get number of rows for the takings (sales/manage) view
	 */
	public function get_found_rows(string $search, array $filters): int
	{
		return $this->search($search, $filters, 0, 0, 'sales.sale_time', 'desc', true);
	}

	/**
	 * Get the sales data for the takings (sales/manage) view
	 */
	public function search(string $search, array $filters, ?int $rows = 0, ?int $limit_from = 0, ?string $sort = 'sales.sale_time', ?string $order = 'desc', ?bool $count_only = false)
	{
		// Set default values
		if($rows == null) $rows = 0;
		if($limit_from == null) $limit_from = 0;
		if($sort == null) $sort = 'sales.sale_time';
		if($order == null) $order = 'desc';
		if($count_only == null) $count_only = false;

		$config = config(OSPOS::class)->settings;
		$db_prefix = $this->db->getPrefix();
		$decimals = totals_decimals();

		//Only non-suspended records
		$where = 'sales.sale_status = 0 AND ';
		$where .= empty($config['date_or_time_format'])
			? 'DATE(`' . $db_prefix . 'sales`.`sale_time`) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date'])
			: '`sales`.`sale_time` BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date']));

		$this->create_temp_table_sales_payments_data($where);

		$sale_price = 'CASE WHEN `sales_items`.`discount_type` = ' . PERCENT
			. " THEN `sales_items`.`quantity_purchased` * `sales_items`.`item_unit_price` - ROUND(`sales_items`.`quantity_purchased` * `sales_items`.`item_unit_price` * `sales_items`.`discount` / 100, $decimals) "
			. 'ELSE `sales_items`.`quantity_purchased` * (`sales_items`.`item_unit_price` - `sales_items`.`discount`) END';

		$sale_cost = 'SUM(`sales_items`.`item_cost_price` * `sales_items`.`quantity_purchased`)';

		$tax = 'IFnull(SUM(`sales_items_taxes`.`tax`), 0)';
		$sales_tax = 'IFnull(SUM(`sales_items_taxes`.`sales_tax`), 0)';
		$internal_tax = 'IFnull(SUM(`sales_items_taxes`.`internal_tax`), 0)';
		$cash_adjustment = 'IFnull(SUM(`payments`.`sale_cash_adjustment`), 0)';

		$sale_subtotal = "ROUND(SUM($sale_price), $decimals) - $internal_tax";
		$sale_total = "ROUND(SUM($sale_price), $decimals) + $sales_tax + $cash_adjustment";

		$this->create_temp_table_sales_items_taxes_data($where);

		$builder = $this->db->table('sales_items AS sales_items');

		// get_found_rows case
		if($count_only)
		{
			$builder->select('COUNT(DISTINCT `' . $db_prefix . 'sales`.`sale_id`) AS count');
		}
		else
		{
			$builder->select([
				'`' . $db_prefix . 'sales`.`sale_id` AS sale_id',
				'MAX(DATE(`' . $db_prefix . 'sales`.`sale_time`)) AS sale_date',
				'MAX(`' . $db_prefix . 'sales`.`sale_time`) AS sale_time',
				'MAX(`' . $db_prefix . 'sales`.`invoice_number`) AS invoice_number',
				'MAX(`' . $db_prefix . 'sales`.`quote_number`) AS quote_number',
				'SUM(`sales_items`.`quantity_purchased`) AS items_purchased',
				'MAX(CONCAT(`customer_p`.`first_name`, " ", `customer_p`.`last_name`)) AS customer_name',
				'MAX(`customer`.`company_name`) AS company_name',
				$sale_subtotal . ' AS subtotal',
				$tax . ' AS tax',
				$sale_total . ' AS total',
				$sale_cost . ' AS cost',
				'(' . $sale_total . ' - ' . $sale_cost . ') AS profit',
				$sale_total . ' AS amount_due',
				'MAX(`payments`.`sale_payment_amount`) AS amount_tendered',
				'(MAX(`payments`.`sale_payment_amount`)) - (' . $sale_total . ') AS change_due',
				'MAX(`payments`.`payment_type`) AS payment_type'
			], false);
		}

		$builder->join('sales', '`sales_items`.`sale_id` = `' . $db_prefix . 'sales`.`sale_id`', 'inner');
		$builder->join('people AS customer_p', '`' . $db_prefix . 'sales`.`customer_id` = `customer_p`.`person_id`', 'LEFT');
		$builder->join('customers AS customer', '`' . $db_prefix . 'sales`.`customer_id` = `customer`.`person_id`', 'LEFT');
		$builder->join('sales_payments_temp AS payments', '`' . $db_prefix . 'sales`.`sale_id` = `payments`.`sale_id`', 'LEFT OUTER');
		$builder->join(
			'sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'LEFT OUTER');

		$builder->where($where);

		$this->add_filters_to_query($search, $filters, $builder);

		//get_found_rows
		if($count_only)
		{
			return $builder->get()->getRow()->count;
		}

		$builder->groupBy('sales.sale_id');

		//order by sale time by default
		$builder->orderBy($sort, $order);

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/**
	 * Get the payment summary for the takings (sales/manage) view
	 */
	public function get_payments_summary(string $search, array $filters): array
	{
		$config = config(OSPOS::class)->settings;

		// get payment summary
		$builder = $this->db->table('sales AS sales');
		$builder->select('payment_type, COUNT(payment_amount) AS count, SUM(payment_amount - cash_refund) AS payment_amount');
		$builder->join('sales_payments', 'sales_payments.sale_id = sales.sale_id');
		$builder->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'LEFT');
		$builder->join('customers AS customer', 'sales.customer_id = customer.person_id', 'LEFT');

		//TODO: This needs to be replaced with Ternary notation
		if(empty($config['date_or_time_format']))	//TODO: duplicated code.  We should think about refactoring out a method.
		{
			$builder->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$builder->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if(!empty($search))	//TODO: duplicated code.  We should think about refactoring out a method.
		{
			if($filters['is_valid_receipt'])
			{
				$pieces = explode(' ',$search);
				$builder->where('sales.sale_id', $pieces[1]);
			}
			else
			{
				$builder->groupStart();
					$builder->like('customer_p.last_name', $search);	// customer last name
					$builder->orLike('customer_p.first_name', $search);	// customer first name
					$builder->orLike('CONCAT(customer_p.first_name, " ", customer_p.last_name)', $search);	// customer first and last name
					$builder->orLike('customer.company_name', $search);	// customer company name
				$builder->groupEnd();
			}
		}

		//TODO: This needs to be converted to a switch statement
		if($filters['sale_type'] == 'sales')	//TODO: we need to think about refactoring this block to a switch statement.
		{
			$builder->where('sales.sale_status = ' . COMPLETED . ' AND payment_amount > 0');
		}
		elseif($filters['sale_type'] == 'quotes')
		{
			$builder->where('sales.sale_status = ' . SUSPENDED . ' AND sales.quote_number IS NOT NULL');
		}
		elseif($filters['sale_type'] == 'returns')
		{
			$builder->where('sales.sale_status = ' . COMPLETED . ' AND payment_amount < 0');
		}
		elseif($filters['sale_type'] == 'all')
		{
			$builder->where('sales.sale_status = ' . COMPLETED);
		}

		//TODO: Avoid the double negatives
		if($filters['only_invoices'])
		{
			$builder->where('invoice_number IS NOT NULL');
		}

		if($filters['only_cash'])
		{
			$builder->like('payment_type', lang('Sales.cash'));
		}

		if($filters['only_due'])
		{
			$builder->like('payment_type', lang('Sales.due'));
		}

		if($filters['only_check'])
		{
			$builder->like('payment_type', lang('Sales.check'));
		}

		if($filters['only_creditcard'])
		{
			$builder->like('payment_type', lang('Sales.credit'));
		}

		$builder->groupBy('payment_type');

		$payments = $builder->get()->getResultArray();

		// consider Gift Card as only one type of payment and do not show "Gift Card: 1, Gift Card: 2, etc." in the total
		$gift_card_count = 0;
		$gift_card_amount = 0;

		foreach($payments as $key => $payment)
		{
			if(strstr($payment['payment_type'], lang('Sales.giftcard')))
			{
				$gift_card_count  += $payment['count'];
				$gift_card_amount += $payment['payment_amount'];

				//remove the "Gift Card: 1", "Gift Card: 2", etc. payment string
				unset($payments[$key]);
			}
		}

		if($gift_card_count > 0)
		{
			$payments[] = ['payment_type' => lang('Sales.giftcard'), 'count' => $gift_card_count, 'payment_amount' => $gift_card_amount];
		}

		return $payments;
	}

	/**
	 * Gets total of rows
	 */
	public function get_total_rows(): int
	{
		$builder = $this->db->table('sales');

		return $builder->countAllResults();
	}

	/**
	 * Gets search suggestions
	 */
	public function get_search_suggestions(string $search, int $limit = 25): array	//TODO: $limit is never used.
	{
		$suggestions = [];

		if(!$this->is_valid_receipt($search))
		{
			$builder = $this->db->table('sales');
			$builder->distinct()->select('first_name, last_name');
			$builder->join('people', 'people.person_id = sales.customer_id');
			$builder->like('last_name', $search);
			$builder->orLike('first_name', $search);
			$builder->orLike('CONCAT(first_name, " ", last_name)', $search);
			$builder->orLike('company_name', $search);
			$builder->orderBy('last_name', 'asc');

			foreach($builder->get()->getResultArray() as $result)
			{
				$suggestions[] = ['label' => $result['first_name'] . ' ' . $result['last_name']];
			}
		}
		else
		{
			$suggestions[] = ['label' => $search];
		}

		return $suggestions;
	}

	/**
	 * Gets total of invoice rows
	 */
	public function get_invoice_count(): int
	{
		$builder = $this->db->table('sales');
		$builder->where('invoice_number IS NOT NULL');

		return $builder->countAllResults();
	}

	/**
	 * Gets sale by invoice number
	 */
	public function get_sale_by_invoice_number(string $invoice_number): ResultInterface
	{
		$builder = $this->db->table('sales');
		$builder->where('invoice_number', $invoice_number);

		return $builder->get();
	}

	/**
	 * @param string $year
	 * @param int $start_from
	 * @return int
	 */
	public function get_invoice_number_for_year(string $year = '', int $start_from = 0): int
	{
		return $this->get_number_for_year('invoice_number', $year, $start_from);
	}

	/**
	 * @param string $year
	 * @param int $start_from
	 * @return int
	 */
	public function get_quote_number_for_year(string $year = '', int $start_from = 0): int
	{
		return $this->get_number_for_year('quote_number', $year, $start_from);
	}

	/**
	 * Gets invoice number by year
	 */
	private function get_number_for_year(string $field, string $year = '', int $start_from = 0): int
	{
		$year = $year == '' ? date('Y') : $year;

		$builder = $this->db->table('sales');
		$builder->select('COUNT( 1 ) AS number_year');
		$builder->where('DATE_FORMAT(sale_time, "%Y" ) = ', $year);
		$builder->where("$field IS NOT NULL");
		$result = $builder->get()->getRowArray();

		return ($start_from + $result['number_year']);
	}

	/**
	 * Checks if valid receipt
	 */
	public function is_valid_receipt(string &$receipt_sale_id): bool	//TODO: like the others, maybe this should be an array rather than a delimited string... either that or the parameter name needs to be changed. $receipt_sale_id implies that it's an int.
	{
		$config = config(OSPOS::class)->settings;

		if(!empty($receipt_sale_id))
		{
			//POS #
			$pieces = explode(' ', $receipt_sale_id);

			if(count($pieces) == 2 && preg_match('/(POS)/i', $pieces[0]))
			{
				return $this->exists($pieces[1]);
			}
			elseif($config['invoice_enable'])
			{
				$sale_info = $this->get_sale_by_invoice_number($receipt_sale_id);

				if($sale_info->getNumRows() > 0)
				{
					$receipt_sale_id = 'POS ' . $sale_info->getRow()->sale_id;

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Checks if sale exists
	 */
	public function exists(int $sale_id): bool
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		return ($builder->get()->getNumRows() == 1);	//TODO: ===
	}

	/**
	 * Update sale
	 */
	public function update($sale_id = null, $sale_data = null): bool
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);
		$success = $builder->update($sale_data);

		//touch payment only if update sale is successful and there is a payments object otherwise the result would be to delete all the payments associated to the sale
		if($success && !empty($sale_data['payments']))
		{
			//Run these queries as a transaction, we want to make sure we do all or nothing
			$this->db->transStart();

			$builder = $this->db->table('sales_payments');

			// add new payments
			foreach($sale_data['payments'] as $payment)
			{
				$payment_id = $payment['payment_id'];
				$payment_type = $payment['payment_type'];
				$payment_amount = $payment['payment_amount'];
				$cash_refund = $payment['cash_refund'];
				$cash_adjustment = $payment['cash_adjustment'];
				$employee_id = $payment['employee_id'];

				if($payment_id == NEW_ENTRY && $payment_amount != 0)
				{
					// Add a new payment transaction
					$sales_payments_data = [
						'sale_id' => $sale_id,
						'payment_type' => $payment_type,
						'payment_amount' => $payment_amount,
						'cash_refund' => $cash_refund,
						'cash_adjustment' => $cash_adjustment,
						'employee_id'  => $employee_id
					];
					$success = $builder->insert($sales_payments_data);
				}
				elseif($payment_id != NEW_ENTRY)
				{
					if($payment_amount != 0)
					{
						// Update existing payment transactions (payment_type only)
						$sales_payments_data = [
							'payment_type' => $payment_type,
							'payment_amount' => $payment_amount,
							'cash_refund' => $cash_refund,
							'cash_adjustment' => $cash_adjustment
						];

						$builder->where('payment_id', $payment_id);
						$success = $builder->update($sales_payments_data);
					}
					else
					{
						// Remove existing payment transactions with a payment amount of zero
						$success = $builder->delete(['payment_id' => $payment_id]);
					}
				}
			}

			$this->db->transComplete();
			$success &= $this->db->transStatus();
		}

		return $success;
	}

	/**
	 * Save the sale information after the sales is complete but before the final document is printed
	 * The sales_taxes variable needs to be initialized to an empty array before calling
	 * @throws ReflectionException
	 */
	public function save_value(int $sale_id, string &$sale_status, array &$items, int $customer_id, int $employee_id, string $comment, ?string $invoice_number,
							?string $work_order_number, ?string $quote_number, int $sale_type, ?array $payments, ?int $dinner_table_id, ?array &$sales_taxes): int	//TODO: this method returns the sale_id but the override is expecting it to return a bool. The signature needs to be reworked.  Generally when there are more than 3 maybe 4 parameters, there's a good chance that an object needs to be passed rather than so many params.
	{
		$config = config(OSPOS::class)->settings;
		$attribute = model(Attribute::class);
		$customer = model(Customer::class);
		$giftcard = model(Giftcard::class);
		$inventory = model('Inventory');
		$item = model(Item::class);

		$item_quantity = model(Item_quantity::class);

		if($sale_id != NEW_ENTRY)
		{
			$this->clear_suspended_sale_detail($sale_id);
		}

		if(count($items) == 0)	//TODO: ===
		{
			return -1;	//TODO: Replace -1 with a constant
		}

		$sales_data = [
			'sale_time' => date('Y-m-d H:i:s'),
			'customer_id' => $customer->exists($customer_id) ? $customer_id : null,
			'employee_id' => $employee_id,
			'comment' => $comment,
			'sale_status' => $sale_status,
			'invoice_number' => $invoice_number,
			'quote_number' => $quote_number,
			'work_order_number'=> $work_order_number,
			'dinner_table_id' => $dinner_table_id,
			'sale_type' => $sale_type
		];

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		if($sale_id == NEW_ENTRY)
		{
			$builder = $this->db->table('sales');
			$builder->insert($sales_data);
			$sale_id = $this->db->insertID();
		}
		else
		{
			$builder = $this->db->table('sales');
			$builder->where('sale_id', $sale_id);
			$builder->update($sales_data);
		}

		$total_amount = 0;
		$total_amount_used = 0;

		foreach($payments as $payment_id => $payment)
		{
			if(!empty(strstr($payment['payment_type'], lang('Sales.giftcard'))))
			{
				// We have a gift card, and we have to deduct the used value from the total value of the card.
				$splitpayment = explode( ':', $payment['payment_type'] );	//TODO: this variable doesn't follow our naming conventions.  Probably should be refactored to split_payment.
				$cur_giftcard_value = $giftcard->get_giftcard_value( $splitpayment[1] );	//TODO: this should be refactored to $current_giftcard_value
				$giftcard->update_giftcard_value( $splitpayment[1], $cur_giftcard_value - $payment['payment_amount'] );
			}
			elseif(!empty(strstr($payment['payment_type'], lang('Sales.rewards'))))
			{
				$cur_rewards_value = $customer->get_info($customer_id)->points;
				$customer->update_reward_points_value($customer_id, $cur_rewards_value - $payment['payment_amount'] );
				$total_amount_used = floatval($total_amount_used) + floatval($payment['payment_amount']);
			}

			$sales_payments_data = [
				'sale_id' => $sale_id,
				'payment_type' => $payment['payment_type'],
				'payment_amount' => $payment['payment_amount'],
				'cash_refund' => $payment['cash_refund'],
				'cash_adjustment' => $payment['cash_adjustment'],
				'employee_id' => $employee_id
			];

			$builder = $this->db->table('sales_payments');
			$builder->insert($sales_payments_data);

			$total_amount = floatval($total_amount) + floatval($payment['payment_amount']) - floatval($payment['cash_refund']);
		}

		$this->save_customer_rewards($customer_id, $sale_id, $total_amount, $total_amount_used);

		$customer = $customer->get_info($customer_id);

		foreach($items as $line => $item_data)
		{
			$cur_item_info = $item->get_info($item_data['item_id']);

			if($item_data['price'] == 0.00)
			{
				$item_data['discount'] = 0.00;
			}

			$sales_items_data = [
				'sale_id' => $sale_id,
				'item_id' => $item_data['item_id'],
				'line' => $item_data['line'],
				'description' => character_limiter($item_data['description'], 255),
				'serialnumber' => character_limiter($item_data['serialnumber'], 30),
				'quantity_purchased' => $item_data['quantity'],
				'discount' => $item_data['discount'],
				'discount_type' => $item_data['discount_type'],
				'item_cost_price' => $item_data['cost_price'],
				'item_unit_price' => $item_data['price'],
				'item_location' => $item_data['item_location'],
				'print_option' => $item_data['print_option']
			];

			$builder = $this->db->table('sales_items');
			$builder->insert($sales_items_data);

			if($cur_item_info->stock_type == HAS_STOCK && $sale_status == COMPLETED)	//TODO: === ?
			{
				// Update stock quantity if item type is a standard stock item and the sale is a standard sale
				$item_quantity_data = $item_quantity->get_item_quantity($item_data['item_id'], $item_data['item_location']);

				$item_quantity->save_value([
					'quantity'	=> $item_quantity_data->quantity - $item_data['quantity'],
					'item_id' => $item_data['item_id'],
					'location_id' => $item_data['item_location']],
					$item_data['item_id'],
					$item_data['item_location']
				);

				// if an items was deleted but later returned it's restored with this rule
				if($item_data['quantity'] < 0)
				{
					$item->undelete($item_data['item_id']);
				}

				// Inventory Count Details
				$sale_remarks = 'POS ' . $sale_id;	//TODO: Use string interpolation here.
				$inv_data = [
					'trans_date' => date('Y-m-d H:i:s'),
					'trans_items' => $item_data['item_id'],
					'trans_user' => $employee_id,
					'trans_location' => $item_data['item_location'],
					'trans_comment' => $sale_remarks,
					'trans_inventory' => -$item_data['quantity']
				];

				$inventory->insert($inv_data, false);
			}

			$attribute->copy_attribute_links($item_data['item_id'], 'sale_id', $sale_id);
		}

		if($customer_id == NEW_ENTRY || $customer->taxable)
		{
			$this->save_sales_tax($sale_id, $sales_taxes[0]);
			$this->save_sales_items_taxes($sale_id, $sales_taxes[1]);
		}

		if($config['dinner_table_enable'])
		{
			$dinner_table = model(Dinner_table::class);
			if($sale_status == COMPLETED)	//TODO: === ?
			{
				$dinner_table->release($dinner_table_id);
			}
			else
			{
				$dinner_table->occupy($dinner_table_id);
			}
		}

		$this->db->transComplete();

		return $this->db->transStatus() ? $sale_id : -1;
	}

	/**
	 * Saves sale tax
	 */
	public function save_sales_tax(int $sale_id, array $sales_taxes): void	//TODO: should we return the result of the insert here as a bool?
	{
		$builder = $this->db->table('sales_taxes');

		foreach($sales_taxes as $line => $sales_tax)
		{
			$sales_tax['sale_id'] = $sale_id;
			$builder->insert($sales_tax);
		}
	}

	/**
	 * Apply customer sales tax if the customer sales tax is enabled
	 * The original tax is still supported if the user configures it,
	 * but it won't make sense unless it's used exclusively for the purpose
	 * of VAT tax which becomes a price component.  VAT taxes must still be reported
	 * as a separate tax entry on the invoice.
	 */
	public function save_sales_items_taxes(int $sale_id, array $sales_item_taxes): void
	{
		$builder = $this->db->table('sales_items_taxes');

		foreach($sales_item_taxes as $line => $tax_item)
		{
			$sales_items_taxes = [
				'sale_id' => $sale_id,
				'item_id' => $tax_item['item_id'],
				'line' => $tax_item['line'],
				'name' => $tax_item['name'],
				'percent' => $tax_item['percent'],
				'tax_type' => $tax_item['tax_type'],
				'rounding_code' => $tax_item['rounding_code'],
				'cascade_sequence' => $tax_item['cascade_sequence'],
				'item_tax_amount' => $tax_item['item_tax_amount'],
				'sales_tax_code_id' => $tax_item['sales_tax_code_id'],
				'tax_category_id' => $tax_item['tax_category_id'],
				'jurisdiction_id' => $tax_item['jurisdiction_id']
			];

			$builder->insert($sales_items_taxes);
		}
	}

	/**
	 * Return the taxes that were charged
	 */
	public function get_sales_taxes(int $sale_id): array
	{
		$builder = $this->db->table('sales_taxes');
		$builder->where('sale_id', $sale_id);
		$builder->orderBy('print_sequence', 'asc');

		$query = $builder->get();

		return $query->getResultArray();
	}

	/**
	 * Return the taxes applied to a sale for a particular item
	 */
	public function get_sales_item_taxes(int $sale_id, int $item_id): array
	{
		$builder = $this->db->table('sales_items_taxes');
		$builder->select('item_id, name, percent');
		$builder->where('sale_id',$sale_id);
		$builder->where('item_id',$item_id);

		//return an array of taxes for an item
		return $builder->get()->getResultArray();
	}

	/**
	 * Deletes list of sales
	 * @throws ReflectionException
	 */
	public function delete_list(array $sale_ids, int $employee_id, bool $update_inventory = true): bool
	{
		$result = true;

		foreach($sale_ids as $sale_id)
		{
			$result &= $this->delete($sale_id, false, $update_inventory, $employee_id);
		}

		return $result;
	}

	/**
	 * Restores list of sales
	 */
	public function restore_list(array $sale_ids, int $employee_id, bool $update_inventory = true): bool	//TODO: $employee_id and $update_inventory are never used in the function.
	{
		foreach($sale_ids as $sale_id)
		{
			$this->update_sale_status($sale_id, SUSPENDED);
		}

		return true;
	}

	/**
	 * Delete sale.  Hard deletes are not supported for sales transactions.
	 * When a sale is "deleted" it is simply changed to a status of canceled.
	 * However, if applicable the inventory still needs to be updated
	 * @throws ReflectionException
	 */
	public function delete($sale_id = null, bool $purge = false, bool $update_inventory = true, $employee_id = null): bool
	{
		// start a transaction to assure data integrity
		$this->db->transStart();

		$sale_status = $this->get_sale_status($sale_id);

		if($update_inventory && $sale_status == COMPLETED)
		{
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$inventory = model('Inventory');
			$item = model(Item::class);
			$item_quantity = model(Item_quantity::class);

			$items = $this->get_sale_items($sale_id)->getResultArray();

			foreach($items as $item_data)
			{
				$cur_item_info = $item->get_info($item_data['item_id']);

				if($cur_item_info->stock_type == HAS_STOCK)
				{
					// create query to update inventory tracking
					$inv_data = [
						'trans_date' => date('Y-m-d H:i:s'),
						'trans_items' => $item_data['item_id'],
						'trans_user' => $employee_id,
						'trans_comment' => 'Deleting sale ' . $sale_id,
						'trans_location' => $item_data['item_location'],
						'trans_inventory' => $item_data['quantity_purchased']
					];
					// update inventory
					$inventory->insert($inv_data, false);

					// update quantities
					$item_quantity->change_quantity($item_data['item_id'], $item_data['item_location'], $item_data['quantity_purchased']);
				}
			}
		}

		$this->update_sale_status($sale_id, CANCELED);

		// execute transaction
		$this->db->transComplete();

		return $this->db->transStatus();
	}

	/**
	 * Gets sale item
	 */
	public function get_sale_items(int $sale_id): ResultInterface
	{
		$builder = $this->db->table('sales_items');
		$builder->where('sale_id', $sale_id);

		return $builder->get();
	}

	/**
	 * Used by the invoice and receipt programs
	 */
	public function get_sale_items_ordered(int $sale_id): ResultInterface
	{
		$config = config(OSPOS::class)->settings;
		$item = model(Item::class);

		$builder = $this->db->table('sales_items AS sales_items');
		$builder->select('
			sales_items.sale_id,
			sales_items.item_id,
			sales_items.description,
			serialnumber,	
			line,
			quantity_purchased,
			item_cost_price,
			item_unit_price,
			discount,
			discount_type,
			item_location,
			print_option,
			' . $item->get_item_name('name') . ',
			category,
			item_type,
			stock_type');
		$builder->join('items AS items', 'sales_items.item_id = items.item_id');
		$builder->where('sales_items.sale_id', $sale_id);

		// Entry sequence (this will render kits in the expected sequence)
		if($config['line_sequence'] == '0')	//TODO: Replace these with constants and this should be converted to a switch.
		{
			$builder->orderBy('line', 'asc');
		}
		// Group by Stock Type (nonstock first - type 1, stock next - type 0)
		elseif($config['line_sequence'] == '1')
		{
			$builder->orderBy('stock_type', 'desc');
			$builder->orderBy('sales_items.description', 'asc');
			$builder->orderBy('items.name', 'asc');
			$builder->orderBy('items.qty_per_pack', 'asc');
		}
		// Group by Item Category
		elseif($config['line_sequence'] == '2')
		{
			$builder->orderBy('category', 'asc');
			$builder->orderBy('sales_items.description', 'asc');
			$builder->orderBy('items.name', 'asc');
			$builder->orderBy('items.qty_per_pack', 'asc');
		}
		// Group by entry sequence in descending sequence (the Standard)
		else
		{
			$builder->orderBy('line', 'desc');
		}

		return $builder->get();
	}

	/**
	 * Gets sale payments
	 */
	public function get_sale_payments(int $sale_id): ResultInterface
	{
		$builder = $this->db->table('sales_payments');
		$builder->where('sale_id', $sale_id);

		return $builder->get();
	}

	/**
	 * Gets sale payment options
	 */
	public function get_payment_options(bool $giftcard = true, bool $reward_points = true): array
	{
		$payments = get_payment_options();

		if($giftcard)
		{
			$payments[lang('Sales.giftcard')] = lang('Sales.giftcard');
		}

		if($reward_points)
		{
			$payments[lang('Sales.rewards')] = lang('Sales.rewards');
		}
		$sale_lib = new Sale_lib();
		if($sale_lib->get_mode() == 'sale_work_order')
		{
			$payments[lang('Sales.cash_deposit')] = lang('Sales.cash_deposit');
			$payments[lang('Sales.credit_deposit')] = lang('Sales.credit_deposit');
		}

		return $payments;
	}

	/**
	 * Gets sale customer name
	 */
	public function get_customer(int $sale_id): object
	{
		$customer = model(Customer::class);

		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		return $customer->get_info($builder->get()->getRow()->customer_id);
	}

	/**
	 * Gets sale employee name
	 */
	public function get_employee(int $sale_id): object
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		$employee = model(Employee::class);

		return $employee->get_info($builder->get()->getRow()->employee_id);
	}

	/**
	 * Checks if quote number exists
	 */
	public function check_quote_number_exists(string $quote_number, string $sale_id = ''): bool
	{
		$builder = $this->db->table('sales');
		$builder->where('quote_number', $quote_number);

		if(!empty($sale_id))
		{
			$builder->where('sale_id !=', $sale_id);
		}

		return ($builder->get()->getNumRows() == 1);	//TODO: ===
	}

	/**
	 * Checks if invoice number exists
	 */
	public function check_invoice_number_exists(string $invoice_number, string $sale_id = ''): bool
	{
		$builder = $this->db->table('sales');
		$builder->where('invoice_number', $invoice_number);

		if(!empty($sale_id))
		{
			$builder->where('sale_id !=', $sale_id);
		}

		return ($builder->get()->getNumRows() == 1);	//TODO: ===
	}

	/**
	 * Checks if work order number exists
	 */
	public function check_work_order_number_exists(string $work_order_number, string $sale_id = ''): bool
	{
		$builder = $this->db->table('sales');
		$builder->where('invoice_number', $work_order_number);
		if(!empty($sale_id))
		{
			$builder->where('sale_id !=', $sale_id);
		}

		return ($builder->get()->getNumRows() == 1);	//TODO: ===
	}

	/**
	 * Gets Giftcard value
	 */
	public function get_giftcard_value(string $giftcardNumber): float
	{
		$giftcard = model(Giftcard::class);

		if(!$giftcard->exists($giftcard->get_giftcard_id($giftcardNumber)))	//TODO: camelCase is used here for the variable name but we are using _ everywhere else. CI4 moved to camelCase... we should pick one and do that.
		{
			return 0;
		}

		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcardNumber);

		return $builder->get()->getRow()->value;
	}

	/**
	 * Creates sales temporary dimensional table
	 * We create a temp table that allows us to do easy report/sales queries
	 */
	public function create_temp_table(array $inputs): void
	{
		$config = config(OSPOS::class)->settings;

		if(empty($inputs['sale_id']))
		{
			if(empty($config['date_or_time_format']))	//TODO: This needs to be replaced with Ternary notation
			{
				$where = 'DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
			}
			else
			{
				$where = 'sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
			}
		}
		else
		{
			$where = 'sales.sale_id = ' . $this->db->escape($inputs['sale_id']);
		}

		$decimals = totals_decimals();

		$sale_price = 'CASE WHEN sales_items.discount_type = ' . PERCENT
			. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals) "
			. 'ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END';

		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';

		$tax = 'IFnull(SUM(sales_items_taxes.tax), 0)';
		$sales_tax = 'IFnull(SUM(sales_items_taxes.sales_tax), 0)';
		$internal_tax = 'IFnull(SUM(sales_items_taxes.internal_tax), 0)';

		$cash_adjustment = 'IFnull(SUM(payments.sale_cash_adjustment), 0)';

		if($config['tax_included'])
		{
			$sale_total = "ROUND(SUM($sale_price), $decimals) + $cash_adjustment";
			$sale_subtotal = "$sale_total - $internal_tax";
		}
		else
		{
			$sale_subtotal = "ROUND(SUM($sale_price), $decimals) - $internal_tax + $cash_adjustment";
			$sale_total = "ROUND(SUM($sale_price), $decimals) + $sales_tax + $cash_adjustment";
		}

		// create a temporary table to contain all the sum of taxes per sale item
		$sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sales_items_taxes_temp') .
			' (INDEX(sale_id), INDEX(item_id)) ENGINE=MEMORY
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(ROUND(sales_items_taxes.item_tax_amount, ' . $decimals . ')) AS tax,
					SUM(ROUND(CASE WHEN sales_items_taxes.tax_type = 0 THEN sales_items_taxes.item_tax_amount ELSE 0 END, ' . $decimals . ')) AS internal_tax,
					SUM(ROUND(CASE WHEN sales_items_taxes.tax_type = 1 THEN sales_items_taxes.item_tax_amount ELSE 0 END, ' . $decimals . ')) AS sales_tax
				FROM ' . $this->db->prefixTable('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->prefixTable('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->prefixTable('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)';

		$this->db->query($sql);

		// create a temporary table to contain all the payment types and amount
		$sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sales_payments_temp') .
			' (PRIMARY KEY(sale_id), INDEX(sale_id))
			(
				SELECT payments.sale_id AS sale_id,
					SUM(CASE WHEN payments.cash_adjustment = 0 THEN payments.payment_amount ELSE 0 END) AS sale_payment_amount,
					SUM(CASE WHEN payments.cash_adjustment = 1 THEN payments.payment_amount ELSE 0 END) AS sale_cash_adjustment,
					SUM(payments.cash_refund) AS sale_cash_refund,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", (payments.payment_amount - payments.cash_refund)) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->prefixTable('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->prefixTable('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE ' . $where . '
				GROUP BY payments.sale_id
			)';

		$this->db->query($sql);
		$item = model(Item::class);
		$sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sales_items_temp') .
			' (INDEX(sale_date), INDEX(sale_time), INDEX(sale_id))
			(
				SELECT
					MAX(DATE(sales.sale_time)) AS sale_date,
					MAX(sales.sale_time) AS sale_time,
					sales.sale_id AS sale_id,
					MAX(sales.sale_status) AS sale_status,
					MAX(sales.sale_type) AS sale_type,
					MAX(sales.comment) AS comment,
					MAX(sales.invoice_number) AS invoice_number,
					MAX(sales.quote_number) AS quote_number,
					MAX(sales.customer_id) AS customer_id,
					MAX(CONCAT(customer_p.first_name, " ", customer_p.last_name)) AS customer_name,
					MAX(customer_p.first_name) AS customer_first_name,
					MAX(customer_p.last_name) AS customer_last_name,
					MAX(customer_p.email) AS customer_email,
					MAX(customer_p.comments) AS customer_comments,
					MAX(customer.company_name) AS customer_company_name,
					MAX(sales.employee_id) AS employee_id,
					MAX(CONCAT(employee.first_name, " ", employee.last_name)) AS employee_name,
					items.item_id AS item_id,
					MAX(' . $item->get_item_name() . ') AS name,
					MAX(items.item_number) AS item_number,
					MAX(items.category) AS category,
					MAX(items.supplier_id) AS supplier_id,
					MAX(sales_items.quantity_purchased) AS quantity_purchased,
					MAX(sales_items.item_cost_price) AS item_cost_price,
					MAX(sales_items.item_unit_price) AS item_unit_price,
					MAX(sales_items.discount) AS discount,
					sales_items.discount_type AS discount_type,
					sales_items.line AS line,
					MAX(sales_items.serialnumber) AS serialnumber,
					MAX(sales_items.item_location) AS item_location,
					MAX(sales_items.description) AS description,
					MAX(payments.payment_type) AS payment_type,
					MAX(payments.sale_payment_amount) AS sale_payment_amount,
					' . "
					$sale_subtotal AS subtotal,
					$tax AS tax,
					$sale_total AS total,
					$sale_cost AS cost,
					($sale_subtotal - $sale_cost) AS profit
					" . '
				FROM ' . $this->db->prefixTable('sales_items') . ' AS sales_items
				INNER JOIN ' . $this->db->prefixTable('sales') . ' AS sales
					ON sales_items.sale_id = sales.sale_id
				INNER JOIN ' . $this->db->prefixTable('items') . ' AS items
					ON sales_items.item_id = items.item_id
				LEFT OUTER JOIN ' . $this->db->prefixTable('sales_payments_temp') . ' AS payments
					ON sales_items.sale_id = payments.sale_id
				LEFT OUTER JOIN ' . $this->db->prefixTable('suppliers') . ' AS supplier
					ON items.supplier_id = supplier.person_id
				LEFT OUTER JOIN ' . $this->db->prefixTable('people') . ' AS customer_p
					ON sales.customer_id = customer_p.person_id
				LEFT OUTER JOIN ' . $this->db->prefixTable('customers') . ' AS customer
					ON sales.customer_id = customer.person_id
				LEFT OUTER JOIN ' . $this->db->prefixTable('people') . ' AS employee
					ON sales.employee_id = employee.person_id
				LEFT OUTER JOIN ' . $this->db->prefixTable('sales_items_taxes_temp') . ' AS sales_items_taxes
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)';

		$this->db->query($sql);
	}

	/**
	 * Retrieves all sales that are in a suspended state
	 */
	public function get_all_suspended(int $customer_id = null): array
	{
		if($customer_id == NEW_ENTRY)
		{
			$query = $this->db->query("SELECT sale_id, case when sale_type = '".SALE_TYPE_QUOTE."' THEN quote_number WHEN sale_type = '".SALE_TYPE_WORK_ORDER."' THEN work_order_number else sale_id end as doc_id, sale_id as suspended_sale_id, sale_status, sale_time, dinner_table_id, customer_id, employee_id, comment FROM "
				. $this->db->prefixTable('sales') . ' where sale_status = ' . SUSPENDED);
		}
		else
		{
			$query = $this->db->query("SELECT sale_id, case when sale_type = '".SALE_TYPE_QUOTE."' THEN quote_number WHEN sale_type = '".SALE_TYPE_WORK_ORDER."' THEN work_order_number else sale_id end as doc_id, sale_status, sale_time, dinner_table_id, customer_id, employee_id, comment FROM "
				. $this->db->prefixTable('sales') . ' where sale_status = '. SUSPENDED .' AND customer_id = ' . $customer_id);
		}

		return $query->getResultArray();
	}

	/**
	 * Gets the dinner table for the selected sale
	 */
	public function get_dinner_table(int $sale_id)	//TODO: this is returning null or the table_id.  We can keep it this way but multiple return types can't be declared until PHP 8.x
	{
		if($sale_id == NEW_ENTRY)
		{
			return null;
		}

		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		return $builder->get()->getRow()->dinner_table_id;
	}

	/**
	 * Gets the sale type for the selected sale
	 */
	public function get_sale_type(int $sale_id)
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		return $builder->get()->getRow()->sale_type;
	}

	/**
	 * Gets the sale status for the selected sale
	 */
	public function get_sale_status(int $sale_id): int
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		return $builder->get()->getRow()->sale_status;
	}

	/**
	 * @param int $sale_id
	 * @param int $sale_status
	 * @return void
	 */
	public function update_sale_status(int $sale_id, int $sale_status): void
	{
		$builder = $this->db->table('sales');

		$builder->where('sale_id', $sale_id);
		$builder->update(['sale_status' => $sale_status]);
	}

	/**
	 * Gets the quote_number for the selected sale
	 */
	public function get_quote_number(int $sale_id): ?string
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		$row = $builder->get()->getRow();

		if($row != null)
		{
			return $row->quote_number;
		}

		return null;
	}

	/**
	 * Gets the work order number for the selected sale
	 */
	public function get_work_order_number(int $sale_id): ?string
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		$row = $builder->get()->getRow();

		if($row != null)	//TODO: === ?
		{
			return $row->work_order_number;
		}

		return null;
	}

	/**
	 * Gets the quote_number for the selected sale
	 */
	public function get_comment(int $sale_id): ?string
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		$row = $builder->get()->getRow();

		if($row != null)	//TODO: === ?
		{
			return $row->comment;
		}

		return null;
	}

	/**
	 * Gets total of suspended invoices rows
	 */
	public function get_suspended_invoice_count(): int
	{
		$builder = $this->db->table('sales');
		$builder->where('invoice_number IS NOT NULL');
		$builder->where('sale_status', SUSPENDED);

		return $builder->countAllResults();
	}

	/**
	 * Removes a selected sale from the sales table.
	 * This function should only be called for suspended sales that are being restored to the current cart
	 */
	public function delete_suspended_sale(int $sale_id): bool
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();
		$config = config(OSPOS::class)->settings;

		if($config['dinner_table_enable'])
		{
			$dinner_table = model(Dinner_table::class);
			$dinner_table_id = $this->get_dinner_table($sale_id);
			$dinner_table->release($dinner_table_id);
		}

		$this->update_sale_status($sale_id, CANCELED);

		$this->db->transComplete();

		return $this->db->transStatus();
	}

	/**
	 * This clears the sales detail for a given sale_id before the detail is re-saved.
	 * This allows us to reuse the same sale_id
	 */
	public function clear_suspended_sale_detail(int $sale_id): bool
	{
		$this->db->transStart();
		$config = config(OSPOS::class)->settings;

		if($config['dinner_table_enable'])
		{
			$dinner_table = model(Dinner_table::class);
			$dinner_table_id = $this->get_dinner_table($sale_id);
			$dinner_table->release($dinner_table_id);
		}

		$builder = $this->db->table('sales_payments');
		$builder->delete(['sale_id' => $sale_id]);

		$builder = $this->db->table('sales_items_taxes');
		$builder->delete(['sale_id' => $sale_id]);

		$builder = $this->db->table('sales_items');
		$builder->delete(['sale_id' => $sale_id]);

		$builder = $this->db->table('sales_taxes');
		$builder->delete(['sale_id' => $sale_id]);

		$this->db->transComplete();

		return $this->db->transStatus();
	}

	/**
	 * Gets suspended sale info
	 */
	public function get_suspended_sale_info(int $sale_id): ResultInterface
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);
		$builder->join('people', 'people.person_id = sales.customer_id', 'LEFT');
		$builder->where('sale_status', SUSPENDED);

		return $builder->get();
	}

	/**
	 * @param int $customer_id
	 * @param int $sale_id
	 * @param float $total_amount
	 * @param float $total_amount_used
	 */
	private function save_customer_rewards(int $customer_id, int $sale_id, float $total_amount, float $total_amount_used): void
	{
		$config = config(OSPOS::class)->settings;

		if(!empty($customer_id) && $config['customer_reward_enable'])
		{
			$customer = model(Customer::class);
			$customer_rewards = model(Customer_rewards::class);
			$rewards = model(Rewards::class);

			$package_id = $customer->get_info($customer_id)->package_id;

			if(!empty($package_id))
			{
				$points_percent = $customer_rewards->get_points_percent($package_id);
				$points = $customer->get_info($customer_id)->points;
				$points = ($points == null ? 0 : $points);
				$points_percent = ($points_percent == null ? 0 : $points_percent);
				$total_amount_earned = ($total_amount * $points_percent / 100);
				$points = $points + $total_amount_earned;

				$customer->update_reward_points_value($customer_id, $points);

				$rewards_data = ['sale_id' => $sale_id, 'earned' => $total_amount_earned, 'used' => $total_amount_used];

				$rewards->save_value($rewards_data);
			}
		}
	}

	/**
	 * Creates a temporary table to store the sales_payments data
	 *
	 * @param string $where
	 * @return array
	 */
	private function create_temp_table_sales_payments_data(string $where): void
	{
		$builder = $this->db->table('sales_payments AS payments');
		$builder->select([
			'payments.sale_id',
			'SUM(CASE WHEN `payments`.`cash_adjustment` = 0 THEN `payments`.`payment_amount` ELSE 0 END) AS sale_payment_amount',
			'SUM(CASE WHEN `payments`.`cash_adjustment` = 1 THEN `payments`.`payment_amount` ELSE 0 END) AS sale_cash_adjustment',
			'GROUP_CONCAT(CONCAT(`payments`.`payment_type`, " ", (`payments`.`payment_amount` - `payments`.`cash_refund`)) SEPARATOR ", ") AS payment_type'
		]);
		$builder->join('sales', 'sales.sale_id = payments.sale_id', 'inner');
		$builder->where($where);
		$builder->groupBy('payments.sale_id');

		$sub_query = $builder->getCompiledSelect();

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '
			. $this->db->prefixTable('sales_payments_temp')
			. ' (PRIMARY KEY(`sale_id`), INDEX(`sale_id`)) AS (' . $sub_query . ')');
	}

	/**
	 * Temporary table to store the sales_items_taxes data
	 *
	 * @param string $where
	 * @return \CodeIgniter\Database\BaseBuilder
	 */
	private function create_temp_table_sales_items_taxes_data(string $where): void
	{

		$builder = $this->db->table('sales_items_taxes AS sales_items_taxes');
		$builder->select([
			'sales_items_taxes.sale_id AS sale_id',
			'sales_items_taxes.item_id AS item_id',
			'sales_items_taxes.line AS line',
			'SUM(sales_items_taxes.item_tax_amount) AS tax',
			'SUM(CASE WHEN sales_items_taxes.tax_type = 0 THEN sales_items_taxes.item_tax_amount ELSE 0 END) AS internal_tax',
			'SUM(CASE WHEN sales_items_taxes.tax_type = 1 THEN sales_items_taxes.item_tax_amount ELSE 0 END) AS sales_tax'
		]);
		$builder->join('sales', 'sales.sale_id = sales_items_taxes.sale_id', 'inner');
		$builder->join('sales_items', 'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line', 'inner');
		$builder->where($where);
		$builder->groupBy(['sale_id', 'item_id', 'line']);
		$sub_query = $builder->getCompiledSelect();

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '
			. $this->db->prefixTable('sales_items_taxes_temp')
			. ' (INDEX(sale_id), INDEX(item_id)) ENGINE=MEMORY AS (' . $sub_query . ')');
	}

	/**
	 * @param string $search
	 * @param array $filters
	 * @param BaseBuilder $builder
	 * @return void
	 */
	private function add_filters_to_query(string $search, array $filters, BaseBuilder $builder): void
	{
		if(!empty($search))    //TODO: this is duplicated code.  We should think about refactoring out a method
		{
			if($filters['is_valid_receipt'])
			{
				$pieces = explode(' ', $search);
				$builder->where('sales.sale_id', $pieces[1]);
			}
			else
			{
				$builder->groupStart();
				// customer last name
				$builder->like('customer_p.last_name', $search);
				// customer first name
				$builder->orLike('customer_p.first_name', $search);
				// customer first and last name
				$builder->orLike('CONCAT(customer_p.first_name, " ", customer_p.last_name)', $search);
				// customer company name
				$builder->orLike('customer.company_name', $search);
				$builder->groupEnd();
			}
		}

		if($filters['location_id'] != 'all')
		{
			$builder->where('sales_items.item_location', $filters['location_id']);
		}

		if($filters['only_invoices'])
		{
			$builder->where('sales.invoice_number IS NOT NULL');
		}

		if($filters['only_cash'])
		{
			$builder->groupStart();
			$builder->like('payments.payment_type', lang('Sales.cash'));
			$builder->orWhere('payments.payment_type IS NULL');
			$builder->groupEnd();
		}

		if($filters['only_creditcard'])
		{
			$builder->like('payments.payment_type', lang('Sales.credit'));
		}

		if($filters['only_due'])
		{
			$builder->like('payments.payment_type', lang('Sales.due'));
		}

		if($filters['only_check'])
		{
			$builder->like('payments.payment_type', lang('Sales.check'));
		}
	}
}
