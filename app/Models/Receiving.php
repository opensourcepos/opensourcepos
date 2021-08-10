<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Receiving class
 */

class Receiving extends CI_Model
{
	public function get_info($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->join('people', 'people.person_id = receivings.supplier_id', 'LEFT');
		$this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
		$this->db->where('receiving_id', $receiving_id);

		return $this->db->get();
	}

	public function get_receiving_by_reference($reference)
	{
		$this->db->from('receivings');
		$this->db->where('reference', $reference);

		return $this->db->get();
	}

	public function is_valid_receipt($receipt_receiving_id)
	{
		if(!empty($receipt_receiving_id))
		{
			//RECV #
			$pieces = explode(' ', $receipt_receiving_id);

			if(count($pieces) == 2 && preg_match('/(RECV|KIT)/', $pieces[0]))
			{
				return $this->exists($pieces[1]);
			}
			else
			{
				return $this->get_receiving_by_reference($receipt_receiving_id)->num_rows() > 0;
			}
		}

		return FALSE;
	}

	public function exists($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id', $receiving_id);

		return ($this->db->get()->num_rows() == 1);
	}

	public function update($receiving_data, $receiving_id)
	{
		$this->db->where('receiving_id', $receiving_id);

		return $this->db->update('receivings', $receiving_data);
	}

	public function save($items, $supplier_id, $employee_id, $comment, $reference, $payment_type, $receiving_id = FALSE)
	{
		if(count($items) == 0)
		{
			return -1;
		}

		$receivings_data = array(
			'receiving_time' => date('Y-m-d H:i:s'),
			'supplier_id' => $this->Supplier->exists($supplier_id) ? $supplier_id : NULL,
			'employee_id' => $employee_id,
			'payment_type' => $payment_type,
			'comment' => $comment,
			'reference' => $reference
		);

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('receivings', $receivings_data);
		$receiving_id = $this->db->insert_id();

		foreach($items as $line=>$item)
		{
			$cur_item_info = $this->Item->get_info($item['item_id']);

			$receivings_items_data = array(
				'receiving_id' => $receiving_id,
				'item_id' => $item['item_id'],
				'line' => $item['line'],
				'description' => $item['description'],
				'serialnumber' => $item['serialnumber'],
				'quantity_purchased' => $item['quantity'],
				'receiving_quantity' => $item['receiving_quantity'],
				'discount' => $item['discount'],
				'discount_type' => $item['discount_type'],
				'item_cost_price' => $cur_item_info->cost_price,
				'item_unit_price' => $item['price'],
				'item_location' => $item['item_location']
			);

			$this->db->insert('receivings_items', $receivings_items_data);

			$items_received = $item['receiving_quantity'] != 0 ? $item['quantity'] * $item['receiving_quantity'] : $item['quantity'];

			// update cost price, if changed AND is set in config as wanted
			if($cur_item_info->cost_price != $item['price'] && $this->config->item('receiving_calculate_average_price') != FALSE)
			{
				$this->Item->change_cost_price($item['item_id'], $items_received, $item['price'], $cur_item_info->cost_price);
			}

			//Update stock quantity
			$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);
			$this->Item_quantity->save(array('quantity' => $item_quantity->quantity + $items_received, 'item_id' => $item['item_id'],
											  'location_id' => $item['item_location']), $item['item_id'], $item['item_location']);

			$recv_remarks = 'RECV ' . $receiving_id;
			$inv_data = array(
				'trans_date' => date('Y-m-d H:i:s'),
				'trans_items' => $item['item_id'],
				'trans_user' => $employee_id,
				'trans_location' => $item['item_location'],
				'trans_comment' => $recv_remarks,
				'trans_inventory' => $items_received
			);

			$this->Inventory->insert($inv_data);

			$this->Attribute->copy_attribute_links($item['item_id'], 'receiving_id', $receiving_id);

			$supplier = $this->Supplier->get_info($supplier_id);
		}

		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE)
		{
			return -1;
		}

		return $receiving_id;
	}

	public function delete_list($receiving_ids, $employee_id, $update_inventory = TRUE)
	{
		$success = TRUE;

		// start a transaction to assure data integrity
		$this->db->trans_start();

		foreach($receiving_ids as $receiving_id)
		{
			$success &= $this->delete($receiving_id, $employee_id, $update_inventory);
		}

		// execute transaction
		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	public function delete($receiving_id, $employee_id, $update_inventory = TRUE)
	{
		// start a transaction to assure data integrity
		$this->db->trans_start();

		if($update_inventory)
		{
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_receiving_items($receiving_id)->result_array();
			foreach($items as $item)
			{
				// create query to update inventory tracking
				$inv_data = array(
					'trans_date' => date('Y-m-d H:i:s'),
					'trans_items' => $item['item_id'],
					'trans_user' => $employee_id,
					'trans_comment' => 'Deleting receiving ' . $receiving_id,
					'trans_location' => $item['item_location'],
					'trans_inventory' => $item['quantity_purchased'] * (-$item['receiving_quantity'])
				);
				// update inventory
				$this->Inventory->insert($inv_data);

				// update quantities
				$this->Item_quantity->change_quantity($item['item_id'], $item['item_location'], $item['quantity_purchased'] * (-$item['receiving_quantity']));
			}
		}

		// delete all items
		$this->db->delete('receivings_items', array('receiving_id' => $receiving_id));
		// delete sale itself
		$this->db->delete('receivings', array('receiving_id' => $receiving_id));

		// execute transaction
		$this->db->trans_complete();
	
		return $this->db->trans_status();
	}

	public function get_receiving_items($receiving_id)
	{
		$this->db->from('receivings_items');
		$this->db->where('receiving_id', $receiving_id);

		return $this->db->get();
	}
	
	public function get_supplier($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id', $receiving_id);

		return $this->Supplier->get_info($this->db->get()->row()->supplier_id);
	}

	public function get_payment_options()
	{
		return array(
			$this->lang->line('sales_cash') => $this->lang->line('sales_cash'),
			$this->lang->line('sales_check') => $this->lang->line('sales_check'),
			$this->lang->line('sales_debit') => $this->lang->line('sales_debit'),
			$this->lang->line('sales_credit') => $this->lang->line('sales_credit'),
			$this->lang->line('sales_due') => $this->lang->line('sales_due')
		);
	}

	/*
	We create a temp table that allows us to do easy report/receiving queries
	*/
	public function create_temp_table(array $inputs)
	{
		if(empty($inputs['receiving_id']))
		{
			if(empty($this->config->item('date_or_time_format')))
			{
				$where = 'WHERE DATE(receiving_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
			}
			else
			{
				$where = 'WHERE receiving_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
			}
		}
		else
		{
			$where = 'WHERE receivings_items.receiving_id = ' . $this->db->escape($inputs['receiving_id']);
		}

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('receivings_items_temp') .
			' (INDEX(receiving_date), INDEX(receiving_time), INDEX(receiving_id))
			(
				SELECT 
					MAX(DATE(receiving_time)) AS receiving_date,
					MAX(receiving_time) AS receiving_time,
					receivings_items.receiving_id AS receiving_id,
					MAX(comment) AS comment,
					MAX(item_location) AS item_location,
					MAX(reference) AS reference,
					MAX(payment_type) AS payment_type,
					MAX(employee_id) AS employee_id, 
					items.item_id AS item_id,
					MAX(receivings.supplier_id) AS supplier_id,
					MAX(quantity_purchased) AS quantity_purchased,
					MAX(receivings_items.receiving_quantity) AS item_receiving_quantity,
					MAX(item_cost_price) AS item_cost_price,
					MAX(item_unit_price) AS item_unit_price,
					MAX(discount) AS discount,
					MAX(discount_type) AS discount_type,
					receivings_items.line AS line,
					MAX(serialnumber) AS serialnumber,
					MAX(receivings_items.description) AS description,
					MAX(CASE WHEN receivings_items.discount_type = ' . PERCENT . ' THEN item_unit_price * quantity_purchased * receivings_items.receiving_quantity - item_unit_price * quantity_purchased * receivings_items.receiving_quantity * discount / 100 ELSE item_unit_price * quantity_purchased * receivings_items.receiving_quantity - discount END) AS subtotal,
					MAX(CASE WHEN receivings_items.discount_type = ' . PERCENT . ' THEN item_unit_price * quantity_purchased * receivings_items.receiving_quantity - item_unit_price * quantity_purchased * receivings_items.receiving_quantity * discount / 100 ELSE item_unit_price * quantity_purchased * receivings_items.receiving_quantity - discount END) AS total,
					MAX((CASE WHEN receivings_items.discount_type = ' . PERCENT . ' THEN item_unit_price * quantity_purchased * receivings_items.receiving_quantity - item_unit_price * quantity_purchased * receivings_items.receiving_quantity * discount / 100 ELSE item_unit_price * quantity_purchased * receivings_items.receiving_quantity - discount END) - (item_cost_price * quantity_purchased)) AS profit,
					MAX(item_cost_price * quantity_purchased * receivings_items.receiving_quantity ) AS cost
				FROM ' . $this->db->dbprefix('receivings_items') . ' AS receivings_items
				INNER JOIN ' . $this->db->dbprefix('receivings') . ' AS receivings
					ON receivings_items.receiving_id = receivings.receiving_id
				INNER JOIN ' . $this->db->dbprefix('items') . ' AS items
					ON receivings_items.item_id = items.item_id
				' . "
				$where
				" . '
				GROUP BY receivings_items.receiving_id, items.item_id, receivings_items.line
			)'
		);
	}
}
?>
