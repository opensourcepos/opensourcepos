<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\Item;
use Config\Database;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\ItemFixtureTrait;

class ItemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use ItemFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private const LOCATION_ID = 1;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();
    }

    public function testSuggestionsColumnValidationRejectsSQLInjection(): void
    {
        // These malicious values should not be in the allowed columns list
        $malicious_columns = [
            'name, SLEEP(5)',
            'name; DROP TABLE items',
            "name' OR '1'='1",
            'name UNION SELECT * FROM users',
            'name--comment'
        ];

        foreach ($malicious_columns as $malicious) {
            $this->assertNotContains($malicious, Item::ALLOWED_SUGGESTIONS_COLUMNS,
                "Malicious input should not be in allowed columns: $malicious");
        }
    }

    public function testSuggestionsColumnValidationAcceptsValidColumns(): void
    {
        // Valid columns should be preserved
        $valid_columns = ['name', 'item_number', 'description', 'cost_price', 'unit_price'];

        foreach ($valid_columns as $column) {
            $this->assertContains($column, Item::ALLOWED_SUGGESTIONS_COLUMNS,
                "Valid column should be in allowed list: $column");
        }
    }

    public function testSuggestionsColumnValidationHandlesEmptyString(): void
    {
        // Empty string should only be allowed for second and third columns
        $this->assertContains('', Item::ALLOWED_SUGGESTIONS_COLUMNS_WITH_EMPTY,
            'Empty string should be allowed for optional columns');
    }

    public function testSuggestionsColumnValidationConstantsAreConsistent(): void
    {
        // Ensure the constants are properly defined
        $this->assertCount(5, Item::ALLOWED_SUGGESTIONS_COLUMNS,
            'Should have exactly 5 allowed columns');
        $this->assertCount(6, Item::ALLOWED_SUGGESTIONS_COLUMNS_WITH_EMPTY,
            'Should have exactly 6 allowed columns including empty');

        // Ensure empty string is only in WITH_EMPTY variant
        $this->assertNotContains('', Item::ALLOWED_SUGGESTIONS_COLUMNS,
            'Empty string should not be in required columns list');
    }

    public function testGetMultipleInfo_ReturnsEmptyResultWhenLocationIdIsNull(): void
    {
        $itemId = $this->createTestItem();
        $itemModel = model(Item::class);

        $result = $itemModel->getMultipleInfo([$itemId], null);

        $this->assertSame(0, $result->getNumRows());
    }

    public function testGetMultipleInfo_ReturnsRowForMatchingLocation(): void
    {
        $itemId = $this->createTestItem();
        \Config\Database::connect()->table('item_quantities')->insert([
            'item_id'     => $itemId,
            'location_id' => self::LOCATION_ID,
            'quantity'    => 5,
        ]);
        $itemModel = model(Item::class);

        $result = $itemModel->getMultipleInfo([$itemId], self::LOCATION_ID);

        $this->assertSame(1, $result->getNumRows());
    }

    protected function createItemQuantityRow(int $itemId, float $quantity): void
    {
        Database::connect()->table('item_quantities')->insert([
            'item_id'     => $itemId,
            'location_id' => self::LOCATION_ID,
            'quantity'    => $quantity,
        ]);
    }

    public function testChangeCostPrice_CalculatesWeightedAverageForPositiveStock(): void
    {
        $itemId = $this->createTestItem();
        $this->createItemQuantityRow($itemId, 10);

        $itemModel = model(Item::class);

        $this->assertTrue($itemModel->change_cost_price($itemId, 10, 20.00, 10.00));
        $this->assertEqualsWithDelta(15.00, (float) $itemModel->getInfo($itemId)->cost_price, 0.001);
    }

    public function testChangeCostPrice_UsesReceivedPriceWhenStockIsZero(): void
    {
        $itemId = $this->createTestItem();

        $itemModel = model(Item::class);

        $this->assertTrue($itemModel->change_cost_price($itemId, 10, 20.00, 10.00));
        $this->assertEqualsWithDelta(20.00, (float) $itemModel->getInfo($itemId)->cost_price, 0.001);
    }

    public function testChangeCostPrice_DoesNotDivideByZeroWhenStockAndReceivedQuantityAreZero(): void
    {
        $itemId = $this->createTestItem();
        $this->createItemQuantityRow($itemId, 0);

        $itemModel = model(Item::class);

        $this->assertTrue($itemModel->change_cost_price($itemId, 0, 20.00, 10.00));
        $this->assertEqualsWithDelta(20.00, (float) $itemModel->getInfo($itemId)->cost_price, 0.001);
    }

    public function testChangeCostPrice_UsesReceivedPriceWhenStockIsNegative(): void
    {
        $itemId = $this->createTestItem();
        $this->createItemQuantityRow($itemId, -5);

        $itemModel = model(Item::class);

        $this->assertTrue($itemModel->change_cost_price($itemId, 10, 20.00, 10.00));

        $costPrice = (float) $itemModel->getInfo($itemId)->cost_price;

        $this->assertEqualsWithDelta(20.00, $costPrice, 0.001);
        $this->assertGreaterThanOrEqual(10.00, $costPrice);
        $this->assertLessThanOrEqual(20.00, $costPrice);
    }
}
