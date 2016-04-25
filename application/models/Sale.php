<?php
class Sale extends CI_Model
{
	public function get_info($sale_id)
	{
		$this->db->select('first_name, last_name, email, comment, sale_payment_amount AS amount_tendered, payment_type,
			invoice_number, sale_time, employee_id, customer_id, comments, sale_id, (sale_payment_amount - total) AS change_due', FALSE);
		$this->db->select('DATE_FORMAT(sale_time, "%d-%m-%Y") AS sale_date', FALSE);
		$this->db->select('CONCAT(first_name, " ", last_name) AS customer_name', FALSE);
		$this->db->select('SUM(item_unit_price * quantity_purchased * (1 - discount_percent / 100)) AS amount_due');
		$this->db->from('sales_items_temp');
		$this->db->join('people', 'people.person_id = sales_items_temp.customer_id', 'left');
		$this->db->where('sales_items_temp.sale_id', $sale_id);
		$this->db->group_by('sale_id');
		$this->db->order_by('sale_time', 'asc');

		return $this->db->get();
	}
	
	/*
	 Get number of rows for the takings (sales/manage) view
	*/
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters)->num_rows();
	}

	/*
	 Get the sales data for the takings (sales/manage) view
	*/
	public function search($search, $filters, $rows=0, $limit_from=0)
	{
		$this->db->select('sale_id, sale_date, sale_time, SUM(quantity_purchased) AS items_purchased,
						CONCAT(customer.first_name, " ", customer.last_name) AS customer_name, 
						SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, SUM(profit) AS profit,
						sale_payment_amount AS amount_tendered, SUM(total) AS amount_due, (sale_payment_amount - SUM(total)) AS change_due, 
						payment_type, invoice_number', FALSE);
		$this->db->from('sales_items_temp');
		$this->db->join('people AS customer', 'sales_items_temp.customer_id = customer.person_id', 'left');

		if (empty($search))
		{
			$this->db->where('sale_time BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			if ($filters['is_valid_receipt'] != FALSE)
			{
				$pieces = explode(' ', $search);
				$this->db->where('sales_items_temp.sale_id', $pieces[1]);
			}

			else
			{
				$this->db->like('last_name', $search);
				$this->db->or_like('first_name', $search);
				$this->db->or_like('CONCAT(customer.first_name, " ", last_name)', $search);
			}
		}

		if ($filters['location_id'] != 'all')
		{
			$this->db->where('item_location', $filters['location_id']);
		}

		if ($filters['sale_type'] == 'sales')
        {
            $this->db->where('quantity_purchased > 0');
        }
        elseif ($filters['sale_type'] == 'returns')
        {
            $this->db->where('quantity_purchased < 0');
        }
		
		if ($filters['only_invoices'] != FALSE)
		{
			$this->db->where('invoice_number <> ', 'NULL');
		}

		if ($filters['only_cash'] != FALSE)
		{
			$this->db->like('payment_type ', $this->lang->line('sales_cash'), 'after');
		}
		
		$this->db->group_by('sale_id');
		$this->db->order_by('sale_date', 'asc');
		
		if ($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/*
	 Get the payment summary for the takings (sales/manage) view
	*/
	public function get_payments_summary($search, $filters)
	{
		// get payment summary
		$this->db->select('payment_type, count(*) AS count, SUM(payment_amount) AS payment_amount', FALSE);
		$this->db->from('sales');
		$this->db->join('sales_payments', 'sales_payments.sale_id=sales.sale_id');
		$this->db->join('people', 'people.person_id = sales.customer_id', 'left');

		if (empty($search))
		{
			//$this->db->where('sale_time BETWEEN '. $this->db->escape($filters['start_date']). ' AND '. $this->db->escape($filters['end_date']));
		}
		else
		{
			if ($filters['is_valid_receipt'] != FALSE)
			{
				$pieces = explode(' ',$search);
				$this->db->where('sales.sale_id', $pieces[1]);
			}
			else
			{
				$this->db->like('last_name', $search);
				$this->db->or_like('first_name', $search);
				$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
			}
		}

		if ($filters['sale_type'] == 'sales')
		{
			$this->db->where('payment_amount > 0');
		}
		elseif ($filters['sale_type'] == 'returns')
		{
			$this->db->where('payment_amount < 0');
		}

		if ($filters['only_invoices'] != FALSE)
		{
			$this->db->where('invoice_number <> ', 'NULL');
		}
		
		if ($filters['only_cash'] != FALSE)
		{
			$this->db->like('payment_type ', $this->lang->line('sales_cash'), 'after');
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

		if( $gift_card_count > 0 )
		{
			$payments[] = array('payment_type' => $this->lang->line('sales_giftcard'), 'count' => $gift_card_count, 'payment_amount' => $gift_card_amount);
		}

		return $payments;
	}
	
	public function get_total_rows()
	{
		$this->db->from('sales');

		return $this->db->count_all_results();
	}

	public function get_search_suggestions($search, $limit=25)
	{
		$suggestions = array();

		if (!$this->sale_lib->is_valid_receipt($search))
		{
			$this->db->distinct();
			$this->db->select('first_name, last_name');
			$this->db->from('sales');
			$this->db->join('people', 'people.person_id = sales.customer_id');
			$this->db->like('last_name', $search);
			$this->db->or_like('first_name', $search);
			$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
			$this->db->order_by('last_name', 'asc');

			foreach($this->db->get()->result_array() as $result)
			{
				$suggestions[] = array('label' => $result[ 'first_name' ].' '.$result[ 'last_name' ]);
			}
		}
		else
		{
			$suggestions[] = array('label' => $search);
		}

		return $suggestions;
	}

	public function get_invoice_count()
	{
		$this->db->from('sales');
		$this->db->where('invoice_number is not null');

		return $this->db->count_all_results();
	}
	
	public function get_sale_by_invoice_number($invoice_number)
	{
		$this->db->from('sales');
		$this->db->where('invoice_number', $invoice_number);

		return $this->db->get();
	}
	
	public function get_invoice_number_for_year($year = '', $start_from = 0) 
	{
		$year = $year == '' ? date('Y') : $year;
		$this->db->select("COUNT( 1 ) AS invoice_number_year", FALSE);
		$this->db->from('sales');
		$this->db->where("DATE_FORMAT(sale_time, '%Y' ) = ", $year, FALSE);
		$this->db->where("invoice_number IS NOT ", "NULL", FALSE);
		$result = $this->db->get()->row_array();

		return ($start_from + $result['invoice_number_year']);
	}

	public function exists($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return ($this->db->get()->num_rows()==1);
	}
	
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
			$this->db->delete('sales_payments', array('sale_id'=>$sale_id));

			// add new payments
			foreach($payments as $payment)
			{
				$sales_payments_data = array(
					'sale_id'=>$sale_id,
					'payment_type'=>$payment['payment_type'],
					'payment_amount'=>$payment['payment_amount']
					);

				$success = $this->db->insert('sales_payments', $sales_payments_data);
			}
			
			$this->db->trans_complete();
			
			$success = $this->db->trans_status();
		}
		
		return $success;
	}
	
	public function save($items, $customer_id, $employee_id, $comment, $invoice_number, $payments, $sale_id=false)
	{
		if(count($items)==0)
		{
			return -1;
		}

		$sales_data = array(
			'sale_time'		=> date('Y-m-d H:i:s'),
			'customer_id'	=> $this->Customer->exists($customer_id) ? $customer_id : null,
			'employee_id'	=> $employee_id,
			'comment'		=> $comment,
			'invoice_number'=> $invoice_number
		);

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('sales', $sales_data);
		$sale_id = $this->db->insert_id();

		foreach($payments as $payment_id=>$payment)
		{
			if ( substr( $payment['payment_type'], 0, strlen( $this->lang->line('sales_giftcard') ) ) == $this->lang->line('sales_giftcard') )
			{
				// We have a gift card and we have to deduct the used value from the total value of the card.
				$splitpayment = explode( ':', $payment['payment_type'] );
				$cur_giftcard_value = $this->Giftcard->get_giftcard_value( $splitpayment[1] );
				$this->Giftcard->update_giftcard_value( $splitpayment[1], $cur_giftcard_value - $payment['payment_amount'] );
			}

			$sales_payments_data = array(
				'sale_id'		=> $sale_id,
				'payment_type'	=> $payment['payment_type'],
				'payment_amount'=> $payment['payment_amount']
			);
			$this->db->insert('sales_payments',$sales_payments_data);
		}

		foreach($items as $line=>$item)
		{
			$cur_item_info = $this->Item->get_info($item['item_id']);

			$sales_items_data = array(
				'sale_id'			=> $sale_id,
				'item_id'			=> $item['item_id'],
				'line'				=> $item['line'],
				'description'		=> character_limiter($item['description'], 30),
				'serialnumber'		=> character_limiter($item['serialnumber'], 30),
				'quantity_purchased'=> $item['quantity'],
				'discount_percent'	=> $item['discount'],
				'item_cost_price'	=> $cur_item_info->cost_price,
				'item_unit_price'	=> $item['price'],
				'item_location'		=> $item['item_location']
			);

			$this->db->insert('sales_items',$sales_items_data);

			// Update stock quantity
			$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);
			$this->Item_quantity->save(array('quantity'		=> $item_quantity->quantity - $item['quantity'],
                                              'item_id'		=> $item['item_id'],
                                              'location_id'	=> $item['item_location']), $item['item_id'], $item['item_location']);

			// if an items was deleted but later returned it's restored with this rule
			if($item['quantity'] < 0)
			{
				$this->Item->undelete($item['item_id']);
			}
											  
			// Inventory Count Details
			$sale_remarks ='POS '.$sale_id;
			$inv_data = array(
				'trans_date'		=> date('Y-m-d H:i:s'),
				'trans_items'		=> $item['item_id'],
				'trans_user'		=> $employee_id,
				'trans_location'	=> $item['item_location'],
				'trans_comment'		=> $sale_remarks,
				'trans_inventory'	=> -$item['quantity']
			);
			$this->Inventory->insert($inv_data);

			$customer = $this->Customer->get_info($customer_id);
 			if ($customer_id == -1 or $customer->taxable)
 			{
				foreach($this->Item_taxes->get_info($item['item_id']) as $row)
				{
					$this->db->insert('sales_items_taxes', array(
						'sale_id' 	=> $sale_id,
						'item_id' 	=> $item['item_id'],
						'line'      => $item['line'],
						'name'		=> $row['name'],
						'percent' 	=> $row['percent']
					));
				}
			}
		}
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE)
		{
			return -1;
		}
		
		return $sale_id;
	}
	
	public function delete_list($sale_ids, $employee_id, $update_inventory=TRUE) 
	{
		$result = TRUE;

		foreach($sale_ids as $sale_id)
		{
			$result &= $this->delete($sale_id, $employee_id, $update_inventory);
		}

		return $result;
	}
	
	public function delete($sale_id, $employee_id, $update_inventory=TRUE) 
	{
		// start a transaction to assure data integrity
		$this->db->trans_start();
		// first delete all payments
		$this->db->delete('sales_payments', array('sale_id' => $sale_id));
		// then delete all taxes on items
		$this->db->delete('sales_items_taxes', array('sale_id' => $sale_id));

		if ($update_inventory)
		{
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_sale_items($sale_id)->result_array();
			foreach($items as $item)
			{
				// create query to update inventory tracking
				$inv_data = array(
					'trans_date'=>date('Y-m-d H:i:s'),
					'trans_items'=>$item['item_id'],
					'trans_user'=>$employee_id,
					'trans_comment'=>'Deleting sale ' . $sale_id,
					'trans_location'=>$item['item_location'],
					'trans_inventory'=>$item['quantity_purchased']
				);
				// update inventory
				$this->Inventory->insert($inv_data);

				// update quantities
				$this->Item_quantity->change_quantity($item['item_id'], $item['item_location'], $item['quantity_purchased']);
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

	public function get_sale_items($sale_id)
	{
		$this->db->from('sales_items');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get();
	}

	public function get_sale_payments($sale_id)
	{
		$this->db->from('sales_payments');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get();
	}
	
	public function get_payment_options($giftcard=true)
	{
		$payments = array(
				$this->lang->line('sales_debit') => $this->lang->line('sales_debit'),
				$this->lang->line('sales_credit') => $this->lang->line('sales_credit'),
				$this->lang->line('sales_cash') => $this->lang->line('sales_cash'),
				$this->lang->line('sales_check') => $this->lang->line('sales_check')
				);
		
		if($giftcard)
		{
			$payments[$this->lang->line('sales_giftcard')] = $this->lang->line('sales_giftcard');
		}

		return $payments;
	}

	public function get_customer($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->Customer->get_info($this->db->get()->row()->customer_id);
	}
	
	public function invoice_number_exists($invoice_number, $sale_id='')
	{
		$this->db->from('sales');
		$this->db->where('invoice_number', $invoice_number);
		if (!empty($sale_id))
		{
			$this->db->where('sale_id !=', $sale_id);
		}
		
		return ($this->db->get()->num_rows()==1);
	}
	
	public function get_giftcard_value( $giftcardNumber )
	{
		if ( !$this->Giftcard->exists($this->Giftcard->get_giftcard_id($giftcardNumber)) )
		{
			return 0;
		}
		
		$this->db->from('giftcards');
		$this->db->where('giftcard_number', $giftcardNumber);

		return $this->db->get()->row()->value;
	}

	//We create a temp table that allows us to do easy report/sales queries
	public function create_sales_items_temp_table()
	{
		if ($this->config->item('tax_included'))
		{
			$total = "1";
			$subtotal = "(1 - (SUM(1 - 100/(100+percent))))";
			$tax="(SUM(1 - 100/(100+percent)))";
		}
		else
		{
			$tax = "(SUM(percent)/100)";
			$total = "(1+(SUM(percent/100)))";
			$subtotal = "1";
		}
		
		$decimals = totals_decimals();

		$this->db->query("CREATE TEMPORARY TABLE IF NOT EXISTS ".$this->db->dbprefix('sales_items_temp')."
		(SELECT date(sale_time) as sale_date, sale_time, ".$this->db->dbprefix('sales_items').".sale_id, comment, payments.payment_type, payments.sale_payment_amount, item_location, customer_id, employee_id,
		".$this->db->dbprefix('items').".item_id, supplier_id, quantity_purchased, item_cost_price, item_unit_price, SUM(percent) as item_tax_percent,
		discount_percent, ROUND((item_unit_price * quantity_purchased-item_unit_price * quantity_purchased * discount_percent / 100) * $subtotal, $decimals) as subtotal,
		".$this->db->dbprefix('sales_items').".line as line, serialnumber, ".$this->db->dbprefix('sales_items').".description as description,
		ROUND((item_unit_price * quantity_purchased-item_unit_price * quantity_purchased * discount_percent / 100) * $total, $decimals) as total,
		ROUND((item_unit_price * quantity_purchased-item_unit_price * quantity_purchased * discount_percent / 100) * $tax, $decimals) as tax,
		ROUND((item_unit_price * quantity_purchased-item_unit_price * quantity_purchased * discount_percent / 100)- (item_cost_price*quantity_purchased), $decimals) as profit,
		(item_cost_price * quantity_purchased) as cost,
		invoice_number
		FROM ".$this->db->dbprefix('sales_items')."
		INNER JOIN ".$this->db->dbprefix('sales')." ON ".$this->db->dbprefix('sales_items').'.sale_id='.$this->db->dbprefix('sales').'.sale_id'."
		INNER JOIN ".$this->db->dbprefix('items')." ON ".$this->db->dbprefix('sales_items').'.item_id='.$this->db->dbprefix('items').'.item_id'."
		INNER JOIN (SELECT sale_id, SUM(payment_amount) AS sale_payment_amount,
		GROUP_CONCAT(CONCAT(payment_type,' ',payment_amount) SEPARATOR ', ') AS payment_type FROM " . $this->db->dbprefix('sales_payments') . " GROUP BY sale_id) AS payments 
		ON " . $this->db->dbprefix('sales_items') . '.sale_id'. "=" . "payments.sale_id		
		LEFT OUTER JOIN ".$this->db->dbprefix('suppliers')." ON  ".$this->db->dbprefix('items').'.supplier_id='.$this->db->dbprefix('suppliers').'.person_id'."
		LEFT OUTER JOIN ".$this->db->dbprefix('sales_items_taxes')." ON  "
		.$this->db->dbprefix('sales_items').'.sale_id='.$this->db->dbprefix('sales_items_taxes').'.sale_id'." and "
		.$this->db->dbprefix('sales_items').'.item_id='.$this->db->dbprefix('sales_items_taxes').'.item_id'." and "
		.$this->db->dbprefix('sales_items').'.line='.$this->db->dbprefix('sales_items_taxes').'.line'."
		GROUP BY sale_id, item_id, line)");

		//Update null item_tax_percents to be 0 instead of null
		$this->db->where('item_tax_percent IS NULL');
		$this->db->update('sales_items_temp', array('item_tax_percent' => 0));

		//Update null tax to be 0 instead of null
		$this->db->where('tax IS NULL');
		$this->db->update('sales_items_temp', array('tax' => 0));

		//Update null subtotals to be equal to the total as these don't have tax
		$this->db->query('UPDATE '.$this->db->dbprefix('sales_items_temp'). ' SET total=subtotal WHERE total IS NULL');
	}
}
?>
