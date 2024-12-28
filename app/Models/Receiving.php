<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use Config\OSPOS;
use ReflectionException;

/**
 * Receiving class
 */
class Receiving extends Model
{
	protected $table = 'receivings';
	protected $primaryKey = 'receiving_id';
	protected $useAutoIncrement = true;
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'receiving_time',
		'supplier_id',
		'employee_id',
		'comment',
		'receiving_id',
		'payment_type',
		'reference'
	];

	/**
	 * @param int $receiving_id
	 * @return ResultInterface
	 */
	public function get_info(int $receiving_id): ResultInterface
	{
		$builder = $this->db->table('receivings');
		$builder->join('people', 'people.person_id = receivings.supplier_id', 'LEFT');
		$builder->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
		$builder->where('receiving_id', $receiving_id);

		return $builder->get();
	}

	/**
	 * @param string $reference
	 * @return ResultInterface
	 */
	public function get_receiving_by_reference(string $reference): ResultInterface
	{
		$builder = $this->db->table('receivings');
		$builder->where('reference', $reference);

		return $builder->get();
	}

	/**
	 * @param string $receipt_receiving_id
	 * @return bool
	 */
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

		return false;
	}

	/**
	 * @param int $receiving_id
	 * @return bool
	 */
	public function exists(int $receiving_id): bool
	{
		$builder = $this->db->table('receivings');
		$builder->where('receiving_id', $receiving_id);

		return ($builder->get()->getNumRows() == 1);
	}

	/**
	 * @param $receiving_id
	 * @param $receiving_data
	 * @return bool
	 */
	public function update($receiving_id = null, $receiving_data = null): bool
	{
		$builder = $this->db->table('receivings');
		$builder->where('receiving_id', $receiving_id);

		return $builder->update($receiving_data);
	}

	/**
	 * @throws ReflectionException
	 */
	public function save_value(array $items, int $supplier_id, int $employee_id, string $comment, string $reference, string $payment_type, int $receiving_id = NEW_ENTRY): int	//TODO: $receiving_id gets overwritten before it's evaluated. It doesn't make sense to pass this here.
	{
		$attribute = model(Attribute::class);
		$inventory = model('Inventory');
		$item = model(Item::class);
		$item_quantity = model(Item_quantity::class);
		$supplier = model(Supplier::class);

		if(count($items) == 0)
		{
			return -1;	//TODO: Replace -1 with a constant
		}

		$receivings_data = [
			'receiving_time' => date('Y-m-d H:i:s'),
			'supplier_id' => $supplier->exists($supplier_id) ? $supplier_id : null,
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

		$builder = $this->db->table('receivings_items');

		foreach($items as $line => $item_data)
		{
			$config = config(OSPOS::class)->settings;
			$cur_item_info = $item->get_info($item_data['item_id']);

			$receivings_items_data = [
				'receiving_id' => $receiving_id,
				'item_id' => $item_data['item_id'],
				'line' => $item_data['line'],
				'description' => $item_data['description'],
				'serialnumber' => $item_data['serialnumber'],
				'quantity_purchased' => $item_data['quantity'],
				'receiving_quantity' => $item_data['receiving_quantity'],
				'discount' => $item_data['discount'],
				'discount_type' => $item_data['discount_type'],
				'item_cost_price' => $cur_item_info->cost_price,
				'item_unit_price' => $item_data['price'],
				'item_location' => $item_data['item_location']
			];

			$builder->insert($receivings_items_data);

			$items_received = $item_data['receiving_quantity'] != 0 ? $item_data['quantity'] * $item_data['receiving_quantity'] : $item_data['quantity'];

			// update cost price, if changed AND is set in config as wanted
			if($cur_item_info->cost_price != $item_data['price'] && $config['receiving_calculate_average_price'])
			{
				$item->change_cost_price($item_data['item_id'], $items_received, $item_data['price'], $cur_item_info->cost_price);
			}

			//Update stock quantity
			$item_quantity_value = $item_quantity->get_item_quantity($item_data['item_id'], $item_data['item_location']);
			$item_quantity->save_value([
				'quantity' => $item_quantity_value->quantity + $items_received,
				'item_id' => $item_data['item_id'],
				'location_id' => $item_data['item_location']],
				$item_data['item_id'],
				$item_data['item_location']
			);

			$recv_remarks = 'RECV ' . $receiving_id;
			$inv_data = [
				'trans_date' => date('Y-m-d H:i:s'),
				'trans_items' => $item_data['item_id'],
				'trans_user' => $employee_id,
				'trans_location' => $item_data['item_location'],
				'trans_comment' => $recv_remarks,
				'trans_inventory' => $items_received
			];

			$inventory->insert($inv_data, false);
			$attribute->copy_attribute_links($item_data['item_id'], 'receiving_id', $receiving_id);
		//	$supplier = $supplier->get_info($supplier_id);	//TODO: supplier is never used after this.
		}

		$this->db->transComplete();

		return $this->db->transStatus() ? $receiving_id : -1;
	}

	/**
	 * Save a requisition to the `ospos_receivings` and `ospos_receivings_items` tables
	 * and update stock quantities in the respective locations.
	 *
	 * @throws ReflectionException
	 */
	public function save_requisition(array $items, int $employee_id, string $comment, string $reference, int $stock_source, int $stock_destination): int
	{
		$inventory = model('Inventory');
		$item_quantity_model = model(Item_quantity::class);

		if (count($items) == 0) {
			return -1; // No items to save
		}

		// Prepare the receiving data
		$receivings_data = [
			'receiving_time' => date('Y-m-d H:i:s'),
			'employee_id' => $employee_id,
			'comment' => $comment,
			'reference' => $reference,
			'total' => 0, // This will be updated later
			'amount_change' => 0,
			'amount_tendered' => 0,
			'operation_type' => 'Requisition',
		];

		// Run these queries as a transaction
		$this->db->transStart();

		// Insert into `ospos_receivings`
		$builder = $this->db->table('ospos_receivings');
		$builder->insert($receivings_data);
		$receiving_id = $this->db->insertID();

		if (!$receiving_id) {
			log_message('error', 'Failed to insert requisition header: ' . print_r($receivings_data, true));
			$this->db->transRollback();
			return -1;
		}

		// Insert items into `ospos_receivings_items`
		$builder = $this->db->table('ospos_receivings_items');
		$total_cost = 0;

		foreach ($items as $item_data) {
			$receivings_items_data = [
				'receiving_id' => $receiving_id,
				'item_id' => $item_data['item_id'],
				'line' => $item_data['line'],
				'description' => $item_data['description'] ?? '',
				'serialnumber' => $item_data['serialnumber'] ?? '',
				'quantity_purchased' => $item_data['quantity'],
				'item_cost_price' => $item_data['price'],
				'item_unit_price' => $item_data['price'],
				'discount' => $item_data['discount'] ?? 0,
				'discount_type' => $item_data['discount_type'] ?? 0,
				'item_location' => $item_data['item_location'] ?? 1,
				'receiving_quantity' => $item_data['receiving_quantity'] ?? 1,
			];
		
			$builder->insert($receivings_items_data);
		
			if (!$this->db->affectedRows()) {
				log_message('error', 'Failed to insert requisition item: ' . print_r($receivings_items_data, true));
				$this->db->transRollback();
				return -1;
			}
		
			// Calculate the total cost
			$total_cost += ($item_data['price'] * $item_data['quantity']) - ($item_data['discount'] ?? 0);
		
			// Update stock quantities
			$item_id = $item_data['item_id'] > 0 ? $item_data['item_id'] : 0;
			$quantity = $item_data['quantity'] > 0 ? $item_data['quantity'] : 0;

			if ($item_id === 0 || $quantity === 0) {
				continue; // Skip if item or quantity is invalid
			}		
		
			// Subtract from stock at the source
			$source_quantity = $item_quantity_model->get_item_quantity($item_id, $stock_source);
			$item_quantity_model->save_value([
				'quantity' => $source_quantity->quantity - $quantity,
				'item_id' => $item_id,
				'location_id' => $stock_source,
			], $item_id, $stock_source);
		
			// Add to stock at the destination
			$destination_quantity = $item_quantity_model->get_item_quantity($item_id, $stock_destination);
			$item_quantity_model->save_value([
				'quantity' => $destination_quantity->quantity + $quantity,
				'item_id' => $item_id,
				'location_id' => $stock_destination,
			], $item_id, $stock_destination);
		
			// Register inventory for the outgoing location (only if not already registered)
			$recv_remarks = 'REQ ' . $receiving_id;
		
			$inv_data_source = [
				'trans_date' => date('Y-m-d H:i:s'),
				'trans_items' => $item_id,
				'trans_user' => $employee_id,
				'trans_location' => $stock_source,
				'trans_comment' => $recv_remarks . ' OUT',
				'trans_inventory' => -$quantity,
			];
			$existing_source = $inventory->where([
				'trans_items' => $item_id,
				'trans_location' => $stock_source,
				'trans_comment' => $recv_remarks . ' OUT'
			])->countAllResults();
			if ($existing_source === 0) {
				$inventory->insert($inv_data_source, false);
			}
		
			// Register inventory for the incoming location (only if not already registered)
			$inv_data_destination = [
				'trans_date' => date('Y-m-d H:i:s'),
				'trans_items' => $item_id,
				'trans_user' => $employee_id,
				'trans_location' => $stock_destination,
				'trans_comment' => $recv_remarks . ' IN',
				'trans_inventory' => $quantity,
			];
			$existing_destination = $inventory->where([
				'trans_items' => $item_id,
				'trans_location' => $stock_destination,
				'trans_comment' => $recv_remarks . ' IN'
			])->countAllResults();
			if ($existing_destination === 0) {
				$inventory->insert($inv_data_destination, false);
			}
		}

		// Update the total cost in ospos_receivings
		$this->db->table('ospos_receivings')
			->where('receiving_id', $receiving_id)
			->update(['total' => $total_cost]);

		if (!$this->db->transComplete()) {
			log_message('error', 'Transaction failed for requisition: ' . print_r($receivings_data, true));
			return -1;
		}

		return $receiving_id;
	}

	/**
	 * @throws ReflectionException
	 */
	public function delete_list(array $receiving_ids, int $employee_id, bool $update_inventory = true): bool
	{
		$success = true;

		// start a transaction to assure data integrity
		$this->db->transStart();

		foreach($receiving_ids as $receiving_id)
		{
			$success &= $this->delete_value($receiving_id, $employee_id, $update_inventory);
		}

		// execute transaction
		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	/**
	 * @throws ReflectionException
	 */
	public function delete_value(int $receiving_id, int $employee_id, bool $update_inventory = true): bool
	{
		// start a transaction to assure data integrity
		$this->db->transStart();

		if($update_inventory)
		{
			//TODO: defect, not all item deletions will be undone? get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_receiving_items($receiving_id)->getResultArray();

			$inventory = model('Inventory');
			$item_quantity = model(Item_quantity::class);

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
				$inventory->insert($inv_data, false);

				// update quantities
				$item_quantity->change_quantity($item['item_id'], $item['item_location'], $item['quantity_purchased'] * (-$item['receiving_quantity']));
			}
		}

		//delete all items
		$builder = $this->db->table('receivings_items');
		$builder->delete(['receiving_id' => $receiving_id]);

		//delete sale itself
		$builder = $this->db->table('receivings');
		$builder->delete(['receiving_id' => $receiving_id]);

		// execute transaction
		$this->db->transComplete();

		return $this->db->transStatus();
	}

	/**
	 * @param int $receiving_id
	 * @return ResultInterface
	 */
	public function get_receiving_items(int $receiving_id): ResultInterface
	{
		$builder = $this->db->table('receivings_items');
		$builder->where('receiving_id', $receiving_id);

		return $builder->get();
	}

	/**
	 * @param int $receiving_id
	 * @return object
	 */
	public function get_supplier(int $receiving_id): object
	{
		$builder = $this->db->table('receivings');
		$builder->where('receiving_id', $receiving_id);

		$supplier = model(Supplier::class);
		return $supplier->get_info($builder->get()->getRow()->supplier_id);
	}

	/**
	 * @return array
	 */
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

	/**
	 * Create a temp table that allows us to do easy report/receiving queries
	 */
	public function create_temp_table(array $inputs): void
	{
		$config = config(OSPOS::class)->settings;
		$db_prefix = $this->db->getPrefix();

		if(empty($inputs['receiving_id']))
		{
			$where = empty($config['date_or_time_format'])
				? 'DATE(`receiving_time`) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date'])
				: 'receiving_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}
		else
		{
			$where = 'receivings_items.receiving_id = ' . $this->db->escape($inputs['receiving_id']);
		}

		$builder = $this->db->table('receivings_items');
		$builder->select([
			'MAX(DATE(`receiving_time`)) AS receiving_date',
			'MAX(`receiving_time`) AS receiving_time',
			'receivings_items.receiving_id AS receiving_id',
			'MAX(`comment`) AS comment',
			'MAX(`item_location`) AS item_location',
			'MAX(`reference`) AS reference',
			'MAX(`payment_type`) AS payment_type',
			'MAX(`employee_id`) AS employee_id',
			'items.item_id AS item_id',
			'MAX(`'. $db_prefix .'receivings`.`supplier_id`) AS supplier_id',
			'MAX(`quantity_purchased`) AS quantity_purchased',
			'MAX(`'. $db_prefix .'receivings_items`.`receiving_quantity`) AS item_receiving_quantity',
			'MAX(`item_cost_price`) AS item_cost_price',
			'MAX(`item_unit_price`) AS item_unit_price',
			'MAX(`discount`) AS discount',
			'MAX(`discount_type`) AS discount_type',
			'receivings_items.line AS line',
			'MAX(`serialnumber`) AS serialnumber',
			'MAX(`'. $db_prefix .'receivings_items`.`description`) AS description',
			'MAX(CASE WHEN `'. $db_prefix .'receivings_items`.`discount_type` = ' . PERCENT . ' THEN `item_unit_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` - `item_unit_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` * `discount` / 100 ELSE `item_unit_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` - `discount` END) AS subtotal',
			'MAX(CASE WHEN `'. $db_prefix .'receivings_items`.`discount_type` = ' . PERCENT . ' THEN `item_unit_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` - `item_unit_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` * `discount` / 100 ELSE `item_unit_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` - `discount` END) AS total',
			'MAX((CASE WHEN `'. $db_prefix .'receivings_items`.`discount_type` = ' . PERCENT . ' THEN `item_unit_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` - `item_unit_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` * `discount` / 100 ELSE `item_unit_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` - discount END) - (`item_cost_price` * `quantity_purchased`)) AS profit',
			'MAX(`item_cost_price` * `quantity_purchased` * `'. $db_prefix .'receivings_items`.`receiving_quantity` ) AS cost'
		]);
		$builder->join('receivings', 'receivings_items.receiving_id = receivings.receiving_id', 'inner');
		$builder->join('items', 'receivings_items.item_id = items.item_id', 'inner');
		$builder->where($where);
		$builder->groupBy(['receivings_items.receiving_id', 'items.item_id', 'receivings_items.line']);
		$selectQuery = $builder->getCompiledSelect();

		//QueryBuilder does not support creating temporary tables.
		$sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('receivings_items_temp') .
			' (INDEX(receiving_date), INDEX(receiving_time), INDEX(receiving_id)) AS (' . $selectQuery . ')';

		$this->db->query($sql);
	}
}
