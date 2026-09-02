<?php

namespace Tests\Support;

use App\Models\Item;
use Config\Database;

trait ItemFixtureTrait
{
    protected function createTestItem(int $stockType = HAS_STOCK): int
    {
        $itemData = [
            'item_id'               => null,
            'name'                  => 'Test Item',
            'description'           => 'Test Item',
            'category'              => 'Test Category',
            'cost_price'            => 1.00,
            'unit_price'            => 5.00,
            'reorder_level'         => 0,
            'item_number'           => 'TEST-' . uniqid(),
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
            'stock_type'            => $stockType,
            'deleted'               => 0,
        ];

        $itemModel = model(Item::class);
        $itemModel->save_value($itemData);

        return (int) $itemData['item_id'];
    }

    protected function getItemQuantity(int $itemId, int $locationId): float
    {
        $row = Database::connect()->table('item_quantities')
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->get()
            ->getRow();

        return $row === null ? 0.0 : (float) $row->quantity;
    }
}
