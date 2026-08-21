<?php

namespace Tests\Support;

use App\Models\Attribute;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Item_quantity;

trait ItemSearchFixtureTrait
{
    protected function createSearchableItem(array $overrides = []): int
    {
        $itemData = array_merge([
            'item_id'               => null,
            'name'                  => 'Searchable Item ' . uniqid(),
            'category'              => 'Test Category',
            'cost_price'            => 1.00,
            'unit_price'            => 5.00,
            'reorder_level'         => 0,
            'item_number'           => 'SEARCH-' . uniqid(),
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
            'item_type'             => ITEM,
            'deleted'               => 0,
        ], $overrides);

        $itemModel = model(Item::class);
        $itemModel->save_value($itemData);

        $itemId = (int) $itemData['item_id'];

        $this->addInventoryRecord($itemId);

        return $itemId;
    }

    protected function addInventoryRecord(int $itemId, int $locationId = 1): void
    {
        $inventoryModel = model(Inventory::class);
        $inventoryModel->insert([
            'trans_items'     => $itemId,
            'trans_user'      => 1,
            'trans_comment'   => 'Test fixture',
            'trans_inventory' => 0,
            'trans_location'  => $locationId,
        ]);
    }

    protected function addItemQuantity(int $itemId, int $locationId, float $quantity): void
    {
        $itemQuantityModel = model(Item_quantity::class);
        $itemQuantityModel->save_value(
            [
                'item_id'     => $itemId,
                'location_id' => $locationId,
                'quantity'    => $quantity,
            ],
            $itemId,
            $locationId
        );
    }

    protected function defaultSearchFilters(array $overrides = []): array
    {
        return array_merge([
            'start_date'        => '2000-01-01',
            'end_date'          => '2100-01-01',
            'stock_location_id' => -1,
            'empty_upc'         => false,
            'low_inventory'     => false,
            'is_serialized'     => false,
            'no_description'    => false,
            'search_custom'     => false,
            'is_deleted'        => false,
            'temporary'         => false,
            'definition_ids'    => [],
        ], $overrides);
    }

    protected function createAttributeDefinition(string $name, string $type = TEXT, int $flags = 1): int
    {
        $db = db_connect();
        $db->table('attribute_definitions')->insert([
            'definition_name' => $name,
            'definition_type' => $type,
            'definition_flags' => $flags,
            'deleted' => 0,
        ]);

        return (int) $db->insertID();
    }

    protected function linkAttributeValue(int $itemId, int $definitionId, string $value): void
    {
        $db = db_connect();

        // attribute_value is unique: reuse the existing attribute_id if this value already exists
        $existing = $db->table('attribute_values')->select('attribute_id')->where('attribute_value', $value)->get()->getRow();
        if ($existing !== null) {
            $attributeId = (int) $existing->attribute_id;
        } else {
            $db->table('attribute_values')->insert(['attribute_value' => $value]);
            $attributeId = (int) $db->insertID();
        }

        $db->table('attribute_links')->insert([
            'definition_id' => $definitionId,
            'attribute_id'  => $attributeId,
            'item_id'       => $itemId,
        ]);
    }

    protected function linkTypedAttributeValue(int $itemId, int $definitionId, string $value, string $definitionType): void
    {
        $attributeModel = model(Attribute::class);
        $attributeModel->saveAttributeValue($value, $definitionId, $itemId, false, $definitionType);
    }
}
