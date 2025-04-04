<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Item_kit_items class
 */
class Item_kit_items extends Model
{
    protected $table = 'item_kit_items';
    protected $primaryKey = 'item_kit_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'kit_sequence'
    ];

    /**
     * Gets item kit items for a particular item kit
     */
    public function get_info(int $item_kit_id): array
    {
        $builder = $this->db->table('item_kit_items as item_kit_items');
        $builder->select('item_kits.item_kit_id, item_kit_items.item_id, quantity, kit_sequence, unit_price, item_type, stock_type');
        $builder->join('items as items', 'item_kit_items.item_id = items.item_id');
        $builder->join('item_kits as item_kits', 'item_kits.item_kit_id = item_kit_items.item_kit_id');
        $builder->where('item_kits.item_kit_id', $item_kit_id);
        $builder->orWhere('item_kit_number', $item_kit_id);
        $builder->orderBy('kit_sequence', 'asc');

        // Return an array of item kit items for an item
        return $builder->get()->getResultArray();
    }

    /**
     * Gets item kit items for a particular item kit
     */
    public function get_info_for_sale(int $item_kit_id): array    // TODO: This function does not seem to be called anywhere in the code
    {
        $builder = $this->db->table('item_kit_items');
        $builder->where('item_kit_id', $item_kit_id);

        $builder->orderBy('kit_sequence', 'desc');

        // Return an array of item kit items for an item
        return $builder->get()->getResultArray();
    }

    /**
     * Inserts or updates an item kit's items
     */
    public function save_value(array &$item_kit_items_data, int $item_kit_id): bool
    {
        $success = true;

        $this->db->transStart();

        $this->delete($item_kit_id);

        if ($item_kit_items_data != null) {
            $builder = $this->db->table('item_kit_items');

            foreach ($item_kit_items_data as $row) {
                $row['item_kit_id'] = $item_kit_id;
                $success &= $builder->insert($row);
            }
        }

        $this->db->transComplete();

        $success &= $this->db->transStatus();

        return $success;
    }

    /**
     * Deletes item kit items given an item kit
     */
    public function delete($item_kit_id = null, bool $purge = false): bool
    {
        $builder = $this->db->table('item_kit_items');

        return $builder->delete(['item_kit_id' => $item_kit_id]);
    }
}
