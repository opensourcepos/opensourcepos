<?php

namespace App\Models;

use CodeIgniter\Database\RawSql;
use CodeIgniter\Model;
use stdClass;

/**
 * Item_quantity class
 */
class Item_quantity extends Model
{
    protected $table = 'item_quantities';
    protected $primaryKey = 'item_id';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'quantity'
    ];

    protected $item_id;
    protected $location_id;
    protected $quantity;

    /**
     * @param int $item_id
     * @param int $location_id
     * @return bool
     */
    public function exists(int $item_id, int $location_id): bool
    {
        $builder = $this->db->table('item_quantities');
        $builder->where('item_id', $item_id);
        $builder->where('location_id', $location_id);

        return ($builder->get()->getNumRows() == 1);    // TODO: ===
    }

    /**
     * @param array $location_detail
     * @param int $item_id
     * @param int $location_id
     * @return bool
     */
    public function save_value(array $location_detail, int $item_id, int $location_id): bool
    {
        if (!$this->exists($item_id, $location_id)) {
            $builder = $this->db->table('item_quantities');
            return $builder->insert($location_detail);
        }

        $builder = $this->db->table('item_quantities');
        $builder->where('item_id', $item_id);
        $builder->where('location_id', $location_id);

        return $builder->update($location_detail);
    }

    /**
     * @param int $item_id
     * @param int $location_id
     * @return array|Item_quantity|stdClass|null
     */
    public function get_item_quantity(int $item_id, int $location_id): array|Item_quantity|StdClass|null
    {
        $builder = $this->db->table('item_quantities');
        $builder->where('item_id', $item_id);
        $builder->where('location_id', $location_id);
        $result = $builder->get()->getRow();

        if (empty($result)) {
            // Get empty base parent object, as $item_id is NOT an item
            $result = model(Item_quantity::class);

            // Get all the fields from items table (TODO: to be reviewed)
            foreach ($this->db->getFieldNames('item_quantities') as $field) {
                $result->$field = '';
            }

            $result->quantity = 0;
        }

        return $result;
    }

    /**
     * Get all the quantities of the given items. Used by plugins. Do not remove from code.
     *
     * @param array $itemIds
     * @param bool $getDeleted
     * @return array
     */
    public function getBulkItemQuantities(array $itemIds, bool $getDeleted = false): array
    {
        $builder = $this->db->table('item_quantities');
        $builder->whereIn('item_quantities.item_id', $itemIds);

        if (!$getDeleted) {
            $builder->join('stock_locations', 'stock_locations.location_id = item_quantities.location_id');
            $builder->where('stock_locations.deleted', 0);
            $builder->select('item_quantities.*');
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Atomically changes an item's quantity at a location by a signed delta.
     * Positive delta adds; negative delta subtracts. Creates the
     * item_quantities row if it doesn't yet exist. Negative resulting
     * quantity is allowed (no floor guard).
     */
    public function changeQuantity(int $itemId, int $locationId, float $quantityChange): bool
    {
        $builder = $this->db->table('item_quantities');
        $builder->set([
            'item_id'     => $itemId,
            'location_id' => $locationId,
            'quantity'    => $quantityChange,
        ]);
        $builder->updateFields(['quantity' => new RawSql('quantity + ' . $this->db->escape($quantityChange))]);

        return $builder->upsert() !== false;
    }

    /**
     * Set to 0 all quantity in the given item
     */
    public function reset_quantity(int $item_id): bool
    {
        $builder = $this->db->table('item_quantities');
        $builder->where('item_id', $item_id);

        return $builder->update(['quantity' => 0]);
    }

    /**
     * Set to 0 all quantity in the given list of items
     */
    public function reset_quantity_list(array $item_ids): bool
    {
        $builder = $this->db->table('item_quantities');
        $builder->whereIn('item_id', $item_ids);

        return $builder->update(['quantity' => 0]);
    }
}
