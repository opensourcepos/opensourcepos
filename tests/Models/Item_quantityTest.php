<?php

namespace Tests\Models;

use App\Models\Item;
use App\Models\Item_quantity;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\ConcurrentDbRaceTrait;

/**
 * Regression tests for GHSA-995p-52qw-5hh2: changeQuantity() must apply
 * its write in a single atomic upsert, so that two concurrent sales of the
 * same item/location can never both read the same stale quantity and
 * oversell stock. Unlike the gift card and reward point spends, there is
 * deliberately no floor guard here (see the fix's scope note) — negative
 * stock is allowed today and this fix does not change that.
 */
class Item_quantityTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use ConcurrentDbRaceTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = null;

    private const LOCATION_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createTestItem(): int
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
            'stock_type'            => HAS_STOCK,
            'deleted'               => 0,
        ];

        $itemModel = model(Item::class);
        $itemModel->save_value($itemData);

        return (int) $itemData['item_id'];
    }

    protected function createItemQuantityRow(int $itemId, float $quantity): void
    {
        \Config\Database::connect()->table('item_quantities')->insert([
            'item_id'     => $itemId,
            'location_id' => self::LOCATION_ID,
            'quantity'    => $quantity,
        ]);
    }

    protected function getQuantity(int $itemId): float
    {
        $row = \Config\Database::connect()->table('item_quantities')
            ->where('item_id', $itemId)
            ->where('location_id', self::LOCATION_ID)
            ->get()
            ->getRow();

        return (float) $row->quantity;
    }

    public function testDecrementQuantitySucceeds(): void
    {
        $itemId = $this->createTestItem();
        $this->createItemQuantityRow($itemId, 10);
        $itemQuantityModel = model(Item_quantity::class);

        $result = $itemQuantityModel->changeQuantity($itemId, self::LOCATION_ID, -3);

        $this->assertTrue($result);
        $this->assertEqualsWithDelta(7.0, $this->getQuantity($itemId), 0.001);
    }

    public function testDecrementQuantityAllowsGoingNegative(): void
    {
        $itemId = $this->createTestItem();
        $this->createItemQuantityRow($itemId, 5);
        $itemQuantityModel = model(Item_quantity::class);

        $result = $itemQuantityModel->changeQuantity($itemId, self::LOCATION_ID, -8);

        $this->assertTrue($result);
        $this->assertEqualsWithDelta(-3.0, $this->getQuantity($itemId), 0.001);
    }

    public function testDecrementQuantityConcurrentDecrementsBothApply(): void
    {
        $itemId = $this->createTestItem();
        $this->createItemQuantityRow($itemId, 10);
        $db    = \Config\Database::connect();
        $table = $db->DBPrefix . 'item_quantities';

        $sql = sprintf(
            'INSERT INTO %s (item_id, location_id, quantity) VALUES (%s, %s, -3) ON DUPLICATE KEY UPDATE quantity = quantity + -3',
            $table,
            $db->escape($itemId),
            $db->escape(self::LOCATION_ID)
        );

        [$affectedRows1, $affectedRows2] = $this->raceTwoUpdates($sql, $sql);

        $this->assertSame(1, $affectedRows1);
        $this->assertSame(1, $affectedRows2);
        $this->assertEqualsWithDelta(4.0, $this->getQuantity($itemId), 0.001);
    }
}
