<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

define('COMPLETED', 0);
define('SUSPENDED', 1);
define('QUOTE', 2);

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
		// NOTE: temporary tables are created to speed up searches due to the fact that they are ortogonal to the main query
		// create a temporary table to contain all the payments per sale
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_payments_temp') .
			'(
				SELECT payments.sale_id AS sale_id,
					IFNULL(SUM(payments.payment_amount), 0) AS sale_payment_amount,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", payments.payment_amount) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->dbprefix('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE sales.sale_id = ' . $this->db->escape($sale_id) . '
				GROUP BY sale_id
			)'
		);

		$decimals = totals_decimals();

		$sale_price = 'sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100)';
		$tax = 'ROUND(IFNULL(SUM(sales_items_taxes.tax), 0), ' . $decimals . ')';

		if($this->config->item('tax_included'))
		{
			$sale_total = 'ROUND(SUM(' . $sale_price . '),' . $decimals . ')';
			$sale_subtotal = $sale_total . ' - ' . $tax;
		}
		else
		{
			$sale_subtotal = 'ROUND(SUM(' . $sale_price . '),' . $decimals . ')';
			$sale_total = $sale_subtotal . ' + ' . $tax;
		}

		// create a temporary table to contain all the sum of taxes per sale item
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') .
			'(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(sales_items_taxes.item_tax_amount) AS tax
				FROM ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE sales.sale_id = ' . $this->db->escape($sale_id) . '
				GROUP BY sale_id, item_id, line
			)'
		);

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
				' . "
				IFNULL($sale_total, $sale_subtotal) AS amount_due,
				MAX(payments.sale_payment_amount) AS amount_tendered,
				(MAX(payments.sale_payment_amount) - IFNULL($sale_total, $sale_subtotal)) AS change_due,
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

		$this->db->group_by('sale_id');
		$this->db->order_by('sale_time', 'asc');

		return $this->db->get();
	}

	/**
	 * Get number of rows for the takings (sales/manage) view
	 */
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters)->num_rows();
	}

	/**
	 * Get the sales data for the takings (sales/manage) view
	 */
	public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'sale_time', $order = 'desc')
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

		// NOTE: temporary tables are created to speed up searches due to the fact that they are ortogonal to the main query
		// create a temporary table to contain all the payments per sale item
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_payments_temp') .
			' (PRIMARY KEY(sale_id), INDEX(sale_id))
			(
				SELECT payments.sale_id AS sale_id,
					IFNULL(SUM(payments.payment_amount), 0) AS sale_payment_amount,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", payments.payment_amount) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->dbprefix('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE ' . $where . '
				GROUP BY sale_id
			)'
		);

		$decimals = totals_decimals();

		$sale_price = 'sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100)';
		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';
		$tax = 'IFNULL(SUM(sales_items_taxes.tax), 0)';

		if($this->config->item('tax_included'))
		{
			$sale_total = 'ROUND(SUM(' . $sale_price . '), ' . $decimals . ')';
			$sale_subtotal = $sale_total . ' - ' . $tax;
		}
		else
		{
			$sale_subtotal = 'ROUND(SUM(' . $sale_price . '), ' . $decimals . ')';
			$sale_total = $sale_subtotal . ' + ' . $tax;
		}

		// create a temporary table to contain all the sum of taxes per sale item
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') .
			' (INDEX(sale_id), INDEX(item_id))
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(sales_items_taxes.item_tax_amount) as tax
				FROM ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)'
		);

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
				IFNULL($sale_subtotal, $sale_total) AS subtotal,
				$tax AS tax,
				IFNULL($sale_total, $sale_subtotal) AS total,
				$sale_cost AS cost,
				(IFNULL($sale_subtotal, $sale_total) - $sale_cost) AS profit,
				IFNULL($sale_total, $sale_subtotal) AS amount_due,
				MAX(payments.sale_payment_amount) AS amount_tendered,
				(MAX(payments.sale_payment_amount) - IFNULL($sale_total, $sale_subtotal)) AS change_due,
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

		if($filters['only_due'] != FALSE)
		{
			$this->db->like('payments.payment_type', $this->lang->line('sales_due'));
		}

		if($filters['only_check'] != FALSE)
		{
			$this->db->like('payments.payment_type', $this->lang->line('sales_check'));
		}

		$this->db->group_by('sale_id');
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
		$this->db->select('payment_type, count(*) AS count, SUM(payment_amount) AS payment_amount');
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

		$this->db->group_by('payment_type');

		$payments = $this->db->get()->result_array();

		// consider Gift Card as only one type of payment and do not show "Gift Card: 1, Gift Card: 2, etc." in the total
		$gift_card_count = 0;
		$gift_card_amount = 0;
		foreach($payments as $key=>$payment)
		{
			if( strstr($payment['payment_type'], $this->lang->line('sales_giftcard')) != FALSE )
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

	/**
	 * Gets invoice number by year
	 */
	public function get_invoice_number_for_year($year = '', $start_from = 0)
	{
		$year = $year == '' ? date('Y') : $year;
		$this->db->select('COUNT( 1 ) AS invoice_number_year');
		$this->db->from('sales');
		$this->db->where('DATE_FORMAT(sale_time, "%Y" ) = ', $year);
		$this->db->where('invoice_number IS NOT NULL');
		$result = $this->db->get()->row_array();

		return ($start_from + $result['invoice_number_year']);
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

			// first delete all payments
			$this->db->delete('sales_payments', array('sale_id' => $sale_id));

			// add new payments
			foreach($payments as $payment)
			{
				$sales_payments_data = array(
					'sale_id' => $sale_id,
					'payment_type' => $payment['payment_type'],
					'payment_amount' => $payment['payment_amount']
				);

				$success = $this->db->insert('sales_payments', $sales_payments_data);
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
	public function save(&$sale_status, &$items, $customer_id, $employee_id, $comment, $invoice_number, $quote_number, $payments, $dinner_table, &$sales_taxes, $sale_id = FALSE)
	{
		$tax_decimals = tax_decimals();

		if(count($items) == 0)
		{
			return -1;
		}

		$table_status = $this->determine_sale_status($sale_status, $dinner_table);

		$sales_data = array(
			'sale_time'		 => date('Y-m-d H:i:s'),
			'customer_id'	 => $this->Customer->exists($customer_id) ? $customer_id : null,
			'employee_id'	 => $employee_id,
			'comment'		 => $comment,
			'sale_status'    => $sale_status,
			'invoice_number' => $invoice_number,
			'quote_number' => $quote_number,
			'dinner_table_id'=> $dinner_table,
			'sale_status'	 => $sale_status
		);

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('sales', $sales_data);
		$sale_id = $this->db->insert_id();
		$total_amount = 0;
		$total_amount_used = 0;
		foreach($payments as $payment_id=>$payment)
		{
			if( substr( $payment['payment_type'], 0, strlen( $this->lang->line('sales_giftcard') ) ) == $this->lang->line('sales_giftcard') )
			{
				// We have a gift card and we have to deduct the used value from the total value of the card.
				$splitpayment = explode( ':', $payment['payment_type'] );
				$cur_giftcard_value = $this->Giftcard->get_giftcard_value( $splitpayment[1] );
				$this->Giftcard->update_giftcard_value( $splitpayment[1], $cur_giftcard_value - $payment['payment_amount'] );
			}

			if( substr( $payment['payment_type'], 0, strlen( $this->lang->line('sales_rewards') ) ) == $this->lang->line('sales_rewards') )
			{

				$cur_rewards_value = $this->Customer->get_info($customer_id)->points;
				$this->Customer->update_reward_points_value($customer_id, $cur_rewards_value - $payment['payment_amount'] );
				$total_amount_used = floatval($total_amount_used) + floatval($payment['payment_amount']);
			}

			$sales_payments_data = array(
				'sale_id'		 => $sale_id,
				'payment_type'	 => $payment['payment_type'],
				'payment_amount' => $payment['payment_amount']
			);
			$this->db->insert('sales_payments', $sales_payments_data);
			$total_amount = floatval($total_amount) + floatval($payment['payment_amount']);
		}

		$this->save_customer_rewards($customer_id, $sale_id, $total_amount, $total_amount_used);

		$customer = $this->Customer->get_info($customer_id);

		$sales_taxes = array();

		foreach($items as $line=>$item)
		{
			$cur_item_info = $this->Item->get_info($item['item_id']);

			$sales_items_data = array(
				'sale_id'			=> $sale_id,
				'item_id'			=> $item['item_id'],
				'line'				=> $item['line'],
				'description'		=> character_limiter($item['description'], 255),
				'serialnumber'		=> character_limiter($item['serialnumber'], 30),
				'quantity_purchased'=> $item['quantity'],
				'discount_percent'	=> $item['discount'],
				'item_cost_price'	=> $cur_item_info->cost_price,
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

			// Calculate taxes and save the tax information for the sale.  Return the result for printing

			if($customer_id == -1 || $customer->taxable)
			{
				if($this->config->item('tax_included'))
				{
					$tax_type = Tax_lib::TAX_TYPE_VAT;
				}
				else
				{
					$tax_type = Tax_lib::TAX_TYPE_SALES;
				}
				$rounding_code = Rounding_mode::HALF_UP; // half adjust
				$tax_group_sequence = 0;
				$item_total = $this->sale_lib->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE);
				$tax_basis = $item_total;
				$item_tax_amount = 0;

				foreach($this->Item_taxes->get_info($item['item_id']) as $row)
				{

					$sales_items_taxes = array(
						'sale_id'			=> $sale_id,
						'item_id'			=> $item['item_id'],
						'line'				=> $item['line'],
						'name'				=> character_limiter($row['name'], 255),
						'percent'			=> $row['percent'],
						'tax_type'			=> $tax_type,
						'rounding_code'		=> $rounding_code,
						'cascade_tax'		=> 0,
						'cascade_sequence'	=> 0,
						'item_tax_amount'	=> 0
					);

					// This computes tax for each line item and adds it to the tax type total
					$tax_group = (float)$row['percent'] . '% ' . $row['name'];
					$tax_basis = $this->sale_lib->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE);

					if($this->config->item('tax_included'))
					{
						$tax_type = Tax_lib::TAX_TYPE_VAT;
						$item_tax_amount = $this->sale_lib->get_item_tax($item['quantity'], $item['price'], $item['discount'],$row['percent']);
					}
					elseif($this->config->item('customer_sales_tax_support') == '0')
					{
						$tax_type = Tax_lib::TAX_TYPE_SALES;
						$item_tax_amount = $this->tax_lib->get_sales_tax_for_amount($tax_basis, $row['percent'], '0', $tax_decimals);
					}
					else
					{
						$tax_type = Tax_lib::TAX_TYPE_SALES;
					}

					$sales_items_taxes['item_tax_amount'] = $item_tax_amount;
					if($item_tax_amount != 0)
					{
						$this->db->insert('sales_items_taxes', $sales_items_taxes);
						$this->tax_lib->update_sales_taxes($sales_taxes, $tax_type, $tax_group, $row['percent'], $tax_basis, $item_tax_amount, $tax_group_sequence, $rounding_code, $sale_id,  $row['name'], '');
						$tax_group_sequence += 1;
					}

				}

				if($this->config->item('customer_sales_tax_support') == '1')
				{
					$this->save_sales_item_tax($customer, $sale_id, $item, $item_total, $sales_taxes, $sequence, $cur_item_info->tax_category_id);
				}
			}
		}

		if($customer_id == -1 || $customer->taxable)
		{
			$this->tax_lib->round_sales_taxes($sales_taxes);
			$this->save_sales_tax($sales_taxes);
		}

		$dinner_table_data = array(
			'status' => $table_status
		);

		$this->db->where('dinner_table_id',$dinner_table);
		$this->db->update('dinner_tables', $dinner_table_data);

		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE)
		{
			return -1;
		}

		return $sale_id;
	}

	/**
	 * Apply customer sales tax if the customer sales tax is enabledl
	 * The original tax is still supported if the user configures it,
	 * but it won't make sense unless it's used exclusively for the purpose
	 * of VAT tax which becomes a price component.  VAT taxes must still be reported
	 * as a separate tax entry on the invoice.
	 */
	public function save_sales_item_tax(&$customer, &$sale_id, &$item, $tax_basis, &$sales_taxes, &$sequence, $tax_category_id)
	{
		// if customer sales tax is enabled then update  sales_items_taxes with the
		if($this->config->item('customer_sales_tax_support') == '1')
		{
			$register_mode = $this->config->item('default_register_mode');
			$tax_details = $this->tax_lib->apply_sales_tax($item, $customer->city, $customer->state, $customer->sales_tax_code, $register_mode, $sale_id, $sales_taxes);

			$sales_items_taxes = array(
				'sale_id'			=> $sale_id,
				'item_id'			=> $item['item_id'],
				'line'				=> $item['line'],
				'name'				=> $tax_details['tax_name'],
				'percent'			=> $tax_details['tax_rate'],
				'tax_type'			=> Tax_lib::TAX_TYPE_SALES,
				'rounding_code'		=> $tax_details['rounding_code'],
				'cascade_tax'		=> 0,
				'cascade_sequence'	=> 0,
				'item_tax_amount'	=> $tax_details['item_tax_amount']
			);

			$this->db->insert('sales_items_taxes', $sales_items_taxes);
		}
	}

	/**
	 * Saves sale tax
	 */
	public function save_sales_tax(&$sales_taxes)
	{
		foreach($sales_taxes as $line=>$sales_tax)
		{
			$this->db->insert('sales_taxes', $sales_tax);
		}
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
	 * Delete sale
	 */
	public function delete($sale_id, $employee_id, $update_inventory = TRUE)
	{
		// start a transaction to assure data integrity
		$this->db->trans_start();

		// first delete all payments
		$this->db->delete('sales_payments', array('sale_id' => $sale_id));
		// then delete all taxes on items
		$this->db->delete('sales_items_taxes', array('sale_id' => $sale_id));

		if($update_inventory)
		{
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_sale_items($sale_id)->result_array();
			foreach($items as $item)
			{
				$cur_item_info = $this->Item->get_info($item['item_id']);

				if($cur_item_info->stock_type == HAS_STOCK) {
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

		// delete all items
		$this->db->delete('sales_items', array('sale_id' => $sale_id));
		// delete sale itself
		$this->db->delete('sales', array('sale_id' => $sale_id));

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
			sale_id,
			sales_items.item_id,
			sales_items.description,
			serialnumber,
			line,
			quantity_purchased,
			item_cost_price,
			item_unit_price,
			discount_percent,
			item_location,
			print_option,
			items.name as name,
			category,
			item_type,
			stock_type');
		$this->db->from('sales_items as sales_items');
		$this->db->join('items as items', 'sales_items.item_id = items.item_id');
		$this->db->where('sale_id', $sale_id);

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
		}
		// Group by Item Category
		elseif($this->config->item('line_sequence') == '2')
		{
			$this->db->order_by('category', 'asc');
			$this->db->order_by('sales_items.description', 'asc');
			$this->db->order_by('items.name', 'asc');
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
		$payments = array();

		if($this->config->item('payment_options_order') == 'debitcreditcash')
		{
			$payments[$this->lang->line('sales_debit')] = $this->lang->line('sales_debit');
			$payments[$this->lang->line('sales_credit')] = $this->lang->line('sales_credit');
			$payments[$this->lang->line('sales_cash')] = $this->lang->line('sales_cash');
		}
		elseif($this->config->item('payment_options_order') == 'debitcashcredit')
		{
			$payments[$this->lang->line('sales_debit')] = $this->lang->line('sales_debit');
			$payments[$this->lang->line('sales_cash')] = $this->lang->line('sales_cash');
			$payments[$this->lang->line('sales_credit')] = $this->lang->line('sales_credit');
		}
		else // default: if($this->config->item('payment_options_order') == 'cashdebitcredit')
		{
			$payments[$this->lang->line('sales_cash')] = $this->lang->line('sales_cash');
			$payments[$this->lang->line('sales_debit')] = $this->lang->line('sales_debit');
			$payments[$this->lang->line('sales_credit')] = $this->lang->line('sales_credit');
		}

		$payments[$this->lang->line('sales_due')] = $this->lang->line('sales_due');
		$payments[$this->lang->line('sales_check')] = $this->lang->line('sales_check');

		if($giftcard)
		{
			$payments[$this->lang->line('sales_giftcard')] = $this->lang->line('sales_giftcard');
		}

		if($reward_points)
		{
			$payments[$this->lang->line('sales_rewards')] = $this->lang->line('sales_rewards');
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
	// TODO change to use new quote_number field
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
	 * Creates sales temporary dimentional table
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

		$sale_price = 'sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100)';
		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';
		$tax = 'IFNULL(SUM(sales_items_taxes.tax), 0)';

		if($this->config->item('tax_included'))
		{
			$sale_total = 'ROUND(SUM(' . $sale_price . '), ' . $decimals . ')';
			$sale_subtotal = $sale_total . ' - ' . $tax;
		}
		else
		{
			$sale_subtotal = 'ROUND(SUM(' . $sale_price . '), ' . $decimals . ')';
			$sale_total = $sale_subtotal . ' + ' . $tax;
		}

		// create a temporary table to contain all the sum of taxes per sale item
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') .
			' (INDEX(sale_id), INDEX(item_id))
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(sales_items_taxes.item_tax_amount) as tax
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
					IFNULL(SUM(payments.payment_amount), 0) AS sale_payment_amount,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", payments.payment_amount) SEPARATOR ", ") AS payment_type
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
					MAX(items.name) AS name,
					MAX(items.category) AS category,
					MAX(items.supplier_id) AS supplier_id,
					MAX(sales_items.quantity_purchased) AS quantity_purchased,
					MAX(sales_items.item_cost_price) AS item_cost_price,
					MAX(sales_items.item_unit_price) AS item_unit_price,
					MAX(sales_items.discount_percent) AS discount_percent,
					sales_items.line AS line,
					MAX(sales_items.serialnumber) AS serialnumber,
					MAX(sales_items.item_location) AS item_location,
					MAX(sales_items.description) AS description,
					MAX(payments.payment_type) AS payment_type,
					MAX(payments.sale_payment_amount) AS sale_payment_amount,
					' . "
					IFNULL($sale_subtotal, $sale_total) AS subtotal,
					$tax AS tax,
					IFNULL($sale_total, $sale_subtotal) AS total,
					$sale_cost AS cost,
					(IFNULL($sale_subtotal, $sale_total) - $sale_cost) AS profit
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

		// drop the temporary table to contain memory consumption as it's no longer required
		$this->db->query('DROP TEMPORARY TABLE IF EXISTS ' . $this->db->dbprefix('sales_payments_temp'));
		$this->db->query('DROP TEMPORARY TABLE IF EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp'));
	}

	/**
	 * Retrieves all sales that are in a suspended state
	 */
	public function get_all_suspended($customer_id = NULL)
	{
		if($customer_id == -1)
		{
			$query = $this->db->query('select sale_id, sale_id as suspended_sale_id, sale_status, sale_time, dinner_table_id, customer_id, comment from '
				. $this->db->dbprefix('sales') . ' where sale_status = ' . SUSPENDED);
		}
		else
		{
			$query = $this->db->query('select sale_id, sale_status, sale_time, dinner_table_id, customer_id, comment from '
				. $this->db->dbprefix('sales') . ' where sale_status = '. SUSPENDED .' AND customer_id = ' . $customer_id);
		}

		return $query->result_array();

	}

	/**
	 * Gets the dinner table for the selected sale
	 */
	public function get_dinner_table($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row()->dinner_table_id;
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
		else
		{
			return NULL;
		}
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
		else
		{
			return NULL;
		}
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

		$dinner_table = $this->get_dinner_table($sale_id);
		$dinner_table_data = array(
			'status' => 0
		);

		$this->db->where('dinner_table_id',$dinner_table);
		$this->db->update('dinner_tables', $dinner_table_data);

		$this->db->delete('sales_payments', array('sale_id' => $sale_id));
		$this->db->delete('sales_items_taxes', array('sale_id' => $sale_id));
		$this->db->delete('sales_items', array('sale_id' => $sale_id));
		$this->db->delete('sales_taxes', array('sale_id' => $sale_id));
		$this->db->delete('sales', array('sale_id' => $sale_id, 'sale_status' => SUSPENDED));

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
		if (!empty($customer_id) && $this->config->item('customer_reward_enable') == TRUE)
		{
			$package_id = $this->Customer->get_info($customer_id)->package_id;

			if (!empty($package_id))
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

	/**
	 * @param $sale_status
	 * @param $dinner_table
	 * @return int
	 */
	private function determine_sale_status(&$sale_status, $dinner_table)
	{
		if ($sale_status == SUSPENDED && $dinner_table > 2)    //not delivery or take away
		{
			return SUSPENDED;
		}
		return COMPLETED;
	}
}
?>
