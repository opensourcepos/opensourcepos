<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

/**
 * Item_lot class
 *
 * Tracks the remaining quantity of an item per receiving batch (lot).
 * A receiving from a supplier creates one lot. A receiving_id of 0
 * represents stock with no known lot origin (returned or legacy stock).
 */
class Item_lot extends Model
{
    protected $table            = 'item_lots';
    protected $primaryKey       = 'item_id';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes   = false;

    /**
     * Returns the lots that still have stock for an item at a location,
     * ordered oldest receiving first. The unknown lot (receiving_id 0)
     * is always consumed last.
     */
    public function get_lots(int $item_id, int $location_id): array
    {
        $builder = $this->db->table('item_lots AS item_lots');
        $builder->join('receivings AS receivings', 'receivings.receiving_id = item_lots.receiving_id AND item_lots.receiving_id != 0', 'left');
        $builder->select('item_lots.receiving_id, item_lots.quantity, receivings.receiving_time');
        $builder->where('item_lots.item_id', $item_id);
        $builder->where('item_lots.location_id', $location_id);
        $builder->where('item_lots.quantity >', 0);
        $builder->orderBy('CASE WHEN item_lots.receiving_id = 0 THEN 1 ELSE 0 END', 'asc', false);
        $builder->orderBy('receivings.receiving_time', 'asc');
        $builder->orderBy('item_lots.receiving_id', 'asc');

        $result = $builder->get();
        if ($result === false) {
            throw new RuntimeException($this->db->error()['message'] ?? 'get_lots query failed');
        }

        return $result->getResultArray();
    }

    /**
     * Adds (or subtracts) a quantity to a specific lot.
     */
    public function add_quantity(int $item_id, int $receiving_id, int $location_id, float $quantity): bool
    {
        $builder = $this->db->table('item_lots');
        $builder->where('item_id', $item_id);
        $builder->where('receiving_id', $receiving_id);
        $builder->where('location_id', $location_id);
        $current = $builder->get()->getRow();

        if ($current === null) {
            $builder = $this->db->table('item_lots');

            return $builder->insert([
                'item_id'      => $item_id,
                'receiving_id' => $receiving_id,
                'location_id'  => $location_id,
                'quantity'     => $quantity,
            ]);
        }

        $builder = $this->db->table('item_lots');
        $builder->where('item_id', $item_id);
        $builder->where('receiving_id', $receiving_id);
        $builder->where('location_id', $location_id);

        return $builder->update(['quantity' => $current->quantity + $quantity]);
    }

    /**
     * Moves stock from one location to another, FIFO across the source lots,
     * preserving the receiving_id (supplier attribution) for each unit.
     */
    public function move_between_locations(int $item_id, int $location_from, int $location_to, float $quantity): void
    {
        $remaining = $quantity;

        foreach ($this->get_lots($item_id, $location_from) as $lot) {
            if ($remaining <= 0) {
                break;
            }

            $take = min((float) $lot['quantity'], $remaining);
            $receiving_id = (int) $lot['receiving_id'];

            $this->add_quantity($item_id, $receiving_id, $location_from, -$take);
            $this->add_quantity($item_id, $receiving_id, $location_to, $take);

            $remaining -= $take;
        }

        if ($remaining > 0) {
            $this->add_quantity($item_id, 0, $location_from, -$remaining);
            $this->add_quantity($item_id, 0, $location_to, $remaining);
        }
    }

    /**
     * Consumes stock FIFO across lots and records which lot each sold
     * unit came from in the sales_items_lots table.
     */
    public function consume_and_record(int $item_id, int $location_id, float $quantity, int $sale_id, int $line): void
    {
        $remaining   = $quantity;
        $allocations = [];

        foreach ($this->get_lots($item_id, $location_id) as $lot) {
            if ($remaining <= 0) {
                break;
            }

            $take          = min((float) $lot['quantity'], $remaining);
            $allocations[] = ['receiving_id' => (int) $lot['receiving_id'], 'quantity' => $take];
            $remaining -= $take;
        }

        if ($remaining > 0) {
            $allocations[] = ['receiving_id' => 0, 'quantity' => $remaining];
        }

        $builder = $this->db->table('sales_items_lots');

        foreach ($allocations as $allocation) {
            $this->add_quantity($item_id, $allocation['receiving_id'], $location_id, -$allocation['quantity']);
            $builder->insert([
                'sale_id'      => $sale_id,
                'line'         => $line,
                'receiving_id' => $allocation['receiving_id'],
                'quantity'     => $allocation['quantity'],
            ]);
        }
    }

    /**
     * Credits returned stock back into the unknown lot (receiving_id 0).
     */
    public function return_stock(int $item_id, int $location_id, float $quantity, int $sale_id, int $line): void
    {
        $this->add_quantity($item_id, 0, $location_id, $quantity);
        $this->db->table('sales_items_lots')->insert([
            'sale_id'      => $sale_id,
            'line'         => $line,
            'receiving_id' => 0,
            'quantity'     => $quantity,
        ]);
    }

    /**
     * Restores the lots that a sale line consumed (used when a sale is voided).
     */
    public function restore_lots(int $sale_id, int $line, int $item_id, int $location_id): void
    {
        $builder = $this->db->table('sales_items_lots');
        $builder->where('sale_id', $sale_id);
        $builder->where('line', $line);
        $allocations = $builder->get()->getResultArray();

        foreach ($allocations as $allocation) {
            $this->add_quantity($item_id, (int) $allocation['receiving_id'], $location_id, (float) $allocation['quantity']);
        }

        $builder = $this->db->table('sales_items_lots');
        $builder->where('sale_id', $sale_id);
        $builder->where('line', $line);
        $builder->delete();
    }
}
