<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;

/**
 * Transfer class
 *
 * Moves stock between two stock locations in a single transaction.
 * Lot/supplier attribution is preserved: the same receiving_id is moved
 * from the source location to the destination location.
 */
class Transfer extends Model
{
    protected $table            = 'transfers';
    protected $primaryKey       = 'transfer_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'transfer_time',
        'employee_id',
        'location_from',
        'location_to',
        'comment',
    ];

    public function get_info(int $transfer_id): ResultInterface
    {
        $builder = $this->db->table('transfers AS transfers');
        $builder->join('stock_locations AS location_from', 'location_from.location_id = transfers.location_from', 'LEFT');
        $builder->join('stock_locations AS location_to', 'location_to.location_id = transfers.location_to', 'LEFT');
        $builder->join('people AS employee', 'employee.person_id = transfers.employee_id', 'LEFT');
        $builder->select(
            'transfers.transfer_id,
            transfers.transfer_time,
            transfers.employee_id,
            transfers.location_from,
            transfers.location_to,
            transfers.comment,
            CONCAT(employee.first_name, " ", employee.last_name) AS employee_name,
            location_from.location_name AS location_from_name,
            location_to.location_name AS location_to_name',
        );
        $builder->where('transfers.transfer_id', $transfer_id);

        return $builder->get();
    }

    public function get_transfer_items(int $transfer_id): ResultInterface
    {
        $builder = $this->db->table('transfers_items AS transfers_items');
        $builder->join('items AS items', 'items.item_id = transfers_items.item_id');
        $builder->select(
            'transfers_items.item_id,
            transfers_items.line,
            transfers_items.quantity,
            transfers_items.description,
            transfers_items.item_location,
            items.item_number,
            items.name',
        );
        $builder->where('transfers_items.transfer_id', $transfer_id);
        $builder->orderBy('transfers_items.line', 'asc');

        return $builder->get();
    }

    /**
     * Persists the transfer and moves stock between locations.
     *
     * @param array $items Cart items keyed by line
     *
     * @return int The transfer_id on success or -1 on failure
     */
    public function save_value(array $items, int $employee_id, int $location_from, int $location_to, string $comment): int
    {
        $item_quantity = model(Item_quantity::class);
        $item_lot      = model(Item_lot::class);
        $inventory     = model('Inventory');

        if (count($items) === 0) {
            return -1;
        }

        $this->db->transStart();

        $this->db->table('transfers')->insert([
            'transfer_time' => date('Y-m-d H:i:s'),
            'employee_id'   => $employee_id,
            'location_from' => $location_from,
            'location_to'   => $location_to,
            'comment'       => $comment,
        ]);
        $transfer_id = $this->db->insertID();

        $builder = $this->db->table('transfers_items');

        foreach ($items as $line => $item_data) {
            $quantity = (float) $item_data['quantity'];

            $builder->insert([
                'transfer_id'   => $transfer_id,
                'item_id'       => $item_data['item_id'],
                'line'          => $item_data['line'],
                'quantity'      => $quantity,
                'item_location' => $location_from,
                'description'   => $item_data['description'] ?? '',
            ]);

            // Move stock quantities
            $item_quantity->change_quantity($item_data['item_id'], $location_from, -$quantity);
            $item_quantity->change_quantity($item_data['item_id'], $location_to, $quantity);

            // Move lots FIFO from source to destination preserving supplier attribution
            $item_lot->move_between_locations($item_data['item_id'], $location_from, $location_to, $quantity);

            // Inventory audit records for both locations
            $inventory->insert([
                'trans_date'      => date('Y-m-d H:i:s'),
                'trans_items'     => $item_data['item_id'],
                'trans_user'      => $employee_id,
                'trans_comment'   => 'TRANSFER OUT ' . $transfer_id,
                'trans_inventory' => -$quantity,
                'trans_location'  => $location_from,
            ], false);

            $inventory->insert([
                'trans_date'      => date('Y-m-d H:i:s'),
                'trans_items'     => $item_data['item_id'],
                'trans_user'      => $employee_id,
                'trans_comment'   => 'TRANSFER IN ' . $transfer_id,
                'trans_inventory' => $quantity,
                'trans_location'  => $location_to,
            ], false);
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $transfer_id : -1;
    }

    /**
     * Reverses a transfer, moving stock back to the source location.
     */
    public function delete_transfer(int $transfer_id): bool
    {
        $item_quantity = model(Item_quantity::class);
        $item_lot      = model(Item_lot::class);
        $inventory     = model('Inventory');

        $transfer = $this->get_info($transfer_id)->getRowArray();
        if ($transfer === null) {
            return false;
        }

        $this->db->transStart();

        foreach ($this->get_transfer_items($transfer_id)->getResultArray() as $item) {
            $quantity = (float) $item['quantity'];

            // Reverse quantities: source gains it back, destination loses it
            $item_quantity->change_quantity($item['item_id'], (int) $transfer['location_from'], $quantity);
            $item_quantity->change_quantity($item['item_id'], (int) $transfer['location_to'], -$quantity);

            // Reverse lots
            $item_lot->move_between_locations($item['item_id'], (int) $transfer['location_to'], (int) $transfer['location_from'], $quantity);

            // Inventory audit records
            $inventory->insert([
                'trans_date'      => date('Y-m-d H:i:s'),
                'trans_items'     => $item['item_id'],
                'trans_user'      => $transfer['employee_id'],
                'trans_comment'   => 'TRANSFER REVERSED ' . $transfer_id,
                'trans_inventory' => $quantity,
                'trans_location'  => $transfer['location_from'],
            ], false);

            $inventory->insert([
                'trans_date'      => date('Y-m-d H:i:s'),
                'trans_items'     => $item['item_id'],
                'trans_user'      => $transfer['employee_id'],
                'trans_comment'   => 'TRANSFER REVERSED ' . $transfer_id,
                'trans_inventory' => -$quantity,
                'trans_location'  => $transfer['location_to'],
            ], false);
        }

        $this->db->table('transfers_items')->where('transfer_id', $transfer_id)->delete();
        $this->db->table('transfers')->where('transfer_id', $transfer_id)->delete();

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
