<?php

namespace App\Models;

use CodeIgniter\Model;
use app\Libraries\Sale_lib;

/**
 * Sale class
 *
 * @property attribute attribute
 * @property appconfig appconfig
 * @property customer customer
 * @property customer_rewards customer_rewards
 * @property dinner_table dinner_table
 * @property employee employee
 * @property giftcard giftcard
 * @property inventory inventory
 * @property item item
 * @property item_quantity item_quantity
 * @property rewards rewards
 *
 * @property Sale_lib sale_lib
 */
class Sale extends Model
{
	public function __construct()
	{
		parent::__construct();

		$this->attribute = model('Attribute');
		$this->appconfig = model('Appconfig');
		$this->customer = model('Customer');
		$this->customer_rewards = model('Customer_rewards');
		$this->dinner_table = model('Dinner_table');
		$this->employee = model('Employee');
		$this->giftcard = model('Giftcard');
		$this->inventory = model('Inventory');
		$this->item = model('Item');
		$this->item_quantity = model('Item_quantity');
		$this->rewards = model('Rewards');

		$this->sale_lib = new Sale_lib();
	}

	/**
	 * Get sale info
	 */
	public function get_info(int $sale_id)
	{
		$this->create_temp_table (['sale_id' => $sale_id));

		$decimals = totals_decimals();
		$sales_tax = 'IFNULL(SUM(sales_items_taxes.sales_tax), 0)';
		$cash_adjustment = 'IFNULL(SUM(payments.sale_cash_adjustment), 0)';
		$sale_price = 'CASE WHEN sales_items.discount_type = ' . PERCENT
			. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals) "
			. 'ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END';

		if($this->appconfig->get('tax_included'))
		{
			$sale_total = "ROUND(SUM($sale_price), $decimals) + $cash_adjustment";
		}
		else
		{
			$sale_total = "ROUND(SUM($sale_price), $decimals) + $sales_tax + $cash_adjustment";
		}

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
				MAX(IFNULL(payments.sale_cash_adjustment, 0)) AS cash_adjustment,
				MAX(IFNULL(payments.sale_cash_refund, 0)) AS cash_refund,
				' . "
				$sale_total AS amount_due,
				MAX(IFNULL(payments.sale_payment_amount, 0)) AS amount_tendered,
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
	public function get_found_rows(string $search, array $filters)
	{
		return $this->search($search, $filters, 0, 0, 'sales.sale_time', 'desc', TRUE);
	}

	/**
	 * Get the sales data for the takings (sales/manage) view
	 */
	public function search(string $search, array $filters, int $rows = 0, int $limit_from = 0, string $sort = 'sales.sale_time', string $order = 'desc', bool $count_only = FALSE)
	{
		// Pick up only non-suspended records
		$where = 'sales.sale_status = 0 AND ';

		if(empty($this->appconfig->get('date_or_time_format')))
		{
			$where .= 'DATE(sales.sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']);
		}
		else
		{
			$where .= 'sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date']));
		}

		// NOTE: temporary tables are created to speed up searches due to the fact that they are orthogonal to the main query
		// create a temporary table to contain all the payments per sale item
		$sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sales_payments_temp') .
			' (PRIMARY KEY(sale_id), INDEX(sale_id))
			(
				SELECT payments.sale_id,
					SUM(CASE WHEN payments.cash_adjustment = 0 THEN payments.payment_amount ELSE 0 END) AS sale_payment_amount,
					SUM(CASE WHEN payments.cash_adjustment = 1 THEN payments.payment_amount ELSE 0 END) AS sale_cash_adjustment,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", (payments.payment_amount - payments.cash_refund)) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->prefixTable('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->prefixTable('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE ' . $where . '
				GROUP BY payments.sale_id)';

		$this->db->query($sql);

		$decimals = totals_decimals();

		$sale_price = 'CASE WHEN sales_items.discount_type = ' . PERCENT
			. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals) "
			. 'ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END';

		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';

		$tax = 'IFNULL(SUM(sales_items_taxes.tax), 0)';
		$sales_tax = 'IFNULL(SUM(sales_items_taxes.sales_tax), 0)';
		$internal_tax = 'IFNULL(SUM(sales_items_taxes.internal_tax), 0)';
		$cash_adjustment = 'IFNULL(SUM(payments.sale_cash_adjustment), 0)';

		$sale_subtotal = "ROUND(SUM($sale_price), $decimals) - $internal_tax";
		$sale_total = "ROUND(SUM($sale_price), $decimals) + $sales_tax + $cash_adjustment";

		// create a temporary table to contain all the sum of taxes per sale item
		$sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sales_items_taxes_temp') .
			' (INDEX(sale_id), INDEX(item_id)) ENGINE=MEMORY
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(sales_items_taxes.item_tax_amount) AS tax,
					SUM(CASE WHEN sales_items_taxes.tax_type = 0 THEN sales_items_taxes.item_tax_amount ELSE 0 END) AS internal_tax,
					SUM(CASE WHEN sales_items_taxes.tax_type = 1 THEN sales_items_taxes.item_tax_amount ELSE 0 END) AS sales_tax
				FROM ' . $this->db->prefixTable('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->prefixTable('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->prefixTable('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line)';

		$this->db->query($sql);

		$builder = $this->db->table('sales_items AS sales_items');

		// get_found_rows case
		if($count_only == TRUE)
		{
			$builder->select('COUNT(DISTINCT sales.sale_id) AS count');
		}
		else
		{
			$builder->select('
					sales.sale_id AS sale_id,
					MAX(DATE(sales.sale_time)) AS sale_date,
					MAX(sales.sale_time) AS sale_time,
					MAX(sales.invoice_number) AS invoice_number,
					MAX(sales.quote_number) AS quote_number,
					SUM(sales_items.quantity_purchased) AS items_purchased,
					MAX(CONCAT(customer_p.first_name, " ", customer_p.last_name)) AS customer_name,
					MAX(customer.company_name) AS company_name,
					' . "
					$sale_subtotal AS subtotal,
					$tax AS tax,
					$sale_total AS total,
					$sale_cost AS cost,
					($sale_total - $sale_cost) AS profit,
					$sale_total AS amount_due,
					MAX(payments.sale_payment_amount) AS amount_tendered,
					(MAX(payments.sale_payment_amount)) - ($sale_total) AS change_due,
					" . '
					MAX(payments.payment_type) AS payment_type
			');
		}

		$builder->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$builder->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'LEFT');
		$builder->join('customers AS customer', 'sales.customer_id = customer.person_id', 'LEFT');
		$builder->join('sales_payments_temp AS payments', 'sales.sale_id = payments.sale_id', 'LEFT OUTER');
		$builder->join('sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'LEFT OUTER');

		$builder->where($where);

		if(!empty($search))	//TODO: this is duplicated code.  We should think about refactoring out a method
		{
			if($filters['is_valid_receipt'] != FALSE)
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

		if($filters['only_invoices'] != FALSE)
		{
			$builder->where('sales.invoice_number IS NOT NULL');
		}

		if($filters['only_cash'] != FALSE)
		{
			$builder->groupStart();
				$builder->like('payments.payment_type', lang('Sales.cash'));
				$builder->orWhere('payments.payment_type IS NULL');
			$builder->groupEnd();
		}

		if($filters['only_creditcard'] != FALSE)
		{
			$builder->like('payments.payment_type', lang('Sales.credit'));
		}

		if($filters['only_due'] != FALSE)
		{
			$builder->like('payments.payment_type', lang('Sales.due'));
		}

		if($filters['only_check'] != FALSE)
		{
			$builder->like('payments.payment_type', lang('Sales.check'));
		}

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $builder->get()->getRow()->count;
		}

		$builder->groupBy('sales.sale_id');

		// order by sale time by default
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
		// get payment summary
		$builder = $this->db->table('sales AS sales');
		$builder->select('payment_type, COUNT(payment_amount) AS count, SUM(payment_amount - cash_refund) AS payment_amount');
		$builder->join('sales_payments', 'sales_payments.sale_id = sales.sale_id');
		$builder->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'LEFT');
		$builder->join('customers AS customer', 'sales.customer_id = customer.person_id', 'LEFT');

		if(empty($this->appconfig->get('date_or_time_format')))	//TODO: duplicated code.  We should think about refactoring out a method.
		{
			$builder->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$builder->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if(!empty($search))	//TODO: duplicated code.  We should think about refactoring out a method.
		{
			if($filters['is_valid_receipt'] != FALSE)
			{
				$pieces = explode(' ',$search);
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

		if($filters['only_invoices'] != FALSE)
		{
			$builder->where('invoice_number IS NOT NULL');
		}

		if($filters['only_cash'] != FALSE)
		{
			$builder->like('payment_type', lang('Sales.cash'));
		}

		if($filters['only_due'] != FALSE)
		{
			$builder->like('payment_type', lang('Sales.due'));
		}

		if($filters['only_check'] != FALSE)
		{
			$builder->like('payment_type', lang('Sales.check'));
		}

		if($filters['only_creditcard'] != FALSE)
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
			if(strstr($payment['payment_type'], lang('Sales.giftcard')) != FALSE)
			{
				$gift_card_count  += $payment['count'];
				$gift_card_amount += $payment['payment_amount'];

				// remove the "Gift Card: 1", "Gift Card: 2", etc. payment string
				unset($payments[$key]);
			}
		}

		if($gift_card_count > 0)
		{
			$payments[] = ['payment_type' => lang('Sales.giftcard'), 'count' => $gift_card_count, 'payment_amount' => $gift_card_amount);
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
	public function get_search_suggestions(string $search, int $limit = 25): array
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
				$suggestions[] = ['label' => $result['first_name'] . ' ' . $result['last_name']);
			}
		}
		else
		{
			$suggestions[] = ['label' => $search);
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
	public function get_sale_by_invoice_number(string $invoice_number)
	{
		$builder = $this->db->table('sales');
		$builder->where('invoice_number', $invoice_number);

		return $builder->get();
	}

	public function get_invoice_number_for_year(string $year = '', int $start_from = 0): int
	{
		return $this->get_number_for_year('invoice_number', $year, $start_from);
	}

	public function get_quote_number_for_year($year = '', $start_from = 0): int
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
		if(!empty($receipt_sale_id))
		{
			//POS #
			$pieces = explode(' ', $receipt_sale_id);

			if(count($pieces) == 2 && preg_match('/(POS)/i', $pieces[0]))
			{
				return $this->exists($pieces[1]);
			}
			elseif($this->appconfig->get('invoice_enable') == TRUE)
			{
				$sale_info = $this->get_sale_by_invoice_number($receipt_sale_id);
				if($sale_info->getNumRows() > 0)
				{
					$receipt_sale_id = 'POS ' . $sale_info->getRow()->sale_id;

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Checks if sale exists
	 */
	public function exists(int $sale_id): bool
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		return ($builder->get()->getNumRows() == 1);
	}

	/**
	 * Update sale
	 */
	public function update(int $sale_id, array $sale_data, array $payments): bool
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);
		$success = $builder->update($sale_data);

		// touch payment only if update sale is successful and there is a payments object otherwise the result would be to delete all the payments associated to the sale
		if($success && !empty($payments))
		{
			//Run these queries as a transaction, we want to make sure we do all or nothing
			$this->db->transStart();

			// add new payments
			foreach($payments as $payment)
			{
				$payment_id = $payment['payment_id'];
				$payment_type = $payment['payment_type'];
				$payment_amount = $payment['payment_amount'];
				$cash_refund = $payment['cash_refund'];
				$cash_adjustment = $payment['cash_adjustment'];
				$employee_id = $payment['employee_id'];

				if($payment_id == -1 && $payment_amount != 0)
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
					$builder = $this->db->table('sales_payments');
					$success = $builder->insert($sales_payments_data);
				}
				elseif($payment_id != -1)
				{
					$builder = $this->db->table('sales_payments');

					if($payment_amount != 0)
					{
						// Update existing payment transactions (payment_type only)
						$sales_payments_data = [
							'payment_type' => $payment_type,
							'payment_amount' => $payment_amount,
							'cash_refund' => $cash_refund,
							'cash_adjustment' => $cash_adjustment
						);

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
	 */
	public function save(int $sale_id, string &$sale_status, array &$items, int $customer_id, int $employee_id, string $comment, string $invoice_number,
							string $work_order_number, string $quote_number, int $sale_type, array $payments, int $dinner_table, array &$sales_taxes): int	//TODO: this method returns the sale_id but the override is expecting it to return a bool. The signature needs to be reworked.  Generally when there are more than 3 maybe 4 parameters, there's a good chance that an object needs to be passed rather than so many params.
	{
		if($sale_id != -1)
		{
			$this->clear_suspended_sale_detail($sale_id);
		}

		$tax_decimals = tax_decimals();

		if(count($items) == 0)
		{
			return -1;
		}

		$sales_data = [
			'sale_time' => date('Y-m-d H:i:s'),
			'customer_id' => $this->customer->exists($customer_id) ? $customer_id : NULL,
			'employee_id' => $employee_id,
			'comment' => $comment,
			'sale_status' => $sale_status,
			'invoice_number' => $invoice_number,
			'quote_number' => $quote_number,
			'work_order_number'=> $work_order_number,
			'dinner_table_id' => $dinner_table,
			'sale_type' => $sale_type
		];

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		$builder = $this->db->table('sales');

		if($sale_id == -1)	//TODO: I think we have a constant for this and the -1 needs to be replaced with the constant in constants.php... something like NEW_SALE
		{
			$builder->insert($sales_data);
			$sale_id = $this->db->insertID();
		}
		else
		{
			$builder->where('sale_id', $sale_id);
			$builder->update($sales_data);
		}

		$total_amount = 0;
		$total_amount_used = 0;

		foreach($payments as $payment_id => $payment)
		{
			if(!empty(strstr($payment['payment_type'], lang('Sales.giftcard'))))
			{
				// We have a gift card and we have to deduct the used value from the total value of the card.
				$splitpayment = explode( ':', $payment['payment_type'] );	//TODO: this variable doesn't follow our naming conventions.  Probably should be refactored to split_payment.
				$cur_giftcard_value = $this->giftcard->get_giftcard_value( $splitpayment[1] );	//TODO: this should be refactored to $current_giftcard_value
				$this->giftcard->update_giftcard_value( $splitpayment[1], $cur_giftcard_value - $payment['payment_amount'] );
			}
			elseif(!empty(strstr($payment['payment_type'], lang('Sales.rewards'))))
			{
				$cur_rewards_value = $this->customer->get_info($customer_id)->points;
				$this->customer->update_reward_points_value($customer_id, $cur_rewards_value - $payment['payment_amount'] );
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

			$total_amount = floatval($total_amount) + floatval($payment['payment_amount']);
		}

		$this->save_customer_rewards($customer_id, $sale_id, $total_amount, $total_amount_used);

		$customer = $this->customer->get_info($customer_id);

		foreach($items as $line=>$item)
		{
			$cur_item_info = $this->item->get_info($item['item_id']);

			if($item['price'] == 0.00)
			{
				$item['discount'] = 0.00;
			}

			$sales_items_data = [
				'sale_id' => $sale_id,
				'item_id' => $item['item_id'],
				'line' => $item['line'],
				'description' => character_limiter($item['description'], 255),
				'serialnumber' => character_limiter($item['serialnumber'], 30),
				'quantity_purchased' => $item['quantity'],
				'discount' => $item['discount'],
				'discount_type' => $item['discount_type'],
				'item_cost_price' => $item['cost_price'],
				'item_unit_price' => $item['price'],
				'item_location' => $item['item_location'],
				'print_option' => $item['print_option']
			);

			$builder = $this->db->table('sales_items');
			$builder->insert($sales_items_data);

			if($cur_item_info->stock_type == HAS_STOCK && $sale_status == COMPLETED)
			{
				// Update stock quantity if item type is a standard stock item and the sale is a standard sale
				$item_quantity = $this->item_quantity->get_item_quantity($item['item_id'], $item['item_location']);

				$this->item_quantity->save([
					'quantity'	=> $item_quantity->quantity - $item['quantity'],
					'item_id' => $item['item_id'],
					'location_id' => $item['item_location']],
					$item['item_id'],
					$item['item_location']
				);

				// if an items was deleted but later returned it's restored with this rule

				if($item['quantity'] < 0)
				{
					$this->item->undelete($item['item_id']);
				}

				// Inventory Count Details
				$sale_remarks = 'POS '.$sale_id;
				$inv_data = [
					'trans_date' => date('Y-m-d H:i:s'),
					'trans_items' => $item['item_id'],
					'trans_user' => $employee_id,
					'trans_location' => $item['item_location'],
					'trans_comment' => $sale_remarks,
					'trans_inventory' => -$item['quantity']
				];
				$this->inventory->insert($inv_data);	//TODO: Reflection exception needs to be caught if we keep the same inheritance in the insert function.
			}

			$this->attribute->copy_attribute_links($item['item_id'], 'sale_id', $sale_id);
		}

		if($customer_id == -1 || $customer->taxable)	//TODO: Need a NEW_CUSTOMER constant in constants.php instead of -1
		{
			$this->save_sales_tax($sale_id, $sales_taxes[0]);
			$this->save_sales_items_taxes($sale_id, $sales_taxes[1]);
		}

		if($this->appconfig->get('dinner_table_enable') == TRUE)
		{
			if($sale_status == COMPLETED)
			{
				$this->dinner_table->release($dinner_table);
			}
			else
			{
				$this->dinner_table->occupy($dinner_table);
			}
		}

		$this->db->transComplete();

		if($this->db->transStatus() === FALSE)
		{
			return -1;	//TODO: this should also be replaced with a FAIL or NO_PERSON constant or something similar instead of -1
		}

		return $sale_id;
	}

	/**
	 * Saves sale tax
	 */
	public function save_sales_tax(int $sale_id, array $sales_taxes)	//TODO: should we return the result of the insert here as a bool?
	{
		foreach($sales_taxes as $line => $sales_tax)
		{
			$sales_tax['sale_id'] = $sale_id;
			$builder = $this->db->table('sales_taxes');
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
	public function save_sales_items_taxes(int $sale_id, array $sales_item_taxes)
	{
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

			$builder = $this->db->table('sales_items_taxes');
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
	 */
	public function delete_list(array $sale_ids, int $employee_id, bool $update_inventory = TRUE): bool
	{
		$result = TRUE;

		foreach($sale_ids as $sale_id)
		{
			$result &= $this->delete($sale_id, $employee_id, $update_inventory);
		}

		return $result;
	}

	/**
	 * Restores list of sales
	 */
	public function restore_list(array $sale_ids, int $employee_id, bool $update_inventory = TRUE): bool	//TODO: $employee_id and $update_inventory are never used in the function.
	{
		foreach($sale_ids as $sale_id)
		{
			$this->update_sale_status($sale_id, SUSPENDED);
		}

		return TRUE;
	}

	/**
	 * Delete sale.  Hard deletes are not supported for sales transactions.
	 * When a sale is "deleted" it is simply changed to a status of canceled.
	 * However, if applicable the inventory still needs to be updated
	 */
	public function delete(int $sale_id, int $employee_id, bool $update_inventory = TRUE): bool
	{
		// start a transaction to assure data integrity
		$this->db->transStart();

		$sale_status = $this->get_sale_status($sale_id);

		if($update_inventory && $sale_status == COMPLETED)
		{
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_sale_items($sale_id)->getResultArray();
			foreach($items as $item)
			{
				$cur_item_info = $this->item->get_info($item['item_id']);

				if($cur_item_info->stock_type == HAS_STOCK)
				{
					// create query to update inventory tracking
					$inv_data = [
						'trans_date' => date('Y-m-d H:i:s'),
						'trans_items' => $item['item_id'],
						'trans_user' => $employee_id,
						'trans_comment' => 'Deleting sale ' . $sale_id,
						'trans_location' => $item['item_location'],
						'trans_inventory' => $item['quantity_purchased']
					);
					// update inventory
					$this->inventory->insert($inv_data);		//TODO: Probably need a try/catch for the reflection exception if we keep the inheritance of insert()

					// update quantities
					$this->item_quantity->change_quantity($item['item_id'], $item['item_location'], $item['quantity_purchased']);
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
	public function get_sale_items(int $sale_id)
	{
		$builder = $this->db->table('sales_items');
		$builder->where('sale_id', $sale_id);

		return $builder->get();
	}

	/**
	 * Used by the invoice and receipt programs
	 */
	public function get_sale_items_ordered(int $sale_id)
	{
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
			' . $this->item->get_item_name('name') . ',
			category,
			item_type,
			stock_type');
		$builder->join('items AS items', 'sales_items.item_id = items.item_id');
		$builder->where('sales_items.sale_id', $sale_id);

		// Entry sequence (this will render kits in the expected sequence)
		if($this->appconfig->get('line_sequence') == '0')
		{
			$builder->orderBy('line', 'asc');
		}
		// Group by Stock Type (nonstock first - type 1, stock next - type 0)
		elseif($this->appconfig->get('line_sequence') == '1')
		{
			$builder->orderBy('stock_type', 'desc');
			$builder->orderBy('sales_items.description', 'asc');
			$builder->orderBy('items.name', 'asc');
			$builder->orderBy('items.qty_per_pack', 'asc');
		}
		// Group by Item Category
		elseif($this->appconfig->get('line_sequence') == '2')
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
	public function get_sale_payments(int $sale_id)
	{
		$builder = $this->db->table('sales_payments');
		$builder->where('sale_id', $sale_id);

		return $builder->get();
	}

	/**
	 * Gets sale payment options
	 */
	public function get_payment_options(bool $giftcard = TRUE, bool $reward_points = FALSE): array
	{
		$payments = get_payment_options();

		if($giftcard == TRUE)
		{
			$payments[lang('Sales.giftcard')] = lang('Sales.giftcard');
		}

		if($reward_points == TRUE)
		{
			$payments[lang('Sales.rewards')] = lang('Sales.rewards');
		}

		if($this->sale_lib->get_mode() == 'sale_work_order')
		{
			$payments[lang('Sales.cash_deposit')] = lang('Sales.cash_deposit');
			$payments[lang('Sales.credit_deposit')] = lang('Sales.credit_deposit');
		}

		return $payments;
	}

	/**
	 * Gets sale customer name
	 */
	public function get_customer(int $sale_id)
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		return $this->customer->get_info($builder->get()->getRow()->customer_id);
	}

	/**
	 * Gets sale employee name
	 */
	public function get_employee(int $sale_id)
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		return $this->employee->get_info($builder->get()->getRow()->employee_id);
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

		return ($builder->get()->getNumRows() == 1);	//TODO: Probably should be === here.
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

		return ($builder->get()->getNumRows() == 1);	//TODO: Probably should be === here.
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

		return ($builder->get()->getNumRows() == 1);	//TODO: Probably should be === here.
	}

	/**
	 * Gets Giftcard value
	 */
	public function get_giftcard_value(string $giftcardNumber): float
	{
		if(!$this->giftcard->exists($this->giftcard->get_giftcard_id($giftcardNumber)))	//TODO: camelCase is used here for the variable name but we are using _ everywhere else. CI4 moved to camelCase... we should pick one and do that.
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
	public function create_temp_table(array $inputs)
	{
		if(empty($inputs['sale_id']))
		{
			if(empty($this->appconfig->get('date_or_time_format')))
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

		$tax = 'IFNULL(SUM(sales_items_taxes.tax), 0)';
		$sales_tax = 'IFNULL(SUM(sales_items_taxes.sales_tax), 0)';
		$internal_tax = 'IFNULL(SUM(sales_items_taxes.internal_tax), 0)';

		$cash_adjustment = 'IFNULL(SUM(payments.sale_cash_adjustment), 0)';

		if($this4get('tax_included'))
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
					MAX(' . $this->item->get_item_name() . ') AS name,
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
	public function get_all_suspended($customer_id = NULL): array
	{
		if($customer_id == -1)	//TODO: This should be converted to a global constant and stored in constants.php
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
	public function get_dinner_table(int $sale_id)	//TODO: this is returning NULL or the table_id.  We can keep it this way but multiple return types can't be declared until PHP 8.x
	{
		if($sale_id == -1)
		{
			return NULL;
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

	public function update_sale_status(int $sale_id, int $sale_status)
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

		if($row != NULL)
		{
			return $row->quote_number;
		}

		return NULL;
	}

	/**
	 * Gets the work order number for the selected sale
	 */
	public function get_work_order_number(int $sale_id): ?string
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		$row = $builder->get()->getRow();

		if($row != NULL)
		{
			return $row->work_order_number;
		}

		return NULL;
	}

	/**
	 * Gets the quote_number for the selected sale
	 */
	public function get_comment(int $sale_id): ?string
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);

		$row = $builder->get()->getRow();

		if($row != NULL)
		{
			return $row->comment;
		}

		return NULL;
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

		if($this->appconfig->get('dinner_table_enable') == TRUE)
		{
			$dinner_table = $this->get_dinner_table($sale_id);
			$this->dinner_table->release($dinner_table);
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

		if($this->appconfig->get('dinner_table_enable') == TRUE)
		{
			$dinner_table = $this->get_dinner_table($sale_id);
			$this->dinner_table->release($dinner_table);
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
	public function get_suspended_sale_info(int $sale_id)
	{
		$builder = $this->db->table('sales');
		$builder->where('sale_id', $sale_id);
		$builder->join('people', 'people.person_id = sales.customer_id', 'LEFT');
		$builder->where('sale_status', SUSPENDED);

		return $builder->get();
	}

	/**
	 * @param $customer_id
	 * @param $sale_id
	 * @param $total_amount
	 * @param $total_amount_used
	 */
	private function save_customer_rewards(int $customer_id, int $sale_id, float $total_amount, float $total_amount_used)
	{
		if(!empty($customer_id) && $this->appconfig->get('customer_reward_enable') == TRUE)
		{
			$package_id = $this->customer->get_info($customer_id)->package_id;

			if(!empty($package_id))
			{
				$points_percent = $this->customer_rewards->get_points_percent($package_id);
				$points = $this->customer->get_info($customer_id)->points;
				$points = ($points == NULL ? 0 : $points);
				$points_percent = ($points_percent == NULL ? 0 : $points_percent);
				$total_amount_earned = ($total_amount * $points_percent / 100);
				$points = $points + $total_amount_earned;
				$this->customer->update_reward_points_value($customer_id, $points);
				$rewards_data = ['sale_id' => $sale_id, 'earned' => $total_amount_earned, 'used' => $total_amount_used);

				$this->rewards->save($rewards_data);		//TODO: probably should wrap this in a try/catch if we are going to keep the inheritance.
			}
		}
	}
}
?>