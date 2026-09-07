<?php

namespace Tests\Models;

use App\Models\Item;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Regression coverage for GHSA-49mq-h2g4-grr9 (mass assignment in bulk edit).
 *
 * Item::update_multiple() writes through the Query Builder, which bypasses the
 * model's $allowedFields, so these assertions go straight to the items table.
 */
class ItemBulkUpdateTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * The schema is taken as it stands rather than migrated, so these tests run
     * against an existing test database without dropping it. Every row created
     * here is rolled back in tearDown().
     */
    protected $migrate   = false;
    protected $refresh   = false;
    protected $namespace = null;

    protected Item $item;

    /** @var list<int> */
    private array $createdItemIds = [];

    /** @var list<int> */
    private array $createdSupplierPersonIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = model(Item::class);
    }

    protected function tearDown(): void
    {
        if ($this->createdItemIds !== []) {
            $this->db->table('items')->whereIn('item_id', $this->createdItemIds)->delete();
            $this->createdItemIds = [];
        }

        if ($this->createdSupplierPersonIds !== []) {
            $this->db->table('suppliers')->whereIn('person_id', $this->createdSupplierPersonIds)->delete();
            $this->db->table('people')->whereIn('person_id', $this->createdSupplierPersonIds)->delete();
            $this->createdSupplierPersonIds = [];
        }

        parent::tearDown();
    }

    /**
     * Creates an item directly and returns its id.
     */
    private function createItem(array $overrides = []): int
    {
        $itemData = array_merge([
            'name'                  => 'Bulk Edit Fixture',
            'category'              => 'Fixtures',
            'cost_price'            => 10.00,
            'unit_price'            => 20.00,
            'reorder_level'         => 1,
            'description'           => 'fixture',
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
            'deleted'               => 0,
            'item_type'             => 0,
            'stock_type'            => 0,
            'hsn_code'              => ''
        ], $overrides);

        $this->item->save_value($itemData);

        $itemId = (int)$itemData['item_id'];
        $this->createdItemIds[] = $itemId;

        return $itemId;
    }

    /**
     * Creates a supplier (people + suppliers rows, to satisfy the items FK chain)
     * and returns its person_id.
     */
    private function createSupplier(): int
    {
        $this->db->table('people')->insert([
            'first_name'   => 'Bulk',
            'last_name'    => 'Supplier',
            'phone_number' => '555-0100',
            'email'        => 'bulk.supplier@example.test',
            'address_1'    => '1 Fixture Way',
            'address_2'    => '',
            'city'         => 'Testville',
            'state'        => 'TS',
            'zip'          => '00000',
            'country'      => 'US',
            'comments'     => 'fixture'
        ]);

        $personId = (int)$this->db->insertID();

        $this->db->table('suppliers')->insert([
            'person_id'    => $personId,
            'company_name' => 'Bulk Edit Supplies',
            'agency_name'  => '',
            'category'     => 0
        ]);

        $this->createdSupplierPersonIds[] = $personId;

        return $personId;
    }

    private function fetchItem(int $itemId): array
    {
        return (array)$this->db->table('items')
            ->where('item_id', $itemId)
            ->get()
            ->getRow();
    }

    public function testUpdateMultipleIgnoresNonWhitelistedColumns(): void
    {
        $itemId = $this->createItem();

        $result = $this->item->updateMultiple([
            'unit_price' => 25.00,   // legitimate
            'deleted'    => 1,       // injected — would hide the item
            'item_type'  => 2,       // injected — would change item semantics
            'stock_type' => 1        // injected
        ], (string)$itemId);

        $this->assertTrue($result, 'The legitimate field should still be applied');

        $row = $this->fetchItem($itemId);

        $this->assertEquals(25.00, (float)$row['unit_price'], 'Whitelisted field should be updated');
        $this->assertEquals(0, (int)$row['deleted'], 'Injected deleted must be ignored');
        $this->assertEquals(0, (int)$row['item_type'], 'Injected item_type must be ignored');
        $this->assertEquals(0, (int)$row['stock_type'], 'Injected stock_type must be ignored');
    }

    public function testUpdateMultipleWithOnlyNonWhitelistedColumnsIsNoOp(): void
    {
        $itemId = $this->createItem();

        $result = $this->item->updateMultiple(['deleted' => 1], (string)$itemId);

        $this->assertFalse($result, 'An update of only disallowed columns should not run');
        $this->assertEquals(0, (int)$this->fetchItem($itemId)['deleted'], 'Item must not be soft deleted');
    }

    public function testUpdateMultipleAppliesToEveryColonSeparatedId(): void
    {
        $first  = $this->createItem();
        $second = $this->createItem();

        $this->item->updateMultiple(['category' => 'Regrouped'], "$first:$second");

        $this->assertEquals('Regrouped', $this->fetchItem($first)['category']);
        $this->assertEquals('Regrouped', $this->fetchItem($second)['category']);
    }

    public function testUpdateMultipleClearsSupplierWhenPassedNull(): void
    {
        $supplierId = $this->createSupplier();
        $itemId     = $this->createItem(['supplier_id' => $supplierId]);

        $this->assertEquals(
            $supplierId,
            (int)$this->fetchItem($itemId)['supplier_id'],
            'precondition: the item must start with a supplier'
        );

        $filtered = Item::filterBulkEditFields(['supplier_id' => Item::CLEAR_SUPPLIER_OPTION]);
        $this->item->updateMultiple($filtered, (string)$itemId);

        $this->assertNull($this->fetchItem($itemId)['supplier_id'], 'supplier_id should be cleared');
    }

    public function testBulkEditWhitelistExcludesSensitiveColumns(): void
    {
        foreach (['deleted', 'item_type', 'stock_type', 'item_number', 'pic_filename', 'tax_category_id'] as $column) {
            $this->assertNotContains(
                $column,
                Item::ALLOWED_BULK_EDIT_FIELDS,
                "Sensitive column should not be bulk editable: $column"
            );
        }
    }

    public function testBulkEditWhitelistFieldsAreAllRealColumns(): void
    {
        foreach (Item::ALLOWED_BULK_EDIT_FIELDS as $field) {
            $this->assertContains(
                $field,
                $this->db->getFieldNames('items'),
                "Whitelisted bulk edit field must exist on the items table: $field"
            );
        }
    }

    // ========== Item::filterBulkEditFields() — the controller's input filter ==========

    public function testFilterBulkEditFieldsDropsInjectedColumns(): void
    {
        $filtered = Item::filterBulkEditFields([
            'item_ids'   => '1',
            'unit_price' => '25.00',
            'deleted'    => '1',
            'item_type'  => '2',
            'stock_type' => '1'
        ]);

        $this->assertSame(['unit_price' => 25.0], $filtered);
    }

    public function testFilterBulkEditFieldsOmitsAbsentSupplier(): void
    {
        $filtered = Item::filterBulkEditFields(['unit_price' => '30.00']);

        $this->assertArrayNotHasKey(
            'supplier_id',
            $filtered,
            'An absent supplier_id must not be written, or bulk edits would clear suppliers'
        );
    }

    public function testFilterBulkEditFieldsTreatsEmptySupplierAsDoNothing(): void
    {
        $filtered = Item::filterBulkEditFields(['supplier_id' => '', 'unit_price' => '30.00']);

        $this->assertArrayNotHasKey('supplier_id', $filtered, 'An empty supplier_id means do nothing');
    }

    public function testFilterBulkEditFieldsClearsSupplierWithSentinel(): void
    {
        $filtered = Item::filterBulkEditFields(['supplier_id' => Item::CLEAR_SUPPLIER_OPTION]);

        $this->assertArrayHasKey('supplier_id', $filtered);
        $this->assertNull($filtered['supplier_id'], 'The sentinel should become a NULL write');
    }

    public function testFilterBulkEditFieldsKeepsSelectedSupplier(): void
    {
        $filtered = Item::filterBulkEditFields(['supplier_id' => '7']);

        $this->assertSame(['supplier_id' => 7], $filtered);
    }

    public function testFilterBulkEditFieldsSkipsEmptyValuesForEveryField(): void
    {
        $input = array_fill_keys(Item::ALLOWED_BULK_EDIT_FIELDS, '');

        $this->assertSame([], Item::filterBulkEditFields($input), 'An untouched form should update nothing');
    }

    public function testFilterBulkEditFieldsAcceptsEveryWhitelistedField(): void
    {
        $input = [
            'name'                  => 'Renamed',
            'category'              => 'Regrouped',
            'supplier_id'           => '7',
            'cost_price'            => '1.50',
            'unit_price'            => '2.50',
            'reorder_level'         => '3',
            'description'           => 'described',
            'allow_alt_description' => '1',
            'is_serialized'         => '1'
        ];

        $this->assertSame(
            Item::ALLOWED_BULK_EDIT_FIELDS,
            array_keys(Item::filterBulkEditFields($input)),
            'Every whitelisted field should still be editable'
        );
    }

    public function testFilterBulkEditFieldsDropsArrayValues(): void
    {
        $input = array_fill_keys(Item::ALLOWED_BULK_EDIT_FIELDS, ['x']);

        $this->assertSame([], Item::filterBulkEditFields($input), 'Array values must never reach the update');
    }

    public function testFilterBulkEditFieldsDropsNonNumericPricesAndQuantities(): void
    {
        $filtered = Item::filterBulkEditFields([
            'cost_price'    => 'abc',
            'unit_price'    => 'DROP TABLE',
            'reorder_level' => 'lots',
            'name'          => 'Renamed'
        ]);

        $this->assertSame(['name' => 'Renamed'], $filtered, 'Unparseable numbers must be omitted');
    }

    public function testFilterBulkEditFieldsDropsInvalidBooleanValues(): void
    {
        $filtered = Item::filterBulkEditFields([
            'allow_alt_description' => '2',
            'is_serialized'         => 'yes'
        ]);

        $this->assertSame([], $filtered, 'Booleans other than 0/1 must be omitted');
    }

    public function testFilterBulkEditFieldsDropsNonNumericSupplier(): void
    {
        $filtered = Item::filterBulkEditFields(['supplier_id' => '7; DROP TABLE items']);

        $this->assertSame([], $filtered, 'A non-numeric supplier_id other than the sentinel must be omitted');
    }

    public function testFilterBulkEditFieldsAcceptsZeroValues(): void
    {
        $filtered = Item::filterBulkEditFields(['allow_alt_description' => '0', 'is_serialized' => '0']);

        $this->assertSame(['allow_alt_description' => '0', 'is_serialized' => '0'], $filtered);
    }

    public function testFilterBulkEditFieldsOutputIsSafeForUpdateMultiple(): void
    {
        $itemId = $this->createItem();

        $filtered = Item::filterBulkEditFields([
            'item_ids' => (string)$itemId,
            'deleted'  => '1',
            'name'     => 'Renamed'
        ]);

        $this->item->updateMultiple($filtered, (string)$itemId);

        $row = $this->fetchItem($itemId);
        $this->assertEquals('Renamed', $row['name']);
        $this->assertEquals(0, (int)$row['deleted'], 'Injected deleted must never reach the table');
    }

    public function testClearSupplierSentinelCannotCollideWithAPersonId(): void
    {
        $this->assertFalse(
            is_numeric(Item::CLEAR_SUPPLIER_OPTION),
            'The clear-supplier sentinel must be non-numeric so it cannot match a person_id'
        );
    }
}
