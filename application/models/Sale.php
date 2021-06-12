<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Sale class
 */
class Sale extends CI_Model
{
	/**
	 * Get sale info
	 */
	public function get_info($sale_id)
	{
		$this->create_temp_table(array('sale_id' => $sale_id));

		$decimals = totals_decimals();
		$sales_tax = 'IFNULL(SUM(sales_items_taxes.sales_tax), 0)';
		$cash_adjustment = 'IFNULL(SUM(payments.sale_cash_adjustment), 0)';
		$sale_price = 'CASE WHEN sales_items.discount_type = ' . PERCENT
			. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals) "
			. 'ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END';

		if($this->config->item('tax_included'))
		{
			$sale_total = "ROUND(SUM($sale_price), $decimals) + $cash_adjustment";
		}
		else
		{
			$sale_total = "ROUND(SUM($sale_price), $decimals) + $sales_tax + $cash_adjustment";
		}

		$this->db->select('
				sales.sale_id AS sale_id,
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
				MAX(payments.payment_type) AS payment_type
		');

		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$this->db->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'LEFT');
		$this->db->join('customers AS customer', 'sales.customer_id = customer.person_id', 'LEFT');
		$this->db->join('sales_payments_temp AS payments', 'sales.sale_id = payments.sale_id', 'LEFT OUTER');
		$this->db->join('sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'LEFT OUTER');

		$this->db->where('sales.sale_id', $sale_id);

		$this->db->group_by('sales.sale_id');
		$this->db->order_by('sales.sale_time', 'asc');

		return $this->db->get();
	}

	/**
	 * Get number of rows for the takings (sales/manage) view
	 */
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters, 0, 0, 'sales.sale_time', 'desc', TRUE);
	}

	/**
	 * Get the sales data for the takings (sales/manage) view
	 */
	public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'sales.sale_time', $order = 'desc', $count_only = FALSE)
	{
		// Pick up only non-suspended records
		$where = 'sales.sale_status = 0 AND ';

		if(empty($this->config->item('date_or_time_format')))
		{
			$where .= 'DATE(sales.sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']);
		}
		else
		{
			$where .= 'sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date']));
		}

		// NOTE: temporary tables are created to speed up searches due to the fact that they are orthogonal to the main query
		// create a temporary table to contain all the payments per sale item
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_payments_temp') .
			' (PRIMARY KEY(sale_id), INDEX(sale_id))
			(
				SELECT payments.sale_id,
					SUM(CASE WHEN payments.cash_adjustment = 0 THEN payments.payment_amount ELSE 0 END) AS sale_payment_amount,
					SUM(CASE WHEN payments.cash_adjustment = 1 THEN payments.payment_amount ELSE 0 END) AS sale_cash_adjustment,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", (payments.payment_amount - payments.cash_refund)) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->dbprefix('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE ' . $where . '
				GROUP BY payments.sale_id
			)'
		);

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
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') .
			' (INDEX(sale_id), INDEX(item_id)) ENGINE=MEMORY
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(sales_items_taxes.item_tax_amount) AS tax,
					SUM(CASE WHEN sales_items_taxes.tax_type = 0 THEN sales_items_taxes.item_tax_amount ELSE 0 END) AS internal_tax,
					SUM(CASE WHEN sales_items_taxes.tax_type = 1 THEN sales_items_taxes.item_tax_amount ELSE 0 END) AS sales_tax
				FROM ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)'
		);

		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(DISTINCT sales.sale_id) AS count');
		}
		else
		{
			$this->db->select('
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

		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$this->db->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'LEFT');
		$this->db->join('customers AS customer', 'sales.customer_id = customer.person_id', 'LEFT');
		$this->db->join('sales_payments_temp AS payments', 'sales.sale_id = payments.sale_id', 'LEFT OUTER');
		$this->db->join('sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'LEFT OUTER');

		$this->db->where($where);

		if(!empty($search))
		{
			if($filters['is_valid_receipt'] != FALSE)
			{
				$pieces = explode(' ', $search);
				$this->db->where('sales.sale_id', $pieces[1]);
			}
			else
			{
				$this->db->group_start();
					// customer last name
					$this->db->like('customer_p.last_name', $search);
					// customer first name
					$this->db->or_like('customer_p.first_name', $search);
					// customer first and last name
					$this->db->or_like('CONCAT(customer_p.first_name, " ", customer_p.last_name)', $search);
					// customer company name
					$this->db->or_like('customer.company_name', $search);
				$this->db->group_end();
			}
		}

		if($filters['location_id'] != 'all')
		{
			$this->db->where('sales_items.item_location', $filters['location_id']);
		}

		if($filters['only_invoices'] != FALSE)
		{
			$this->db->where('sales.invoice_number IS NOT NULL');
		}

		if($filters['only_cash'] != FALSE)
		{
			$this->db->group_start();
				$this->db->like('payments.payment_type', $this->lang->line('sales_cash'));
				$this->db->or_where('payments.payment_type IS NULL');
			$this->db->group_end();
		}

		if($filters['only_creditcard'] != FALSE)
		{
			$this->db->like('payments.payment_type', $this->lang->line('sales_credit'));
		}

		if($filters['only_due'] != FALSE)
		{
			$this->db->like('payments.payment_type', $this->lang->line('sales_due'));
		}

		if($filters['only_check'] != FALSE)
		{
			$this->db->like('payments.payment_type', $this->lang->line('sales_check'));
		}

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $this->db->get()->row()->count;
		}

		$this->db->group_by('sales.sale_id');

		// order by sale time by default
		$this->db->order_by($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/**
	 * Get the payment summary for the takings (sales/manage) view
	 */
	public function get_payments_summary($search, $filters)
	{
		// get payment summary
		$this->db->select('payment_type, COUNT(payment_amount) AS count, SUM(payment_amount - cash_refund) AS payment_amount');
		$this->db->from('sales AS sales');
		$this->db->join('sales_payments', 'sales_payments.sale_id = sales.sale_id');
		$this->db->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'LEFT');
		$this->db->join('customers AS customer', 'sales.customer_id = customer.person_id', 'LEFT');

		if(empty($this->config->item('date_or_time_format')))
		{
			$this->db->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$this->db->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if(!empty($search))
		{
			if($filters['is_valid_receipt'] != FALSE)
			{
				$pieces = explode(' ',$search);
				$this->db->where('sales.sale_id', $pieces[1]);
			}
			else
			{
				$this->db->group_start();
					// customer last name
					$this->db->like('customer_p.last_name', $search);
					// customer first name
					$this->db->or_like('customer_p.first_name', $search);
					// customer first and last name
					$this->db->or_like('CONCAT(customer_p.first_name, " ", customer_p.last_name)', $search);
					// customer company name
					$this->db->or_like('customer.company_name', $search);
				$this->db->group_end();
			}
		}

		if($filters['sale_type'] == 'sales')
		{
			$this->db->where('sales.sale_status = ' . COMPLETED . ' AND payment_amount > 0');
		}
		elseif($filters['sale_type'] == 'quotes')
		{
			$this->db->where('sales.sale_status = ' . SUSPENDED . ' AND sales.quote_number IS NOT NULL');
		}
		elseif($filters['sale_type'] == 'returns')
		{
			$this->db->where('sales.sale_status = ' . COMPLETED . ' AND payment_amount < 0');
		}
		elseif($filters['sale_type'] == 'all')
		{
			$this->db->where('sales.sale_status = ' . COMPLETED);
		}

		if($filters['only_invoices'] != FALSE)
		{
			$this->db->where('invoice_number IS NOT NULL');
		}

		if($filters['only_cash'] != FALSE)
		{
			$this->db->like('payment_type', $this->lang->line('sales_cash'));
		}

		if($filters['only_due'] != FALSE)
		{
			$this->db->like('payment_type', $this->lang->line('sales_due'));
		}

		if($filters['only_check'] != FALSE)
		{
			$this->db->like('payment_type', $this->lang->line('sales_check'));
		}

		if($filters['only_creditcard'] != FALSE)
		{
			$this->db->like('payment_type', $this->lang->line('sales_credit'));
		}

		$this->db->group_by('payment_type');

		$payments = $this->db->get()->result_array();

		// consider Gift Card as only one type of payment and do not show "Gift Card: 1, Gift Card: 2, etc." in the total
		$gift_card_count = 0;
		$gift_card_amount = 0;
		foreach($payments as $key=>$payment)
		{
			if(strstr($payment['payment_type'], $this->lang->line('sales_giftcard')) != FALSE)
			{
				$gift_card_count  += $payment['count'];
				$gift_card_amount += $payment['payment_amount'];

				// remove the "Gift Card: 1", "Gift Card: 2", etc. payment string
				unset($payments[$key]);
			}
		}

		if($gift_card_count > 0)
		{
			$payments[] = array('payment_type' => $this->lang->line('sales_giftcard'), 'count' => $gift_card_count, 'payment_amount' => $gift_card_amount);
		}

		return $payments;
	}

	/**
	 * Gets total of rows
	 */
	public function get_total_rows()
	{
		$this->db->from('sales');

		return $this->db->count_all_results();
	}

	/**
	 * Gets search suggestions
	 */
	public function get_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		if(!$this->is_valid_receipt($search))
		{
			$this->db->distinct();
			$this->db->select('first_name, last_name');
			$this->db->from('sales');
			$this->db->join('people', 'people.person_id = sales.customer_id');
			$this->db->like('last_name', $search);
			$this->db->or_like('first_name', $search);
			$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
			$this->db->or_like('company_name', $search);
			$this->db->order_by('last_name', 'asc');

			foreach($this->db->get()->result_array() as $result)
			{
				$suggestions[] = array('label' => $result['first_name'] . ' ' . $result['last_name']);
			}
		}
		else
		{
			$suggestions[] = array('label' => $search);
		}

		return $suggestions;
	}

	/**
	 * Gets total of invoice rows
	 */
	public function get_invoice_count()
	{
		$this->db->from('sales');
		$this->db->where('invoice_number IS NOT NULL');

		return $this->db->count_all_results();
	}

	/**
	 * Gets sale by invoice number
	 */
	public function get_sale_by_invoice_number($invoice_number)
	{
		$this->db->from('sales');
		$this->db->where('invoice_number', $invoice_number);

		return $this->db->get();
	}

	public function get_invoice_number_for_year($year = '', $start_from = 0)
	{
		return $this->get_number_for_year('invoice_number', $year, $start_from);
	}

	public function get_quote_number_for_year($year = '', $start_from = 0)
	{
		return $this->get_number_for_year('quote_number', $year, $start_from);
	}

	/**
	 * Gets invoice number by year
	 */
	private function get_number_for_year($field, $year = '', $start_from = 0)
	{
		$year = $year == '' ? date('Y') : $year;
		$this->db->select('COUNT( 1 ) AS number_year');
		$this->db->from('sales');
		$this->db->where('DATE_FORMAT(sale_time, "%Y" ) = ', $year);
		$this->db->where("$field IS NOT NULL");
		$result = $this->db->get()->row_array();

		return ($start_from + $result['number_year']);
	}

	/**
	 * Checks if valid receipt
	 */
	public function is_valid_receipt(&$receipt_sale_id)
	{
		if(!empty($receipt_sale_id))
		{
			//POS #
			$pieces = explode(' ', $receipt_sale_id);

			if(count($pieces) == 2 && preg_match('/(POS)/i', $pieces[0]))
			{
				return $this->exists($pieces[1]);
			}
			elseif($this->config->item('invoice_enable') == TRUE)
			{
				$sale_info = $this->get_sale_by_invoice_number($receipt_sale_id);
				if($sale_info->num_rows() > 0)
				{
					$receipt_sale_id = 'POS ' . $sale_info->row()->sale_id;

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Checks if sale exists
	 */
	public function exists($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return ($this->db->get()->num_rows()==1);
	}

	/**
	 * Update sale
	 */
	public function update($sale_id, $sale_data, $payments)
	{
		$this->db->where('sale_id', $sale_id);
		$success = $this->db->update('sales', $sale_data);

		// touch payment only if update sale is successful and there is a payments object otherwise the result would be to delete all the payments associated to the sale
		if($success && !empty($payments))
		{
			//Run these queries as a transaction, we want to make sure we do all or nothing
			$this->db->trans_start();

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
					$sales_payments_data = array(
						'sale_id'		  => $sale_id,
						'payment_type'	  => $payment_type,
						'payment_amount'  => $payment_amount,
						'cash_refund'	  => $cash_refund,
						'cash_adjustment' => $cash_adjustment,
						'employee_id'	  => $employee_id
					);
					$success = $this->db->insert('sales_payments', $sales_payments_data);
				}
				elseif($payment_id != -1)
				{
					if($payment_amount != 0)
					{
						// Update existing payment transactions (payment_type only)
						$sales_payments_data = array(
							'payment_type' => $payment_type,
							'payment_amount' => $payment_amount,
							'cash_refund' => $cash_refund,
							'cash_adjustment' => $cash_adjustment
						);
						$this->db->where('payment_id', $payment_id);
						$success = $this->db->update('sales_payments', $sales_payments_data);
					}
					else
					{
						// Remove existing payment transactions with a payment amount of zero
						$success = $this->db->delete('sales_payments', array('payment_id' => $payment_id));
					}
				}
			}

			$this->db->trans_complete();

			$success &= $this->db->trans_status();
		}
		return $success;
	}

	/**
	 * Save the sale information after the sales is complete but before the final document is printed
	 * The sales_taxes variable needs to be initialized to an empty array before calling
	 */
	public function save($sale_id, &$sale_status, &$items, $customer_id, $employee_id, $comment, $invoice_number,
							$work_order_number, $quote_number, $sale_type, $payments, $dinner_table, &$sales_taxes)
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

		$sales_data = array(
			'sale_time'			=> date('Y-m-d H:i:s'),
			'customer_id'		=> $this->Customer->exists($customer_id) ? $customer_id : NULL,
			'employee_id'		=> $employee_id,
			'comment'			=> $comment,
			'sale_status'		=> $sale_status,
			'invoice_number'	=> $invoice_number,
			'quote_number'		=> $quote_number,
			'work_order_number'	=> $work_order_number,
			'dinner_table_id'	=> $dinner_table,
			'sale_status'		=> $sale_status,
			'sale_type'			=> $sale_type
		);

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		if($sale_id == -1)
		{
			$this->db->insert('sales', $sales_data);
			$sale_id = $this->db->insert_id();
		}
		else
		{
			$this->db->where('sale_id', $sale_id);
			$this->db->update('sales', $sales_data);
		}
		$total_amount = 0;
		$total_amount_used = 0;
		foreach($payments as $payment_id=>$payment)
		{
			if(!empty(strstr($payment['payment_type'], $this->lang->line('sales_giftcard'))))
			{
				// We have a gift card and we have to deduct the used value from the total value of the card.
				$splitpayment = explode( ':', $payment['payment_type'] );
				$cur_giftcard_value = $this->Giftcard->get_giftcard_value( $splitpayment[1] );
				$this->Giftcard->update_giftcard_value( $splitpayment[1], $cur_giftcard_value - $payment['payment_amount'] );
			}
			elseif(!empty(strstr($payment['payment_type'], $this->lang->line('sales_rewards'))))
			{
				$cur_rewards_value = $this->Customer->get_info($customer_id)->points;
				$this->Customer->update_reward_points_value($customer_id, $cur_rewards_value - $payment['payment_amount'] );
				$total_amount_used = floatval($total_amount_used) + floatval($payment['payment_amount']);
			}

			$sales_payments_data = array(
				'sale_id'		  => $sale_id,
				'payment_type'	  => $payment['payment_type'],
				'payment_amount'  => $payment['payment_amount'],
				'cash_refund'     => $payment['cash_refund'],
				'cash_adjustment' => $payment['cash_adjustment'],
				'employee_id'	  => $employee_id
			);

			$this->db->insert('sales_payments', $sales_payments_data);

			$total_amount = floatval($total_amount) + floatval($payment['payment_amount']);
		}

		$this->save_customer_rewards($customer_id, $sale_id, $total_amount, $total_amount_used);

		$customer = $this->Customer->get_info($customer_id);

		foreach($items as $line=>$item)
		{
			$cur_item_info = $this->Item->get_info($item['item_id']);

			if($item['price'] == 0.00)
			{
				$item['discount'] = 0.00;
			}

			$sales_items_data = array(
				'sale_id'			=> $sale_id,
				'item_id'			=> $item['item_id'],
				'line'				=> $item['line'],
				'description'		=> character_limiter($item['description'], 255),
				'serialnumber'		=> character_limiter($item['serialnumber'], 30),
				'quantity_purchased'=> $item['quantity'],
				'discount'			=> $item['discount'],
				'discount_type'		=> $item['discount_type'],
				'item_cost_price'	=> $item['cost_price'],
				'item_unit_price'	=> $item['price'],
				'item_location'		=> $item['item_location'],
				'print_option'		=> $item['print_option']
			);

			$this->db->insert('sales_items', $sales_items_data);

			if($cur_item_info->stock_type == HAS_STOCK && $sale_status == COMPLETED)
			{
				// Update stock quantity if item type is a standard stock item and the sale is a standard sale
				$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);
				$this->Item_quantity->save(array('quantity'	=> $item_quantity->quantity - $item['quantity'],
					'item_id'		=> $item['item_id'],
					'location_id'	=> $item['item_location']), $item['item_id'], $item['item_location']);

				// if an items was deleted but later returned it's restored with this rule

				if($item['quantity'] < 0)
				{
					$this->Item->undelete($item['item_id']);
				}

				// Inventory Count Details
				$sale_remarks = 'POS '.$sale_id;
				$inv_data = array(
					'trans_date'		=> date('Y-m-d H:i:s'),
					'trans_items'		=> $item['item_id'],
					'trans_user'		=> $employee_id,
					'trans_location'	=> $item['item_location'],
					'trans_comment'		=> $sale_remarks,
					'trans_inventory'	=> -$item['quantity']
				);
				$this->Inventory->insert($inv_data);
			}

			$this->Attribute->copy_attribute_links($item['item_id'], 'sale_id', $sale_id);
		}

		if($customer_id == -1 || $customer->taxable)
		{
			$this->save_sales_tax($sale_id, $sales_taxes[0]);
			$this->save_sales_items_taxes($sale_id, $sales_taxes[1]);
		}

		if($this->config->item('dinner_table_enable') == TRUE)
		{
			if($sale_status == COMPLETED)
			{
				$this->Dinner_table->release($dinner_table);
			}
			else
			{
				$this->Dinner_table->occupy($dinner_table);
			}
		}

		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE)
		{
			return -1;
		}

		return $sale_id;
	}

	/**
	 * Saves sale tax
	 */
	public function save_sales_tax($sale_id, $sales_taxes)
	{
		foreach($sales_taxes as $line=>$sales_tax)
		{
			$sales_tax['sale_id'] = $sale_id;
			$this->db->insert('sales_taxes', $sales_tax);
		}
	}

	/**
	 * Apply customer sales tax if the customer sales tax is enabledl
	 * The original tax is still supported if the user configures it,
	 * but it won't make sense unless it's used exclusively for the purpose
	 * of VAT tax which becomes a price component.  VAT taxes must still be reported
	 * as a separate tax entry on the invoice.
	 */
	public function save_sales_items_taxes($sale_id, $sales_item_taxes)
	{
		foreach($sales_item_taxes as $line => $tax_item)
		{
			$sales_items_taxes = array(
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
				'jurisdiction_id' => $tax_item['jurisdiction_id'],
				'tax_category_id' => $tax_item['tax_category_id']
			);

			$this->db->insert('sales_items_taxes', $sales_items_taxes);
		}
	}

	/**
	 * Return the taxes that were charged
	 */
	public function get_sales_taxes($sale_id)
	{
		$this->db->from('sales_taxes');
		$this->db->where('sale_id', $sale_id);
		$this->db->order_by('print_sequence', 'asc');

		$query = $this->db->get();

		return $query->result_array();
	}

	/**
	 * Return the taxes applied to a sale for a particular item
	 */
	public function get_sales_item_taxes($sale_id, $item_id)
	{
		$this->db->select('item_id, name, percent');
		$this->db->from('sales_items_taxes');
		$this->db->where('sale_id',$sale_id);
		$this->db->where('item_id',$item_id);

		//return an array of taxes for an item
		return $this->db->get()->result_array();
	}

	/**
	 * Deletes list of sales
	 */
	public function delete_list($sale_ids, $employee_id, $update_inventory = TRUE)
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
	public function restore_list($sale_ids, $employee_id, $update_inventory = TRUE)
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
	public function delete($sale_id, $employee_id, $update_inventory = TRUE)
	{
		// start a transaction to assure data integrity
		$this->db->trans_start();

		$sale_status = $this->get_sale_status($sale_id);

		if($update_inventory && $sale_status == COMPLETED)
		{
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_sale_items($sale_id)->result_array();
			foreach($items as $item)
			{
				$cur_item_info = $this->Item->get_info($item['item_id']);

				if($cur_item_info->stock_type == HAS_STOCK)
				{
					// create query to update inventory tracking
					$inv_data = array(
						'trans_date' => date('Y-m-d H:i:s'),
						'trans_items' => $item['item_id'],
						'trans_user' => $employee_id,
						'trans_comment' => 'Deleting sale ' . $sale_id,
						'trans_location' => $item['item_location'],
						'trans_inventory' => $item['quantity_purchased']
					);
					// update inventory
					$this->Inventory->insert($inv_data);

					// update quantities
					$this->Item_quantity->change_quantity($item['item_id'], $item['item_location'], $item['quantity_purchased']);
				}
			}
		}

		$this->update_sale_status($sale_id, CANCELED);

		// execute transaction
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	/**
	 * Gets sale item
	 */
	public function get_sale_items($sale_id)
	{
		$this->db->from('sales_items');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get();
	}

	/**
	 * Used by the invoice and receipt programs
	 */
	public function get_sale_items_ordered($sale_id)
	{
		$this->db->select('
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
			' . $this->Item->get_item_name('name') . ',
			category,
			item_type,
			stock_type');
		$this->db->from('sales_items AS sales_items');
		$this->db->join('items AS items', 'sales_items.item_id = items.item_id');
		$this->db->where('sales_items.sale_id', $sale_id);

		// Entry sequence (this will render kits in the expected sequence)
		if($this->config->item('line_sequence') == '0')
		{
			$this->db->order_by('line', 'asc');
		}
		// Group by Stock Type (nonstock first - type 1, stock next - type 0)
		elseif($this->config->item('line_sequence') == '1')
		{
			$this->db->order_by('stock_type', 'desc');
			$this->db->order_by('sales_items.description', 'asc');
			$this->db->order_by('items.name', 'asc');
			$this->db->order_by('items.qty_per_pack', 'asc');
		}
		// Group by Item Category
		elseif($this->config->item('line_sequence') == '2')
		{
			$this->db->order_by('category', 'asc');
			$this->db->order_by('sales_items.description', 'asc');
			$this->db->order_by('items.name', 'asc');
			$this->db->order_by('items.qty_per_pack', 'asc');
		}
		// Group by entry sequence in descending sequence (the Standard)
		else
		{
			$this->db->order_by('line', 'desc');
		}

		return $this->db->get();
	}

	/**
	 * Gets sale payments
	 */
	public function get_sale_payments($sale_id)
	{
		$this->db->from('sales_payments');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get();
	}

	/**
	 * Gets sale payment options
	 */
	public function get_payment_options($giftcard = TRUE, $reward_points = FALSE)
	{
		$payments = get_payment_options();

		if($giftcard == TRUE)
		{
			$payments[$this->lang->line('sales_giftcard')] = $this->lang->line('sales_giftcard');
		}

		if($reward_points == TRUE)
		{
			$payments[$this->lang->line('sales_rewards')] = $this->lang->line('sales_rewards');
		}

		if($this->sale_lib->get_mode() == 'sale_work_order')
		{
			$payments[$this->lang->line('sales_cash_deposit')] = $this->lang->line('sales_cash_deposit');
			$payments[$this->lang->line('sales_credit_deposit')] = $this->lang->line('sales_credit_deposit');
		}

		return $payments;
	}

	/**
	 * Gets sale customer name
	 */
	public function get_customer($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->Customer->get_info($this->db->get()->row()->customer_id);
	}

	/**
	 * Gets sale employee name
	 */
	public function get_employee($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->Employee->get_info($this->db->get()->row()->employee_id);
	}

	/**
	 * Checks if quote number exists
	 */
	public function check_quote_number_exists($quote_number, $sale_id = '')
	{
		$this->db->from('sales');
		$this->db->where('quote_number', $quote_number);
		if(!empty($sale_id))
		{
			$this->db->where('sale_id !=', $sale_id);
		}

		return ($this->db->get()->num_rows() == 1);
	}

	/**
	 * Checks if invoice number exists
	 */
	public function check_invoice_number_exists($invoice_number, $sale_id = '')
	{
		$this->db->from('sales');
		$this->db->where('invoice_number', $invoice_number);
		if(!empty($sale_id))
		{
			$this->db->where('sale_id !=', $sale_id);
		}

		return ($this->db->get()->num_rows() == 1);
	}

	/**
	 * Checks if work order number exists
	 */
	public function check_work_order_number_exists($work_order_number, $sale_id = '')
	{
		$this->db->from('sales');
		$this->db->where('invoice_number', $work_order_number);
		if(!empty($sale_id))
		{
			$this->db->where('sale_id !=', $sale_id);
		}

		return ($this->db->get()->num_rows() == 1);
	}

	/**
	 * Gets Giftcard value
	 */
	public function get_giftcard_value($giftcardNumber)
	{
		if(!$this->Giftcard->exists($this->Giftcard->get_giftcard_id($giftcardNumber)))
		{
			return 0;
		}

		$this->db->from('giftcards');
		$this->db->where('giftcard_number', $giftcardNumber);

		return $this->db->get()->row()->value;
	}

	/**
	 * Creates sales temporary dimensional table
	 * We create a temp table that allows us to do easy report/sales queries
	 */
	public function create_temp_table(array $inputs)
	{
		if(empty($inputs['sale_id']))
		{
			if(empty($this->config->item('date_or_time_format')))
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

		if($this->config->item('tax_included'))
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
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') .
			' (INDEX(sale_id), INDEX(item_id)) ENGINE=MEMORY
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(ROUND(sales_items_taxes.item_tax_amount, ' . $decimals . ')) AS tax,
					SUM(ROUND(CASE WHEN sales_items_taxes.tax_type = 0 THEN sales_items_taxes.item_tax_amount ELSE 0 END, ' . $decimals . ')) AS internal_tax,
					SUM(ROUND(CASE WHEN sales_items_taxes.tax_type = 1 THEN sales_items_taxes.item_tax_amount ELSE 0 END, ' . $decimals . ')) AS sales_tax
				FROM ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)'
		);

		// create a temporary table to contain all the payment types and amount
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_payments_temp') .
			' (PRIMARY KEY(sale_id), INDEX(sale_id))
			(
				SELECT payments.sale_id AS sale_id,
					SUM(CASE WHEN payments.cash_adjustment = 0 THEN payments.payment_amount ELSE 0 END) AS sale_payment_amount,
					SUM(CASE WHEN payments.cash_adjustment = 1 THEN payments.payment_amount ELSE 0 END) AS sale_cash_adjustment,
					SUM(payments.cash_refund) AS sale_cash_refund,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", (payments.payment_amount - payments.cash_refund)) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->dbprefix('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE ' . $where . '
				GROUP BY payments.sale_id
			)'
		);

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_temp') .
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
					MAX(' . $this->Item->get_item_name() . ') AS name,
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
				FROM ' . $this->db->dbprefix('sales_items') . ' AS sales_items
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales_items.sale_id = sales.sale_id
				INNER JOIN ' . $this->db->dbprefix('items') . ' AS items
					ON sales_items.item_id = items.item_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('sales_payments_temp') . ' AS payments
					ON sales_items.sale_id = payments.sale_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('suppliers') . ' AS supplier
					ON items.supplier_id = supplier.person_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('people') . ' AS customer_p
					ON sales.customer_id = customer_p.person_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('customers') . ' AS customer
					ON sales.customer_id = customer.person_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('people') . ' AS employee
					ON sales.employee_id = employee.person_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('sales_items_taxes_temp') . ' AS sales_items_taxes
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)'
		);
	}

	/**
	 * Retrieves all sales that are in a suspended state
	 */
	public function get_all_suspended($customer_id = NULL)
	{
		if($customer_id == -1)
		{
			$query = $this->db->query("SELECT sale_id, case when sale_type = '".SALE_TYPE_QUOTE."' THEN quote_number WHEN sale_type = '".SALE_TYPE_WORK_ORDER."' THEN work_order_number else sale_id end as doc_id, sale_id as suspended_sale_id, sale_status, sale_time, dinner_table_id, customer_id, employee_id, comment FROM "
				. $this->db->dbprefix('sales') . ' where sale_status = ' . SUSPENDED);
		}
		else
		{
			$query = $this->db->query("SELECT sale_id, case when sale_type = '".SALE_TYPE_QUOTE."' THEN quote_number WHEN sale_type = '".SALE_TYPE_WORK_ORDER."' THEN work_order_number else sale_id end as doc_id, sale_status, sale_time, dinner_table_id, customer_id, employee_id, comment FROM "
				. $this->db->dbprefix('sales') . ' where sale_status = '. SUSPENDED .' AND customer_id = ' . $customer_id);
		}

		return $query->result_array();
	}

	/**
	 * Gets the dinner table for the selected sale
	 */
	public function get_dinner_table($sale_id)
	{
		if($sale_id == -1)
		{
			return NULL;
		}

		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row()->dinner_table_id;
	}

	/**
	 * Gets the sale type for the selected sale
	 */
	public function get_sale_type($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row()->sale_type;
	}

	/**
	 * Gets the sale status for the selected sale
	 */
	public function get_sale_status($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row()->sale_status;
	}

	public function update_sale_status($sale_id, $sale_status)
	{
		$this->db->where('sale_id', $sale_id);
		$this->db->update('sales', array('sale_status'=>$sale_status));
	}

	/**
	 * Gets the quote_number for the selected sale
	 */
	public function get_quote_number($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		$row = $this->db->get()->row();

		if($row != NULL)
		{
			return $row->quote_number;
		}

		return NULL;
	}

	/**
	 * Gets the work order number for the selected sale
	 */
	public function get_work_order_number($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		$row = $this->db->get()->row();

		if($row != NULL)
		{
			return $row->work_order_number;
		}

		return NULL;
	}

	/**
	 * Gets the quote_number for the selected sale
	 */
	public function get_comment($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		$row = $this->db->get()->row();

		if($row != NULL)
		{
			return $row->comment;
		}

		return NULL;
	}

	/**
	 * Gets total of suspended invoices rows
	 */
	public function get_suspended_invoice_count()
	{
		$this->db->from('sales');
		$this->db->where('invoice_number IS NOT NULL');
		$this->db->where('sale_status', SUSPENDED);

		return $this->db->count_all_results();
	}

	/**
	 * Removes a selected sale from the sales table.
	 * This function should only be called for suspended sales that are being restored to the current cart
	 */
	public function delete_suspended_sale($sale_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		if($this->config->item('dinner_table_enable') == TRUE)
		{
			$dinner_table = $this->get_dinner_table($sale_id);
			$this->Dinner_table->release($dinner_table);
		}

		$this->update_sale_status($sale_id, CANCELED);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	/**
	 * This clears the sales detail for a given sale_id before the detail is resaved.
	 * This allows us to reuse the same sale_id
	 */
	public function clear_suspended_sale_detail($sale_id)
	{
		$this->db->trans_start();


		if($this->config->item('dinner_table_enable') == TRUE)
		{
			$dinner_table = $this->get_dinner_table($sale_id);
			$this->Dinner_table->release($dinner_table);
		}

		$this->db->delete('sales_payments', array('sale_id' => $sale_id));
		$this->db->delete('sales_items_taxes', array('sale_id' => $sale_id));
		$this->db->delete('sales_items', array('sale_id' => $sale_id));
		$this->db->delete('sales_taxes', array('sale_id' => $sale_id));

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	/**
	 * Gets suspended sale info
	 */
	public function get_suspended_sale_info($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);
		$this->db->join('people', 'people.person_id = sales.customer_id', 'LEFT');
		$this->db-where('sale_status', SUSPENDED);

		return $this->db->get();
	}

	/**
	 * @param $customer_id
	 * @param $sale_id
	 * @param $total_amount
	 * @param $total_amount_used
	 */
	private function save_customer_rewards($customer_id, $sale_id, $total_amount, $total_amount_used)
	{
		if(!empty($customer_id) && $this->config->item('customer_reward_enable') == TRUE)
		{
			$package_id = $this->Customer->get_info($customer_id)->package_id;

			if(!empty($package_id))
			{
				$points_percent = $this->Customer_rewards->get_points_percent($package_id);
				$points = $this->Customer->get_info($customer_id)->points;
				$points = ($points == NULL ? 0 : $points);
				$points_percent = ($points_percent == NULL ? 0 : $points_percent);
				$total_amount_earned = ($total_amount * $points_percent / 100);
				$points = $points + $total_amount_earned;
				$this->Customer->update_reward_points_value($customer_id, $points);
				$rewards_data = array('sale_id' => $sale_id, 'earned' => $total_amount_earned, 'used' => $total_amount_used);
				$this->Rewards->save($rewards_data);
			}
		}
	}

}
?>
