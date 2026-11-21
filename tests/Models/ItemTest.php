<?php

namespace Tests\Models;

use App\Models\Attribute;
use App\Models\Item;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Config\OSPOS;
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

        config(OSPOS::class)->update_settings();
    }

    public function testSuggestionsColumnValidationRejectsSQLInjection(): void
    {
        // These malicious values should not be in the allowed columns list
        $maliciousColumns = [
            'name, SLEEP(5)',
            'name; DROP TABLE items',
            "name' OR '1'='1",
            'name UNION SELECT * FROM users',
            'name--comment'
        ];

        foreach ($maliciousColumns as $malicious) {
            $this->assertNotContains(
                $malicious,
                Item::ALLOWED_SUGGESTIONS_COLUMNS,
                "Malicious input should not be in allowed columns: $malicious"
            );
        }
    }

    public function testSuggestionsColumnValidationAcceptsValidColumns(): void
    {
        // Valid columns should be preserved
        $validColumns = ['name', 'item_number', 'description', 'cost_price', 'unit_price'];

        foreach ($validColumns as $column) {
            $this->assertContains(
                $column,
                Item::ALLOWED_SUGGESTIONS_COLUMNS,
                "Valid column should be in allowed list: $column"
            );
        }
    }

    public function testSuggestionsColumnValidationHandlesEmptyString(): void
    {
        // Empty string should only be allowed for second and third columns
        $this->assertContains(
            '',
            Item::ALLOWED_SUGGESTIONS_COLUMNS_WITH_EMPTY,
            'Empty string should be allowed for optional columns'
        );
    }

    public function testSuggestionsColumnValidationConstantsAreConsistent(): void
    {
        // Ensure the constants are properly defined
        $this->assertCount(
            5,
            Item::ALLOWED_SUGGESTIONS_COLUMNS,
            'Should have exactly 5 allowed columns'
        );
        $this->assertCount(
            6,
            Item::ALLOWED_SUGGESTIONS_COLUMNS_WITH_EMPTY,
            'Should have exactly 6 allowed columns including empty'
        );

        // Ensure empty string is only in WITH_EMPTY variant
        $this->assertNotContains(
            '',
            Item::ALLOWED_SUGGESTIONS_COLUMNS,
            'Empty string should not be in required columns list'
        );
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
        Database::connect()->table('item_quantities')->insert([
            'item_id'     => $itemId,
            'location_id' => self::LOCATION_ID,
            'quantity'    => 5,
        ]);
        $itemModel = model(Item::class);

        $result = $itemModel->getMultipleInfo([$itemId], self::LOCATION_ID);

        $this->assertSame(1, $result->getNumRows());
    }

    public function testChangeCostPrice_UsesNewPriceWhenExistingQuantityIsZeroOrNegative(): void
    {
        $itemId = $this->createTestItem();
        Database::connect()->table('item_quantities')->insert([
            'item_id'     => $itemId,
            'location_id' => self::LOCATION_ID,
            'quantity'    => -5,
        ]);
        $itemModel = model(Item::class);

        $result = $itemModel->change_cost_price($itemId, 5.0, 12.50, 10.00);

        $this->assertTrue($result);
        $updatedItem = $itemModel->getInfo($itemId);
        $this->assertEqualsWithDelta(12.50, (float) $updatedItem->cost_price, 0.01);
    }

    public function testChangeCostPrice_StoresLocaleIndependentDecimalWhenQuantityNonPositive(): void
    {
        $itemId = $this->createTestItem();
        Database::connect()->table('item_quantities')->insert([
            'item_id'     => $itemId,
            'location_id' => self::LOCATION_ID,
            'quantity'    => 0,
        ]);

        $osposConfig   = config(OSPOS::class);
        $previousLocal = $osposConfig->settings['number_locale'] ?? 'en_US';
        $osposConfig->settings['number_locale'] = 'de_DE';

        try {
            $itemModel = model(Item::class);
            $result    = $itemModel->change_cost_price($itemId, 5.0, 1234.56, 10.00);

            $this->assertTrue($result);
            $updatedItem = $itemModel->getInfo($itemId);
            $this->assertEqualsWithDelta(1234.56, (float) $updatedItem->cost_price, 0.001);
        } finally {
            $osposConfig->settings['number_locale'] = $previousLocal;
        }
    }

    public function testItemNumberExists_ExcludesOnlyValidNumericItemId(): void
    {
        $itemId = $this->createTestItem();
        $itemModel = model(Item::class);
        $itemInfo = $itemModel->getInfo($itemId);

        $this->assertTrue($itemModel->item_number_exists($itemInfo->item_number, ''));
        $this->assertFalse($itemModel->item_number_exists($itemInfo->item_number, (string) $itemId));
    }

    public function testGetSearchSuggestions_ReturnsDescriptionAndCustomAttributeMatches(): void
    {
        $uniqueToken = 'DescToken' . uniqid();
        $attrToken = 'AttrToken' . uniqid();

        $itemData = [
            'name'                  => 'SuggestionItem ' . uniqid(),
            'description'           => $uniqueToken,
            'category'              => 'Test Category',
            'cost_price'            => 1.00,
            'unit_price'            => 5.00,
            'reorder_level'         => 0,
            'item_number'           => 'SUG-' . uniqid(),
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
            'stock_type'            => HAS_STOCK,
            'item_type'             => ITEM,
            'deleted'               => 0,
        ];
        $itemModel = model(Item::class);
        $this->assertTrue($itemModel->save_value($itemData));
        $itemId = (int) $itemData['item_id'];

        $attributeModel = model(Attribute::class);
        $definitionData = [
            'definition_name'  => 'CustomSuggestDef_' . uniqid(),
            'definition_type'  => TEXT,
            'definition_flags' => Attribute::SHOW_IN_ITEMS,
            'deleted'          => 0,
        ];
        $this->assertTrue($attributeModel->saveDefinition($definitionData));
        $definitionId = (int) $definitionData['definition_id'];
        $this->assertGreaterThan(0, $attributeModel->saveAttributeValue($attrToken, $definitionId, $itemId, false, TEXT));

        $descSuggestions = $itemModel->get_search_suggestions($uniqueToken, ['is_deleted' => false, 'search_custom' => false], false);
        $descIds = array_map('intval', array_column($descSuggestions, 'value'));
        $this->assertContains($itemId, $descIds);

        $attrSuggestions = $itemModel->get_search_suggestions($attrToken, ['is_deleted' => false, 'search_custom' => true], false);
        $attrIds = array_map('intval', array_column($attrSuggestions, 'value'));
        $this->assertContains($itemId, $attrIds);

        $stockSuggestions = $itemModel->get_stock_search_suggestions($attrToken, ['is_deleted' => false, 'search_custom' => true], false);
        $stockIds = array_map('intval', array_column($stockSuggestions, 'value'));
        $this->assertContains($itemId, $stockIds);
    }
}
