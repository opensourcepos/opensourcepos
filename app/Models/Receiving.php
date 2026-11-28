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
     * @param int $receivingId
     * @return ResultInterface
     */
    public function get_info(int $receivingId): ResultInterface
    {
        $builder = $this->db->table('receivings');
        $builder->join('people', 'people.person_id = receivings.supplier_id', 'LEFT');
        $builder->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
        $builder->where('receiving_id', $receivingId);

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
    public function is_valid_receipt(string $receipt_receiving_id): bool    // TODO: maybe receipt_receiving_id should be an array rather than a space delimited string
    {
        if (!empty($receipt_receiving_id)) {
            // RECV #
            $pieces = explode(' ', $receipt_receiving_id);

            if (count($pieces) == 2 && preg_match('/(RECV|KIT)/', $pieces[0])) {
                return $this->exists($pieces[1]);
            } else {
                return $this->get_receiving_by_reference($receipt_receiving_id)->getNumRows() > 0;
            }
        }

        return false;
    }

    /**
     * @param int $receivingId
     * @return bool
     */
    public function exists(int $receivingId): bool
    {
        $builder = $this->db->table('receivings');
        $builder->where('receiving_id', $receivingId);

        return ($builder->get()->getNumRows() == 1);
    }

    /**
     * @param $receivingId
     * @param $receiving_data
     * @return bool
     */
    public function update($receivingId = null, $receiving_data = null): bool
    {
        $builder = $this->db->table('receivings');
        $builder->where('receiving_id', $receivingId);

        return $builder->update($receiving_data);
    }

    /**
     * @throws ReflectionException
     */
    public function save_value(array $items, int $supplier_id, int $employeeId, string $comment, string $reference, ?string $payment_type, int $receivingId = NEW_ENTRY): int    // TODO: $receivingId gets overwritten before it's evaluated. It doesn't make sense to pass this here.
    {
        $attribute = model(Attribute::class);
        $inventory = model('Inventory');
        $item = model(Item::class);
        $itemQuantity = model(Item_quantity::class);
        $supplier = model(Supplier::class);

        if (count($items) == 0) {
            return -1;    // TODO: Replace -1 with a constant
        }

        $receivings_data = [
            'receiving_time' => date('Y-m-d H:i:s'),
            'supplier_id'    => $supplier->exists($supplier_id) ? $supplier_id : null,
            'employee_id'    => $employeeId,
            'payment_type'   => $payment_type,
            'comment'        => $comment,
            'reference'      => $reference
        ];

        // Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->transStart();

        $builder = $this->db->table('receivings');
        $builder->insert($receivings_data);
        $receivingId = $this->db->insertID();

        $builder = $this->db->table('receivings_items');

        foreach ($items as $line => $item_data) {
            $config = config(OSPOS::class)->settings;
            $cur_item_info = $item->getInfo($item_data['item_id']);

            $receivings_items_data = [
                'receiving_id'       => $receivingId,
                'item_id'            => $item_data['item_id'],
                'line'               => $item_data['line'],
                'description'        => $item_data['description'],
                'serialnumber'       => $item_data['serialnumber'],
                'quantity_purchased' => $item_data['quantity'],
                'receiving_quantity' => $item_data['receiving_quantity'],
                'discount'           => $item_data['discount'],
                'discount_type'      => $item_data['discount_type'],
                'item_cost_price'    => $cur_item_info->cost_price,
                'item_unit_price'    => $item_data['price'],
                'item_location'      => $item_data['item_location']
            ];

            $builder->insert($receivings_items_data);

            $items_received = $item_data['receiving_quantity'] != 0 ? $item_data['quantity'] * $item_data['receiving_quantity'] : $item_data['quantity'];

            // Update cost price, if changed AND is set in config as wanted
            if ($cur_item_info->cost_price != $item_data['price'] && $config['receiving_calculate_average_price']) {
                $item->change_cost_price($item_data['item_id'], $items_received, $item_data['price'], $cur_item_info->cost_price);
            }

            // Update stock quantity
            $item_quantity_value = $itemQuantity->get_item_quantity($item_data['item_id'], $item_data['item_location']);
            $itemQuantity->save_value(
                [
                    'quantity'    => $item_quantity_value->quantity + $items_received,
                    'item_id'     => $item_data['item_id'],
                    'location_id' => $item_data['item_location']
                ],
                $item_data['item_id'],
                $item_data['item_location']
            );

            $recv_remarks = 'RECV ' . $receivingId;
            $invData = [
                'trans_date'      => date('Y-m-d H:i:s'),
                'trans_items'     => $item_data['item_id'],
                'trans_user'      => $employeeId,
                'trans_location'  => $item_data['item_location'],
                'trans_comment'   => $recv_remarks,
                'trans_inventory' => $items_received
            ];

            $inventory->insert($invData, false);
            $attribute->copy_attribute_links($item_data['item_id'], 'receiving_id', $receivingId);
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $receivingId : -1;
    }


    /**
     * @throws ReflectionException
     */
    public function delete_list(array $receiving_ids, int $employeeId, bool $updateInventory = true): bool
    {
        $success = true;

        // Start a transaction to assure data integrity
        $this->db->transStart();

        foreach ($receiving_ids as $receivingId) {
            $success &= $this->delete_value($receivingId, $employeeId, $updateInventory);
        }

        // Execute transaction
        $this->db->transComplete();

        $success &= $this->db->transStatus();

        return $success;
    }

    /**
     * @throws ReflectionException
     */
    public function delete_value(int $receivingId, int $employeeId, bool $updateInventory = true): bool
    {
        // Start a transaction to assure data integrity
        $this->db->transStart();

        if ($updateInventory) {
            // TODO: defect, not all item deletions will be undone? get array with all the items involved in the sale to update the inventory tracking
            $items = $this->get_receiving_items($receivingId)->getResultArray();

            $inventory = model('Inventory');
            $itemQuantity = model(Item_quantity::class);

            foreach ($items as $item) {
                // Create query to update inventory tracking
                $invData = [
                    'trans_date'      => date('Y-m-d H:i:s'),
                    'trans_items'     => $item['item_id'],
                    'trans_user'      => $employeeId,
                    'trans_comment'   => 'Deleting receiving ' . $receivingId,
                    'trans_location'  => $item['item_location'],
                    'trans_inventory' => $item['quantity_purchased'] * (-$item['receiving_quantity'])
                ];
                // Update inventory
                $inventory->insert($invData, false);

                // Update quantities
                $itemQuantity->changeQuantity($item['item_id'], $item['item_location'], $item['quantity_purchased'] * (-$item['receiving_quantity']));
            }
        }

        // Delete all items
        $builder = $this->db->table('receivings_items');
        $builder->delete(['receiving_id' => $receivingId]);

        // Delete sale itself
        $builder = $this->db->table('receivings');
        $builder->delete(['receiving_id' => $receivingId]);

        // Execute transaction
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * @param int $receivingId
     * @return ResultInterface
     */
    public function get_receiving_items(int $receivingId): ResultInterface
    {
        $builder = $this->db->table('receivings_items');
        $builder->where('receiving_id', $receivingId);

        return $builder->get();
    }

    /**
     * @param int $receivingId
     * @return object
     */
    public function get_supplier(int $receivingId): object
    {
        $builder = $this->db->table('receivings');
        $builder->where('receiving_id', $receivingId);

        $supplier = model(Supplier::class);
        return $supplier->getInfo($builder->get()->getRow()->supplier_id);
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
            lang('Sales.due') => lang('Sales.due'),
            lang('Sales.bank_transfer') => lang('Sales.bank_transfer'),
            lang('Sales.wallet') => lang('Sales.wallet')
        ];
    }

    /**
     * Create a temp table that allows us to do easy report/receiving queries
     */
    public function create_temp_table(array $inputs): void
    {
        $config = config(OSPOS::class)->settings;
        $dbPrefix = $this->db->getPrefix();

        if (empty($inputs['receiving_id'])) {
            $where = empty($config['date_or_time_format'])
                ? 'DATE(`receiving_time`) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date'])
                : 'receiving_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
        } else {
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
            'MAX(`' . $dbPrefix . 'receivings`.`supplier_id`) AS supplier_id',
            'MAX(`quantity_purchased`) AS quantity_purchased',
            'MAX(`' . $dbPrefix . 'receivings_items`.`receiving_quantity`) AS item_receiving_quantity',
            'MAX(`item_cost_price`) AS item_cost_price',
            'MAX(`item_unit_price`) AS item_unit_price',
            'MAX(`discount`) AS discount',
            'MAX(`discount_type`) AS discount_type',
            'receivings_items.line AS line',
            'MAX(`serialnumber`) AS serialnumber',
            'MAX(`' . $dbPrefix . 'receivings_items`.`description`) AS description',
            'MAX(CASE WHEN `' . $dbPrefix . 'receivings_items`.`discount_type` = ' . PERCENT . ' THEN `item_unit_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` - `item_unit_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` * `discount` / 100 ELSE `item_unit_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` - `discount` END) AS subtotal',
            'MAX(CASE WHEN `' . $dbPrefix . 'receivings_items`.`discount_type` = ' . PERCENT . ' THEN `item_unit_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` - `item_unit_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` * `discount` / 100 ELSE `item_unit_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` - `discount` END) AS total',
            'MAX((CASE WHEN `' . $dbPrefix . 'receivings_items`.`discount_type` = ' . PERCENT . ' THEN `item_unit_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` - `item_unit_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` * `discount` / 100 ELSE `item_unit_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` - discount END) - (`item_cost_price` * `quantity_purchased`)) AS profit',
            'MAX(`item_cost_price` * `quantity_purchased` * `' . $dbPrefix . 'receivings_items`.`receiving_quantity` ) AS cost'
        ]);
        $builder->join('receivings', 'receivings_items.receiving_id = receivings.receiving_id', 'inner');
        $builder->join('items', 'receivings_items.item_id = items.item_id', 'inner');
        $builder->where($where);
        $builder->groupBy(['receivings_items.receiving_id', 'items.item_id', 'receivings_items.line']);
        $selectQuery = $builder->getCompiledSelect();

        // QueryBuilder does not support creating temporary tables.
        $sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('receivings_items_temp') .
            ' (INDEX(receiving_date), INDEX(receiving_time), INDEX(receiving_id)) AS (' . $selectQuery . ')';

        $this->db->query($sql);
    }
}
