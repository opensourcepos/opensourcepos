<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;

/**
 * Inventory class
 *
 * @property employee employee
 *
 */
class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'trans_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'trans_items',
        'trans_user',
        'trans_date',
        'trans_comment',
        'trans_inventory',
        'trans_location'
    ];

    /**
     * @param $comment
     * @param $inventory_data
     * @return bool
     */
    public function update($comment = null, $inventory_data = null): bool
    {
        $builder = $this->db->table('inventory');
        $builder->where('trans_comment', $comment);

        return $builder->update($inventory_data);
    }

    /**
     * Retrieves inventory data given an item_id.
     *
     * @param int $itemId
     * @param bool $location_id
     * @return ResultInterface
     */
    public function get_inventory_data_for_item(int $itemId, bool $location_id = false): ResultInterface
    {
        $builder = $this->db->table('inventory');
        $builder->where('trans_items', $itemId);

        if ($location_id) {
            $builder->where('trans_location', $location_id);
        }

        $builder->orderBy('trans_date', 'desc');

        return $builder->get();
    }

    /**
     * @param int $itemId ID number for the item to have quantity reset.
     * @return bool|int|string The row id of the inventory table on insert or false on failure
     */
    public function reset_quantity(int $itemId): bool|int|string
    {
        $inventorySums = $this->get_inventory_sum($itemId);
        foreach ($inventorySums as $inventorySum) {
            if ($inventorySum['sum'] > 0) {
                $employee = model(Employee::class);

                return $this->insert([
                    'trans_inventory' => -1 * $inventorySum['sum'],
                    'trans_items'     => $itemId,
                    'trans_location'  => $inventorySum['location_id'],
                    'trans_comment'   => lang('Items.is_deleted'),
                    'trans_user'      => $employee->get_logged_in_employee_info()->person_id
                ]);
            }
        }

        return true;
    }

    /**
     * @param int $itemId
     * @return array
     */
    public function get_inventory_sum(int $itemId): array
    {
        $builder = $this->db->table('inventory');
        $builder->select('SUM(trans_inventory) AS sum, MAX(trans_location) AS location_id');
        $builder->where('trans_items', $itemId);
        $builder->groupBy('trans_location');

        return $builder->get()->getResultArray();
    }
}
