<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\Item;
use Config\Database;
use Config\OSPOS;
use Tests\Support\ItemSearchFixtureTrait;

class ItemSearchTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use ItemSearchFixtureTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seed = '';
    protected $seedOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    protected $item;

    public static function setUpBeforeClass(): void
    {
        $seeder = Database::seeder('tests');
        $seeder->call('TestDatabaseBootstrapSeeder');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = model(Item::class);

        // Force a fresh read of app_config: the OSPOS settings cache can predate the
        // seeded config rows, which otherwise leaves keys like 'dateformat' missing.
        // OSPOS::set_settings() decides whether app_config is readable via a cached
        // tableExists() check on the shared connection; that cache can go stale mid-suite
        // and silently fall back to defaults (no 'dateformat' key). Set the setting this
        // test needs directly rather than depending on that cache being fresh.
        config(OSPOS::class)->settings['dateformat'] = 'm/d/Y';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testSearchReturnsMatchingItemByName(): void
    {
        $uniqueName = 'Findable Widget ' . uniqid();
        $this->createSearchableItem([
            'name'       => $uniqueName,
            'cost_price' => 12.50,
            'unit_price' => 24.99,
        ]);

        $results = $this->item->search($uniqueName, $this->defaultSearchFilters())->getResult();

        $this->assertCount(1, $results);
        $this->assertEquals($uniqueName, $results[0]->name);
        $this->assertEquals(12.50, (float) $results[0]->cost_price);
        $this->assertEquals(24.99, (float) $results[0]->unit_price);
    }

    public function testSearchExcludesNonMatchingItems(): void
    {
        $matchingName = 'Matching Gadget ' . uniqid();
        $this->createSearchableItem(['name' => $matchingName]);
        $this->createSearchableItem(['name' => 'Unrelated Thing ' . uniqid()]);

        $results = $this->item->search($matchingName, $this->defaultSearchFilters())->getResult();

        $this->assertCount(1, $results);
        $this->assertEquals($matchingName, $results[0]->name);
    }

    public function testGetFoundRowsMatchesActualCount(): void
    {
        $sharedCategory = 'Shared Category ' . uniqid();
        $this->createSearchableItem(['category' => $sharedCategory]);
        $this->createSearchableItem(['category' => $sharedCategory]);
        $this->createSearchableItem(['category' => $sharedCategory]);

        $filters = $this->defaultSearchFilters();

        $foundRows = $this->item->get_found_rows($sharedCategory, $filters);
        $actualRows = $this->item->search($sharedCategory, $filters)->getResultArray();

        $this->assertEquals(3, $foundRows);
        $this->assertCount($foundRows, $actualRows);
    }

    public function testSearchPaginationRowsAndLimitFrom(): void
    {
        $sharedCategory = 'Paginated Category ' . uniqid();
        $this->createSearchableItem(['name' => 'A Item ' . uniqid(), 'category' => $sharedCategory]);
        $this->createSearchableItem(['name' => 'B Item ' . uniqid(), 'category' => $sharedCategory]);
        $this->createSearchableItem(['name' => 'C Item ' . uniqid(), 'category' => $sharedCategory]);

        $results = $this->item->search(
            $sharedCategory,
            $this->defaultSearchFilters(),
            1,
            1,
            'items.name',
            'asc'
        )->getResult();

        $this->assertCount(1, $results);
        $this->assertStringStartsWith('B Item', $results[0]->name);
    }

    public function testSearchOrderPreservedAfterPhaseBJoin(): void
    {
        $sharedCategory = 'Ordered Category ' . uniqid();
        $this->createSearchableItem(['name' => 'Alpha Item ' . uniqid(), 'category' => $sharedCategory]);
        $this->createSearchableItem(['name' => 'Beta Item ' . uniqid(), 'category' => $sharedCategory]);
        $this->createSearchableItem(['name' => 'Gamma Item ' . uniqid(), 'category' => $sharedCategory]);

        $results = $this->item->search(
            $sharedCategory,
            $this->defaultSearchFilters(),
            0,
            0,
            'items.name',
            'desc'
        )->getResult();

        $names = array_map(static fn ($item) => $item->name, $results);
        $sorted = $names;
        rsort($sorted);

        $this->assertSame($sorted, $names);
    }

    public function testSearchNoMatchesReturnsEmptyResultNotError(): void
    {
        $results = $this->item->search('no-such-item-' . uniqid(), $this->defaultSearchFilters())->getResult();

        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    public function testSearchExcludesDeletedItemsByDefault(): void
    {
        $deletedName = 'Deleted Item ' . uniqid();
        $this->createSearchableItem(['name' => $deletedName, 'deleted' => 1]);

        $results = $this->item->search($deletedName, $this->defaultSearchFilters(['is_deleted' => false]))->getResult();

        $this->assertCount(0, $results);
    }

    public function testSearchIncludesDeletedWhenFilterSet(): void
    {
        $deletedName = 'Deleted Item ' . uniqid();
        $this->createSearchableItem(['name' => $deletedName, 'deleted' => 1]);

        $results = $this->item->search($deletedName, $this->defaultSearchFilters(['is_deleted' => true]))->getResult();

        $this->assertCount(1, $results);
        $this->assertEquals($deletedName, $results[0]->name);
    }

    public function testSearchStockLocationFilter(): void
    {
        $locationAName = 'Location A Item ' . uniqid();
        $itemId = $this->createSearchableItem(['name' => $locationAName]);
        $this->addItemQuantity($itemId, 1, 10);

        $resultsAtLocation1 = $this->item->search(
            $locationAName,
            $this->defaultSearchFilters(['stock_location_id' => 1])
        )->getResult();

        $resultsAtLocation2 = $this->item->search(
            $locationAName,
            $this->defaultSearchFilters(['stock_location_id' => 2])
        )->getResult();

        $this->assertCount(1, $resultsAtLocation1);
        $this->assertCount(0, $resultsAtLocation2);
    }

    public function testSearchTemporaryFilter(): void
    {
        $tempName = 'Temp Item ' . uniqid();
        $this->createSearchableItem(['name' => $tempName, 'item_type' => ITEM_TEMP]);

        $resultsWhenTemporary = $this->item->search(
            $tempName,
            $this->defaultSearchFilters(['temporary' => true])
        )->getResult();

        $resultsWhenNotTemporary = $this->item->search(
            $tempName,
            $this->defaultSearchFilters(['temporary' => false])
        )->getResult();

        $this->assertCount(1, $resultsWhenTemporary);
        $this->assertCount(0, $resultsWhenNotTemporary);
    }

    public function testSearchSortByQuantitySumsAcrossLocationsWithoutMultiplication(): void
    {
        $itemId = $this->createSearchableItem(['name' => 'Multi Location Item ' . uniqid()]);

        // createSearchableItem already inserts one inventory row; add two more
        // inventory transactions so a naive SUM-after-join (which multiplies
        // item_quantities rows by the inventory join) would triple-count.
        $this->addInventoryRecord($itemId, 1);
        $this->addInventoryRecord($itemId, 1);

        $this->addItemQuantity($itemId, 1, 10);
        $this->addItemQuantity($itemId, 2, 5);

        $results = $this->item->search(
            '',
            $this->defaultSearchFilters(),
            0,
            0,
            'quantity',
            'desc'
        )->getResult();

        $match = array_values(array_filter($results, static fn ($r) => (int) $r->item_id === $itemId));

        $this->assertCount(1, $match);
        $this->assertEquals(15, (float) $match[0]->quantity);
    }

    public function testSearchByNamedAttributeSyntax(): void
    {
        $colorDefinitionId = $this->createAttributeDefinition('Color ' . uniqid());
        $sizeDefinitionId = $this->createAttributeDefinition('Size ' . uniqid());
        $blueValue = 'Blue' . uniqid();
        $redValue = 'Red' . uniqid();
        $largeValue = 'Large' . uniqid();

        $matchingId = $this->createSearchableItem(['name' => 'Named Attr Match ' . uniqid()]);
        $this->linkAttributeValue($matchingId, $colorDefinitionId, $blueValue);
        $this->linkAttributeValue($matchingId, $sizeDefinitionId, $largeValue);

        $wrongColorId = $this->createSearchableItem(['name' => 'Named Attr WrongColor ' . uniqid()]);
        $this->linkAttributeValue($wrongColorId, $colorDefinitionId, $redValue);
        $this->linkAttributeValue($wrongColorId, $sizeDefinitionId, $largeValue);

        $filters = $this->defaultSearchFilters([
            'search_custom'  => true,
            'definition_ids' => [$colorDefinitionId, $sizeDefinitionId],
        ]);

        $colorAttrName = $this->getDefinitionName($colorDefinitionId);
        $sizeAttrName = $this->getDefinitionName($sizeDefinitionId);

        $results = $this->item->search("{$colorAttrName}:{$blueValue} {$sizeAttrName}:{$largeValue}", $filters)->getResult();
        $ids = array_map(static fn ($item) => (int) $item->item_id, $results);

        $this->assertContains($matchingId, $ids);
        $this->assertNotContains($wrongColorId, $ids);
    }

    public function testSearchByNamedAttributeCombinedWithFreeText(): void
    {
        // When search_custom is on, the free-text remainder matches attribute values
        // (not item name/category/etc) - see the customAttributeSearch branch of search().
        // An item can only have one attribute_links row per definition_id (attribute_links_uq3),
        // so the name:value attribute and the free-text-matched attribute must be different
        // definitions.
        $colorDefinitionId = $this->createAttributeDefinition('Color ' . uniqid());
        $materialDefinitionId = $this->createAttributeDefinition('Material ' . uniqid());
        $greenValue = 'Green' . uniqid();
        $freeTextValue = 'Cotton' . uniqid();

        $matchingId = $this->createSearchableItem(['name' => 'Combo Search Widget ' . uniqid()]);
        $this->linkAttributeValue($matchingId, $colorDefinitionId, $greenValue);
        $this->linkAttributeValue($matchingId, $materialDefinitionId, $freeTextValue);

        $wrongFreeTextId = $this->createSearchableItem(['name' => 'Combo Search Other ' . uniqid()]);
        $this->linkAttributeValue($wrongFreeTextId, $colorDefinitionId, $greenValue);

        $filters = $this->defaultSearchFilters([
            'search_custom'  => true,
            'definition_ids' => [$colorDefinitionId, $materialDefinitionId],
        ]);

        $colorAttrName = $this->getDefinitionName($colorDefinitionId);

        $results = $this->item->search("{$colorAttrName}:{$greenValue} {$freeTextValue}", $filters)->getResult();
        $ids = array_map(static fn ($item) => (int) $item->item_id, $results);

        $this->assertContains($matchingId, $ids);
        $this->assertNotContains($wrongFreeTextId, $ids);
    }

    public function testPlainFreeTextSearchUnaffectedByAttributeParsing(): void
    {
        // search_custom is off here, so this exercises the plain by-name search path -
        // parseAttributeSearch() is never even called in this branch of search().
        $uniqueName = 'Plain Search Widget ' . uniqid();
        $this->createSearchableItem(['name' => $uniqueName]);

        $results = $this->item->search($uniqueName, $this->defaultSearchFilters())->getResult();

        $this->assertCount(1, $results);
        $this->assertEquals($uniqueName, $results[0]->name);
    }

    public function testSearchCustomWithoutNamedAttributeSyntaxMatchesAttributeValueAsBefore(): void
    {
        // No "name:value" syntax present, so parseAttributeSearch() finds no attributes and
        // $freeTextSearch falls back to the raw $search - this is the pre-Feature-C behavior
        // for search_custom, unaffected by the new named-attribute join.
        $definitionId = $this->createAttributeDefinition('Material ' . uniqid());
        $searchValue = 'Cotton' . uniqid();

        $matchingId = $this->createSearchableItem(['name' => 'Search Custom Widget ' . uniqid()]);
        $this->linkAttributeValue($matchingId, $definitionId, $searchValue);

        $filters = $this->defaultSearchFilters([
            'search_custom'  => true,
            'definition_ids' => [$definitionId],
        ]);

        $results = $this->item->search($searchValue, $filters)->getResult();
        $ids = array_map(static fn ($item) => (int) $item->item_id, $results);

        $this->assertContains($matchingId, $ids);
    }

    private function getDefinitionName(int $definitionId): string
    {
        return db_connect()->table('attribute_definitions')
            ->select('definition_name')
            ->where('definition_id', $definitionId)
            ->get()
            ->getRow()
            ->definition_name;
    }
}
