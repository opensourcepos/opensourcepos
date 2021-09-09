<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Receiving class
 *
 * @property attribute attribute
 * @property appconfig config
 * @property inventory inventory
 * @property item item
 * @property item_quantity item_quantity
 * @property supplier supplier
 */

class Receiving extends Model
{
	public function __construct()
	{
		parent::__construct();

		$this->attribute = model('Attribute');
		$this->config = model('Appconfig');
		$this->inventory = model('Inventory');
		$this->item = model('Item');
		$this->item_quantity = model('Item_quantity');
		$this->supplier = model('Supplier');
	}

	public function get_info(int $receiving_id)
	{
		$builder = $this->db->table('receivings');
		$builder->join('people', 'people.person_id = receivings.supplier_id', 'LEFT');
		$builder->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
		$builder->where('receiving_id', $receiving_id);

		return $builder->get();
	}

	public function get_receiving_by_reference(string $reference)
	{
		$builder = $this->db->table('receivings');
		$builder->where('reference', $reference);

		return $builder->get();
	}

	public function is_valid_receipt(string $receipt_receiving_id): bool	//TODO: maybe receipt_receiving_id should be an array rather than a space delimited string
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
				return $this->get_receiving_by_reference($receipt_receiving_id)->getNumRows() > 0;
			}
		}

		return FALSE;
	}

	public function exists(int $receiving_id): bool
	{
		$builder = $this->db->table('receivings');
		$builder->where('receiving_id', $receiving_id);

		return ($builder->get()->getNumRows() == 1);
	}

	public function update(array $receiving_data, int $receiving_id): bool
	{
		$builder = $this->db->table('receivings');
		$builder->where('receiving_id', $receiving_id);

		return $builder->update($receiving_data);
	}

	public function save(array $items, int $supplier_id, int $employee_id, string $comment, string $reference, string $payment_type, bool $receiving_id = FALSE): int	//TODO: the base model is expecting the return type to be a bool.  We need to either override this function properly or rename it, unless there is another solution
	{
		if(count($items) == 0)
		{
			return -1;
		}

		$receivings_data = [
			'receiving_time' => date('Y-m-d H:i:s'),
			'supplier_id' => $this->supplier->exists($supplier_id) ? $supplier_id : NULL,
			'employee_id' => $employee_id,
			'payment_type' => $payment_type,
			'comment' => $comment,
			'reference' => $reference
		];

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		$builder = $this->db->table('receivings');
		$builder->insert($receivings_data);
		$receiving_id = $this->db->insertID();

		foreach($items as $line=>$item)
		{
			$cur_item_info = $this->item->get_info($item['item_id']);

			$receivings_items_data = [
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
			];

			$builder = $this->db->table('receivings_items');
			$builder->insert($receivings_items_data);

			$items_received = $item['receiving_quantity'] != 0 ? $item['quantity'] * $item['receiving_quantity'] : $item['quantity'];

			// update cost price, if changed AND is set in config as wanted
			if($cur_item_info->cost_price != $item['price'] && $this->config->get('receiving_calculate_average_price') != FALSE)
			{
				$this->item->change_cost_price($item['item_id'], $items_received, $item['price'], $cur_item_info->cost_price);
			}

			//Update stock quantity
			$item_quantity = $this->item_quantity->get_item_quantity($item['item_id'], $item['item_location']);
			$this->item_quantity->save([
				'quantity' => $item_quantity->quantity + $items_received,
				'item_id' => $item['item_id'],
				'location_id' => $item['item_location']],
				$item['item_id'],
				$item['item_location']
			);

			$recv_remarks = 'RECV ' . $receiving_id;
			$inv_data = [
				'trans_date' => date('Y-m-d H:i:s'),
				'trans_items' => $item['item_id'],
				'trans_user' => $employee_id,
				'trans_location' => $item['item_location'],
				'trans_comment' => $recv_remarks,
				'trans_inventory' => $items_received
			];

			$this->inventory->insert($inv_data);
			$this->attribute->copy_attribute_links($item['item_id'], 'receiving_id', $receiving_id);
			$supplier = $this->supplier->get_info($supplier_id);	//TODO: supplier is never used after this.
		}

		$this->db->transComplete();

		if($this->db->transStatus() === FALSE)	//TODO: Probably better written as return $this->db->transStatus() ? $receiving_id : -1;
		{
			return -1;
		}

		return $receiving_id;
	}

	public function delete_list(array $receiving_ids, int $employee_id, bool $update_inventory = TRUE): bool
	{
		$success = TRUE;

		// start a transaction to assure data integrity
		$this->db->transStart();

		foreach($receiving_ids as $receiving_id)
		{
			$success &= $this->delete($receiving_id, $employee_id, $update_inventory);
		}

		// execute transaction
		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	public function delete(int $receiving_id, int $employee_id, bool $update_inventory = TRUE): bool
	{
		// start a transaction to assure data integrity
		$this->db->transStart();

		if($update_inventory)
		{
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_receiving_items($receiving_id)->getResultArray();
			foreach($items as $item)
			{
				// create query to update inventory tracking
				$inv_data = [
					'trans_date' => date('Y-m-d H:i:s'),
					'trans_items' => $item['item_id'],
					'trans_user' => $employee_id,
					'trans_comment' => 'Deleting receiving ' . $receiving_id,
					'trans_location' => $item['item_location'],
					'trans_inventory' => $item['quantity_purchased'] * (-$item['receiving_quantity'])
				];
				// update inventory
				$this->inventory->insert($inv_data);

				// update quantities
				$this->item_quantity->change_quantity($item['item_id'], $item['item_location'], $item['quantity_purchased'] * (-$item['receiving_quantity']));
			}
		}

		// delete all items
		$builder = $this->db->table('receivings_items');
		$builder->delete(['receiving_id' => $receiving_id]);
		// delete sale itself

		$builder = $this->db->table('receivings');
		$builder->delete(['receiving_id' => $receiving_id]);

		// execute transaction
		$this->db->transComplete();
	
		return $this->db->transStatus();
	}

	public function get_receiving_items(int $receiving_id)
	{
		$builder = $this->db->table('receivings_items');
		$builder->where('receiving_id', $receiving_id);

		return $builder->get();
	}
	
	public function get_supplier(int $receiving_id)
	{
		$builder = $this->db->table('receivings');
		$builder->where('receiving_id', $receiving_id);

		return $this->supplier->get_info($builder->get()->getRow()->supplier_id);
	}

	public function get_payment_options(): array
	{
		return [
			lang('Sales.cash') => lang('Sales.cash'),
			lang('Sales.check') => lang('Sales.check'),
			lang('Sales.debit') => lang('Sales.debit'),
			lang('Sales.credit') => lang('Sales.credit'),
			lang('Sales.due') => lang('Sales.due')
		];
	}

	/*
	We create a temp table that allows us to do easy report/receiving queries
	*/
	public function create_temp_table(array $inputs)
	{
		if(empty($inputs['receiving_id']))
		{
			if(empty($this->config->get('date_or_time_format')))
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

		$sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('receivings_items_temp') .
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
				FROM ' . $this->db->prefixTable('receivings_items') . ' AS receivings_items
				INNER JOIN ' . $this->db->prefixTable('receivings') . ' AS receivings
					ON receivings_items.receiving_id = receivings.receiving_id
				INNER JOIN ' . $this->db->prefixTable('items') . ' AS items
					ON receivings_items.item_id = items.item_id
				' . "
				$where
				" . '
				GROUP BY receivings_items.receiving_id, items.item_id, receivings_items.line
			)';

		$this->db->query($sql);
	}
}
?>
