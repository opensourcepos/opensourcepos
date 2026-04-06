<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\Item;
use App\Models\Item_quantity;
use App\Models\Inventory;
use App\Models\Item_taxes;
use App\Models\Attribute;
use App\Models\Stock_location;
use App\Models\Supplier;
use Config\Database;

class ItemsCsvImportTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seed = '';
    protected $seedOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    protected $item;
    protected $item_quantity;
    protected $inventory;
    protected $item_taxes;
    protected $attribute;
    protected $stock_location;
    protected $supplier;

    public static function setUpBeforeClass(): void
    {
        $seeder = Database::seeder('tests');
        $seeder->call('TestDatabaseBootstrapSeeder');
    }


    protected function setUp(): void
    {
        parent::setUp();

        helper('importfile');
        helper('attribute');

        $this->item = model(Item::class);
        $this->item_quantity = model(Item_quantity::class);
        $this->inventory = model(Inventory::class);
        $this->item_taxes = model(Item_taxes::class);
        $this->attribute = model(Attribute::class);
        $this->stock_location = model(Stock_location::class);
        $this->supplier = model(Supplier::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGenerateCsvHeaderBasic(): void
    {
        $stockLocations = ['Warehouse'];
        $attributes = [];

        $csv = generate_import_items_csv($stockLocations, $attributes);

        $this->assertStringContainsString('Id,Barcode,"Item Name"', $csv);
        $this->assertStringContainsString('Category,"Supplier ID"', $csv);
        $this->assertStringContainsString('"Cost Price","Unit Price"', $csv);
        $this->assertStringContainsString('"Tax 1 Name","Tax 1 Percent"', $csv);
        $this->assertStringContainsString('"Tax 2 Name","Tax 2 Percent"', $csv);
        $this->assertStringContainsString('"Reorder Level"', $csv);
        $this->assertStringContainsString('Description,"Allow Alt Description"', $csv);
        $this->assertStringContainsString('"Item has Serial Number"', $csv);
        $this->assertStringContainsString('Image,HSN', $csv);
        $this->assertStringContainsString('"location_Warehouse"', $csv);
        $this->assertStringContainsString("\xEF\xBB\xBF", $csv);
    }

    public function testGenerateCsvHeaderMultipleLocations(): void
    {
        $stockLocations = ['Warehouse', 'Store', 'Backroom'];
        $attributes = [];

        $csv = generate_import_items_csv($stockLocations, $attributes);

        $this->assertStringContainsString('"location_Warehouse"', $csv);
        $this->assertStringContainsString('"location_Store"', $csv);
        $this->assertStringContainsString('"location_Backroom"', $csv);
    }

    public function testGenerateCsvHeaderWithAttributes(): void
    {
        $stockLocations = ['Warehouse'];
        $attributes = ['Color', 'Size', 'Weight'];

        $csv = generate_import_items_csv($stockLocations, $attributes);

        $this->assertStringContainsString('"attribute_Color"', $csv);
        $this->assertStringContainsString('"attribute_Size"', $csv);
        $this->assertStringContainsString('"attribute_Weight"', $csv);
    }

    public function testGenerateStockLocationHeaders(): void
    {
        $locations = ['Warehouse', 'Store'];

        $headers = generate_stock_location_headers($locations);

        $this->assertEquals(',"location_Warehouse","location_Store"', $headers);
    }

    public function testGenerateAttributeHeaders(): void
    {
        $attributes = ['Color', 'Size'];

        $headers = generate_attribute_headers($attributes);

        $this->assertEquals(',"attribute_Color","attribute_Size"', $headers);
    }

    public function testGenerateAttributeHeadersRemovesNegativeOneIndex(): void
    {
        $attributes = [-1 => 'None', 'Color' => 'Color'];
        unset($attributes[-1]);

        $headers = generate_attribute_headers($attributes);

        $this->assertStringContainsString('"attribute_Color"', $headers);
    }

    public function testGetCsvFileBasic(): void
    {
        $csvContent = "Id,Barcode,\"Item Name\",Category,\"Supplier ID\",\"Cost Price\",\"Unit Price\",\"Tax 1 Name\",\"Tax 1 Percent\",\"Tax 2 Name\",\"Tax 2 Percent\",\"Reorder Level\",Description,\"Allow Alt Description\",\"Item has Serial Number\",Image,HSN\n";
        $csvContent .= ",ITEM001,Test Item,Electronics,1,10.00,15.00,,,,,5,Test Description,0,0,,HSN001\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, $csvContent);

        $rows = get_csv_file($tempFile);

        $this->assertCount(1, $rows);
        $this->assertEquals('', $rows[0]['Id']);
        $this->assertEquals('ITEM001', $rows[0]['Barcode']);
        $this->assertEquals('Test Item', $rows[0]['Item Name']);
        $this->assertEquals('Electronics', $rows[0]['Category']);

        unlink($tempFile);
    }

    public function testGetCsvFileWithBom(): void
    {
        $bom = pack('CCC', 0xef, 0xbb, 0xbf);
        $csvContent = $bom . "Id,\"Item Name\",Category\n";
        $csvContent .= "1,Test Item,Electronics\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_bom_');
        file_put_contents($tempFile, $csvContent);

        $rows = get_csv_file($tempFile);

        $this->assertCount(1, $rows);
        $this->assertEquals('1', $rows[0]['Id']);
        $this->assertEquals('Test Item', $rows[0]['Item Name']);

        unlink($tempFile);
    }

    public function testGetCsvFileMultipleRows(): void
    {
        $csvContent = "Id,\"Item Name\",Category\n";
        $csvContent .= "1,Item One,Cat A\n";
        $csvContent .= "2,Item Two,Cat B\n";
        $csvContent .= "3,Item Three,Cat C\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_multi_');
        file_put_contents($tempFile, $csvContent);

        $rows = get_csv_file($tempFile);

        $this->assertCount(3, $rows);
        $this->assertEquals('Item One', $rows[0]['Item Name']);
        $this->assertEquals('Item Two', $rows[1]['Item Name']);
        $this->assertEquals('Item Three', $rows[2]['Item Name']);

        unlink($tempFile);
    }

    public function testBomExists(): void
    {
        $bom = pack('CCC', 0xef, 0xbb, 0xbf);
        $contentWithBom = $bom . "test content";

        $tempFile = tempnam(sys_get_temp_dir(), 'bom_test_');
        file_put_contents($tempFile, $contentWithBom);

        $handle = fopen($tempFile, 'r');
        $result = bom_exists($handle);
        fclose($handle);

        $this->assertTrue($result);
        unlink($tempFile);
    }

    public function testBomNotExists(): void
    {
        $contentWithoutBom = "test content without BOM";

        $tempFile = tempnam(sys_get_temp_dir(), 'no_bom_test_');
        file_put_contents($tempFile, $contentWithoutBom);

        $handle = fopen($tempFile, 'r');
        $result = bom_exists($handle);
        fclose($handle);

        $this->assertFalse($result);
        unlink($tempFile);
    }

    public function testImportItemBasicFields(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'CSV Imported Item',
            'description' => 'Description from CSV',
            'category' => 'Electronics',
            'cost_price' => 10.50,
            'unit_price' => 25.99,
            'reorder_level' => 5,
            'supplier_id' => null,
            'item_number' => 'CSV-ITEM-001',
            'allow_alt_description' => 0,
            'is_serialized' => 0,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $row = $this->db->table('items')
            ->where('item_number', $itemData['item_number'])
            ->get()
            ->getRow();

        $this->assertNotNull($row);
        $this->assertIsInt((int) $row->item_id);
        $this->assertGreaterThan(0, (int) $row->item_id);

        $savedItem = $this->item->get_info((int) $row->item_id);
        $this->assertEquals('CSV Imported Item', $savedItem->name);
        $this->assertEquals('Description from CSV', $savedItem->description);
        $this->assertEquals('Electronics', $savedItem->category);
        $this->assertEquals(10.50, (float) $savedItem->cost_price);
        $this->assertEquals(25.99, (float) $savedItem->unit_price);
    }

    public function testImportItemWithQuantity(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Item With Quantity',
            'category' => 'Test Category',
            'cost_price' => 5.00,
            'unit_price' => 10.00,
            'reorder_level' => 2,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $locationId = 1;
        $quantity = 100;

        $itemQuantityData = [
            'item_id' => $itemData['item_id'],
            'location_id' => $locationId,
            'quantity' => $quantity
        ];

        $result = $this->item_quantity->save_value($itemQuantityData, $itemData['item_id'], $locationId);
        $this->assertTrue($result);

        $savedQuantity = $this->item_quantity->get_item_quantity($itemData['item_id'], $locationId);
        $this->assertEquals($quantity, $savedQuantity->quantity);
    }

    public function testImportItemCreatesInventoryRecord(): void
    {
        $itemData = [
            'item_id' => null,
            'item_number' => '1234567890',
            'name' => 'Item With Inventory',
            'category' => 'Test',
            'cost_price' => 5.00,
            'unit_price' => 10.00,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $inventoryData = [
            'trans_inventory' => 50,
            'trans_items' => $itemData['item_id'],
            'trans_location' => 1,
            'trans_comment' => 'CSV Import',
            'trans_user' => 1
        ];

        $transId = $this->inventory->insert($inventoryData);

        $this->assertIsInt($transId);
        $this->assertGreaterThan(0, $transId);

        $inventoryRecords = $this->inventory->get_inventory_data_for_item($itemData['item_id'], 1);
        $this->assertGreaterThanOrEqual(1, $inventoryRecords->getNumRows());
    }

    public function testImportItemWithTaxes(): void
    {
        $itemData = [
            'item_id' => null,
            'item_number' => '1234567890',
            'name' => 'Taxable Item',
            'category' => 'Test',
            'cost_price' => 100.00,
            'unit_price' => 150.00,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $taxesData = [
            ['name' => 'VAT', 'percent' => 20],
            ['name' => 'GST', 'percent' => 10]
        ];

        $result = $this->item_taxes->save_value($taxesData, $itemData['item_id']);
        $this->assertTrue($result);

        $savedTaxes = $this->item_taxes->get_info($itemData['item_id']);

        $taxNames = array_column($savedTaxes, 'name');
        $this->assertContains('VAT', $taxNames);
        $this->assertContains('GST', $taxNames);
    }

    public function testImportMultipleItemsFromSimulatedCsv(): void
    {
        $csvData = [
            [
                'Id' => '',
                'Barcode' => 'ITEM-A',
                'Item Name' => 'First Item',
                'Category' => 'Category A',
                'Supplier ID' => '',
                'Cost Price' => '10.00',
                'Unit Price' => '20.00',
                'Tax 1 Name' => '',
                'Tax 1 Percent' => '',
                'Tax 2 Name' => '',
                'Tax 2 Percent' => '',
                'Reorder Level' => '5',
                'Description' => 'First item description',
                'Allow Alt Description' => '0',
                'Item has Serial Number' => '0',
                'Image' => '',
                'HSN' => '',
                'location_Warehouse' => '100'
            ],
            [
                'Id' => '',
                'Barcode' => 'ITEM-B',
                'Item Name' => 'Second Item',
                'Category' => 'Category B',
                'Supplier ID' => '',
                'Cost Price' => '15.00',
                'Unit Price' => '30.00',
                'Tax 1 Name' => '',
                'Tax 1 Percent' => '',
                'Tax 2 Name' => '',
                'Tax 2 Percent' => '',
                'Reorder Level' => '10',
                'Description' => 'Second item description',
                'Allow Alt Description' => '0',
                'Item has Serial Number' => '0',
                'Image' => '',
                'HSN' => '',
                'location_Warehouse' => '50'
            ]
        ];

        foreach ($csvData as $row) {
            $itemData = [
                'item_id' => (int)$row['Id'] ?: null,
                'name' => $row['Item Name'],
                'description' => $row['Description'],
                'category' => $row['Category'],
                'cost_price' => (float)$row['Cost Price'],
                'unit_price' => (float)$row['Unit Price'],
                'reorder_level' => (int)$row['Reorder Level'],
                'item_number' => $row['Barcode'] ?: null,
                'allow_alt_description' => empty($row['Allow Alt Description']) ? '0' : '1',
                'is_serialized' => empty($row['Item has Serial Number']) ? '0' : '1',
                'deleted' => false
            ];

            $this->assertTrue($this->item->save_value($itemData));
        }

        $item1 = $this->item->get_info_by_id_or_number('ITEM-A');
        $this->assertEquals('First Item', $item1->name);
        $this->assertEquals(10.00, (float) $item1->cost_price);

        $item2 = $this->item->get_info_by_id_or_number('ITEM-B');
        $this->assertEquals('Second Item', $item2->name);
        $this->assertEquals(15.00, (float) $item2->cost_price);
    }

    public function testImportUpdateExistingItem(): void
    {
        $originalData = [
            'item_id' => null,
            'item_number' => '1234567890',
            'name' => 'Original Name',
            'category' => 'Original Category',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($originalData));

        $updatedData = [
            'item_id' => $originalData['item_id'],
            'name' => 'Updated Name',
            'category' => 'Updated Category',
            'cost_price' => 15.00,
            'unit_price' => 30.00,
            'description' => 'New description',
            'reorder_level' => 10,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($updatedData, $updatedData['item_id']));

        $updatedItem = $this->item->get_info($updatedData['item_id']);
        $this->assertEquals('Updated Name', $updatedItem->name);
        $this->assertEquals('Updated Category', $updatedItem->category);
        $this->assertEquals(15.00, (float)$updatedItem->cost_price);
        $this->assertEquals(30.00, (float)$updatedItem->unit_price);
    }

    public function testImportDeleteAttributeFromExistingItem(): void
    {
        $originalData = [
            'item_id' => null,
            'item_number' => '1234567890',
            'name' => 'Original Name',
            'category' => 'Original Category',
            'cost_price' => '10.00',
            'unit_price' => '20.00',
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($originalData));

        $definitionData = [
            'definition_name' => 'Color',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];

        $this->assertTrue($this->attribute->saveDefinition($definitionData));

        //Assign attribute to item
        $attributeValue = 'Red';
        $attributeId = $this->attribute->saveAttributeValue(//need to properly assign an attribute link before this is going to work
            $attributeValue,
            $definitionData['definition_id'],
            $originalData['item_id'],
            false,
            TEXT
        );
        $this->assertGreaterThan(0, $attributeId, 'Attribute fixture must exist before testing _DELETE_');

        //Mock CSV import
        $updatedItemData = ['item_id' => $originalData['item_id']];
        $attributeDefinitionData = [$definitionData];
        $updatedValues = ['Color' => '_DELETE_'];

        $saveSuccess = $this->attribute->saveCSVRowAttributeData($updatedValues, $updatedItemData, $attributeDefinitionData);
        $this->assertTrue($saveSuccess);

        $resultingAttribute = $this->attribute->getAttributeValue($originalData['item_id'], $definitionData['definition_id']);
        $this->assertEquals(NEW_ENTRY, $resultingAttribute->attribute_id);
    }

    public function testImportItemWithAttributeText(): void
    {
        $itemData = [
            'item_id' => null,
            'item_number' => '1234567890',
            'name' => 'Item With Attribute',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $definitionData = [
            'definition_name' => 'Color',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];

        $this->assertTrue($this->attribute->saveDefinition($definitionData));
        $this->assertNotEmpty($definitionData['definition_id']);
        $definitionId = $definitionData['definition_id'];

        $this->assertNotNull($definitionId);


        $attributeValue = 'Red';
        $attributeId = $this->attribute->saveAttributeValue(
            $attributeValue,
            $definitionId,
            $itemData['item_id'],
            false,
            TEXT
        );

        $this->assertNotFalse($attributeId);

        $savedValue = $this->attribute->getAttributeValue($itemData['item_id'], $definitionId);
        $this->assertEquals('Red', $savedValue->attribute_value);
    }

    public function testImportItemWithAttributeDropdown(): void
    {
        $itemData = [
            'item_id' => null,
            'item_number' => '1234567890',
            'name' => 'Item With Dropdown',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        // Mock Attribute DROPDOWN
        $definitionData = [
            'definition_name' => 'Size',
            'definition_type' => DROPDOWN,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $this->assertTrue($this->attribute->saveDefinition($definitionData));
        $definitionId = $definitionData['definition_id'];

        // Mock Attribute DROPDOWN Values
        $dropdownValues = ['Small', 'Medium', 'Large'];
        foreach ($dropdownValues as $i => $value) {
            $this->attribute->saveAttributeValue(
                $value,
                $definitionId,
                false,
                false,
                DROPDOWN
            );
        }

        // Save dropdown attribute value for item
        $attributeValue = 'Medium';
        $attributeId = $this->attribute->saveAttributeValue(
            $attributeValue,
            $definitionId,
            $itemData['item_id'],
            false,
            DROPDOWN
        );

        $this->assertNotFalse($attributeId);

        $savedValue = $this->attribute->getAttributeValue($itemData['item_id'], $definitionId);
        $this->assertEquals('Medium', $savedValue->attribute_value);
    }

    public function testImportItemQuantityZero(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Item Zero Quantity',
            'category' => 'Test',
            'cost_price' => 5.00,
            'unit_price' => 10.00,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $locationId = 1;

        $itemQuantityData = [
            'item_id' => $itemData['item_id'],
            'location_id' => $locationId,
            'quantity' => 0
        ];

        $result = $this->item_quantity->save_value($itemQuantityData, $itemData['item_id'], $locationId);
        $this->assertTrue($result);

        $savedQuantity = $this->item_quantity->get_item_quantity($itemData['item_id'], $locationId);
        $this->assertEquals(0, (int)$savedQuantity->quantity);
    }

    public function testImportItemWithNegativeReorderLevel(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Item Negative Reorder',
            'category' => 'Test',
            'cost_price' => 5.00,
            'unit_price' => 10.00,
            'reorder_level' => -1,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $savedItem = $this->item->get_info($itemData['item_id']);
        $this->assertEquals(-1, (int)$savedItem->reorder_level);
    }

    public function testImportItemPriceRoundingBoundaries(): void
    {
        $cases = [
            [
                'cost_price' => 10.004,
                'unit_price' => 25.004,
                'expected_cost_price' => '10.00',
                'expected_unit_price' => '25.00',
            ],
            [
                'cost_price' => 10.005,
                'unit_price' => 25.005,
                'expected_cost_price' => '10.01',
                'expected_unit_price' => '25.01',
            ],
            [
                'cost_price' => 10.006,
                'unit_price' => 25.006,
                'expected_cost_price' => '10.01',
                'expected_unit_price' => '25.01',
            ],
        ];

        foreach ($cases as $case) {
            $itemData = [
                'item_id' => null,
                'name' => 'Rounding Boundary Item ' . $case['cost_price'],
                'category' => 'Test',
                'cost_price' => $case['cost_price'],
                'unit_price' => $case['unit_price'],
                'deleted' => 0
            ];

            $this->assertTrue($this->item->save_value($itemData));

            $savedItem = $this->item->get_info($itemData['item_id']);

            $this->assertSame(
                $case['expected_cost_price'],
                number_format((float) $savedItem->cost_price, 2, '.', ''),
                'Cost price should be rounded correctly at the 3rd decimal boundary'
            );

            $this->assertSame(
                $case['expected_unit_price'],
                number_format((float) $savedItem->unit_price, 2, '.', ''),
                'Unit price should be rounded correctly at the 3rd decimal boundary'
            );
        }
    }

    public function testImportItemWithHsnCode(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Item With HSN',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'hsn_code' => '8471',
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $savedItem = $this->item->get_info($itemData['item_id']);
        $this->assertEquals('8471', $savedItem->hsn_code);
    }

    public function testImportItemQuantityMultipleLocations(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Item Multi Location',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $locations = [
            'Warehouse' => 100,
            'Store' => 50,
            'Backroom' => 25,
        ];

        $locationIds = [];

        foreach ($locations as $locationName => $quantity) {
            $locationData = [
                'location_name' => $locationName,
                'deleted' => 0
            ];

            $this->assertTrue($this->stock_location->save_value($locationData, NEW_ENTRY));
            $this->assertNotEmpty($locationData['location_id']);

            $locationIds[$locationName] = $locationData['location_id'];

            $result = $this->item_quantity->save_value(
                [
                    'item_id' => $itemData['item_id'],
                    'location_id' => $locationData['location_id'],
                    'quantity' => $quantity
                ],
                $itemData['item_id'],
                $locationData['location_id']
            );

            $this->assertTrue($result);
        }

        $warehouseQuantity = $this->item_quantity->get_item_quantity($itemData['item_id'], $locationIds['Warehouse']);
        $this->assertEquals(100, (int) $warehouseQuantity->quantity);

        $storeQuantity = $this->item_quantity->get_item_quantity($itemData['item_id'], $locationIds['Store']);
        $this->assertEquals(50, (int) $storeQuantity->quantity);

        $backroomQuantity = $this->item_quantity->get_item_quantity($itemData['item_id'], $locationIds['Backroom']);
        $this->assertEquals(25, (int) $backroomQuantity->quantity);
    }

    public function testCsvImportQuantityValidationNumeric(): void
    {
        $csvData = [
            'Id' => '',
            'Barcode' => 'VALID-ITEM',
            'Item Name' => 'Valid Item',
            'Category' => 'Test',
            'Cost Price' => '10.00',
            'Unit Price' => '20.00',
            'location_Warehouse' => '100'
        ];

        $this->assertTrue(is_numeric($csvData['location_Warehouse']));
        $this->assertTrue(is_numeric($csvData['Cost Price']));
        $this->assertTrue(is_numeric($csvData['Unit Price']));
    }

    public function testCsvImportEmptyBarcodeAllowed(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Item Without Barcode',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'item_number' => null,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $this->assertIsInt($itemData['item_id']);
        $this->assertGreaterThan(0, $itemData['item_id']);

        $savedItem = $this->item->get_info($itemData['item_id']);
        $this->assertEquals('Item Without Barcode', $savedItem->name);
    }

    public function testCsvImportItemExistsCheck(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Existing Item',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $exists = $this->item->exists($itemData['item_id']);
        $this->assertTrue($exists);

        $notExists = $this->item->exists(999999);
        $this->assertFalse($notExists);
    }

    public function testFullCsvImportFlowSimulated(): void
    {
        $csvRow = [
            'Id' => '',
            'Barcode' => 'FULL-TEST-001',
            'Item Name' => 'Complete Test Item',
            'Category' => 'Electronics',
            'Supplier ID' => '',
            'Cost Price' => '50.00',
            'Unit Price' => '100.00',
            'Tax 1 Name' => 'VAT',
            'Tax 1 Percent' => '20',
            'Tax 2 Name' => '',
            'Tax 2 Percent' => '',
            'Reorder Level' => '10',
            'Description' => 'A complete test item for CSV import',
            'Allow Alt Description' => '1',
            'Item has Serial Number' => '0',
            'Image' => '',
            'HSN' => '84713020'
        ];

        $itemData = [
            'item_id' => (int)$csvRow['Id'] ?: null,
            'name' => $csvRow['Item Name'],
            'description' => $csvRow['Description'],
            'category' => $csvRow['Category'],
            'cost_price' => (float)$csvRow['Cost Price'],
            'unit_price' => (float)$csvRow['Unit Price'],
            'reorder_level' => (int)$csvRow['Reorder Level'],
            'item_number' => $csvRow['Barcode'] ?: null,
            'allow_alt_description' => empty($csvRow['Allow Alt Description']) ? '0' : '1',
            'is_serialized' => empty($csvRow['Item has Serial Number']) ? '0' : '1',
            'hsn_code' => $csvRow['HSN'],
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $taxesData = [];
        if (is_numeric($csvRow['Tax 1 Percent']) && $csvRow['Tax 1 Name'] !== '') {
            $taxesData[] = ['name' => $csvRow['Tax 1 Name'], 'percent' => $csvRow['Tax 1 Percent']];
        }
        if (is_numeric($csvRow['Tax 2 Percent']) && $csvRow['Tax 2 Name'] !== '') {
            $taxesData[] = ['name' => $csvRow['Tax 2 Name'], 'percent' => $csvRow['Tax 2 Percent']];
        }

        if (!empty($taxesData)) {
            $this->item_taxes->save_value($taxesData, $itemData['item_id']);
        }

        $locationId = 1;
        $quantity = 75;

        $quantityData = [
            'item_id' => $itemData['item_id'],
            'location_id' => $locationId,
            'quantity' => $quantity
        ];
        $this->item_quantity->save_value($quantityData, $itemData['item_id'], $locationId);

        $inventoryData = [
            'trans_inventory' => $quantity,
            'trans_items' => $itemData['item_id'],
            'trans_location' => $locationId,
            'trans_comment' => 'CSV import quantity',
            'trans_user' => 1
        ];
        $this->inventory->insert($inventoryData);

        $savedItem = $this->item->get_info($itemData['item_id']);
        $this->assertEquals('Complete Test Item', $savedItem->name);
        $this->assertEquals('Electronics', $savedItem->category);
        $this->assertEquals(50.00, (float)$savedItem->cost_price);
        $this->assertEquals(100.00, (float)$savedItem->unit_price);
        $this->assertEquals('84713020', $savedItem->hsn_code);

        $savedQuantity = $this->item_quantity->get_item_quantity($itemData['item_id'], $locationId);
        $this->assertEquals($quantity, (int)$savedQuantity->quantity);

        $savedTaxes = $this->item_taxes->get_info($itemData['item_id']);
        $this->assertCount(1, $savedTaxes);
        $this->assertEquals('VAT', $savedTaxes[0]['name']);
        $this->assertEquals(20, (float)$savedTaxes[0]['percent']);

        $inventoryRecords = $this->inventory->get_inventory_data_for_item($itemData['item_id'], $locationId);
        $this->assertGreaterThanOrEqual(1, $inventoryRecords->getNumRows());
    }

    public function testImportCsvInvalidStockLocationColumn(): void
    {
        $csvRow = [
            'Id' => '',
            'Item Name' => 'Test Item Invalid Location',
            'Category' => 'Test',
            'Cost Price' => '10.00',
            'Unit Price' => '20.00',
            'location_NonExistentLocation' => '100'
        ];

        $allowedLocations = [1 => 'Warehouse'];

        $locationColumnsInCsv = [];
        foreach (array_keys($csvRow) as $key) {
            if (str_starts_with($key, 'location_')) {
                $locationColumnsInCsv[$key] = substr($key, 9);
            }
        }

        $invalidLocations = [];
        foreach ($locationColumnsInCsv as $column => $locationName) {
            if (!in_array($locationName, $allowedLocations)) {
                $invalidLocations[] = $locationName;
            }
        }

        $this->assertNotEmpty($invalidLocations, 'Should detect invalid location in CSV');
        $this->assertContains('NonExistentLocation', $invalidLocations);
    }

    public function testImportCsvValidStockLocationColumn(): void
    {
        $csvRow = [
            'Id' => '',
            'Item Name' => 'Test Item Valid Location',
            'Category' => 'Test',
            'Cost Price' => '10.00',
            'Unit Price' => '20.00',
            'location_Warehouse' => '100'
        ];

        $allowedLocations = [1 => 'Warehouse'];

        $locationColumnsInCsv = [];
        foreach (array_keys($csvRow) as $key) {
            if (str_starts_with($key, 'location_')) {
                $locationColumnsInCsv[$key] = substr($key, 9);
            }
        }

        $invalidLocations = [];
        foreach ($locationColumnsInCsv as $column => $locationName) {
            if (!in_array($locationName, $allowedLocations)) {
                $invalidLocations[] = $locationName;
            }
        }

        $this->assertEmpty($invalidLocations, 'Should have no invalid locations');
    }

    public function testImportCsvMixedValidAndInvalidLocations(): void
    {
        $csvRow = [
            'Id' => '',
            'Item Name' => 'Test Item Mixed Locations',
            'Category' => 'Test',
            'Cost Price' => '10.00',
            'Unit Price' => '20.00',
            'location_Warehouse' => '100',
            'location_InvalidLocation' => '50'
        ];

        $allowedLocations = [1 => 'Warehouse', 2 => 'Store'];

        $locationColumnsInCsv = [];
        foreach (array_keys($csvRow) as $key) {
            if (str_starts_with($key, 'location_')) {
                $locationColumnsInCsv[$key] = substr($key, 9);
            }
        }

        $invalidLocations = [];
        foreach ($locationColumnsInCsv as $column => $locationName) {
            if (!in_array($locationName, $allowedLocations)) {
                $invalidLocations[] = $locationName;
            }
        }

        $this->assertCount(1, $invalidLocations, 'Should have exactly one invalid location');
        $this->assertContains('InvalidLocation', $invalidLocations);
    }

    public function testValidateCsvStockLocations(): void
    {
        $csvContent = "Id,\"Item Name\",Category,\"Cost Price\",\"Unit Price\",\"location_Warehouse\",\"location_FakeLocation\"\n";
        $csvContent .= ",Test Item,Test,10.00,20.00,100,50\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_location_test_');
        file_put_contents($tempFile, $csvContent);

        $rows = get_csv_file($tempFile);
        $this->assertCount(1, $rows);

        $row = $rows[0];
        $this->assertArrayHasKey('location_Warehouse', $row);
        $this->assertArrayHasKey('location_FakeLocation', $row);

        unlink($tempFile);
    }

    public function testImportItemQuantityOnlyForValidLocations(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Item Location Test',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $this->assertTrue($this->item->save_value($itemData));

        $uniqueId = uniqid();
        $locations = ['Warehouse' . $uniqueId, 'Store' . $uniqueId];

        $allowedLocations = [];
        foreach ($locations as $index => $locationName) {
            $currentLocation = ['location_name' => $locationName, 'deleted' => 0];
            $this->assertTrue($this->stock_location->save_value($currentLocation, NEW_ENTRY));
            $allowedLocations[$currentLocation['location_id']] = $locationName;
        }

        $csvRowSimulated = [
            'location_Warehouse' . $uniqueId => '100',
            'location_Store' . $uniqueId => '50',
            'location_NonExistent' => '25'
        ];

        foreach ($allowedLocations as $locationId => $locationName) {
            $columnName = "location_$locationName";
            if (isset($csvRowSimulated[$columnName]) || $csvRowSimulated[$columnName] === '0') {
                $quantityData = [
                    'item_id' => $itemData['item_id'],
                    'location_id' => $locationId,
                    'quantity' => (int)$csvRowSimulated[$columnName]
                ];
                $this->item_quantity->save_value($quantityData, $itemData['item_id'], $locationId);
            }
        }

        $warehouseId = array_search('Warehouse' . $uniqueId, $allowedLocations, true);
        $warehouseQuantity = $this->item_quantity->get_item_quantity($itemData['item_id'], $warehouseId);
        $this->assertEquals(100, (int)$warehouseQuantity->quantity);

        $storeId = array_search('Store'. $uniqueId, $allowedLocations, true);
        $storeQuantity = $this->item_quantity->get_item_quantity($itemData['item_id'], $storeId);
        $this->assertEquals(50, (int)$storeQuantity->quantity);

        $result = $this->item_quantity->exists($itemData['item_id'], 999);
        $this->assertFalse($result, 'Should not have quantity for non-existent location');
    }

    public function testDetectCsvLocationColumns(): void
    {
        $row = [
            'Id' => '',
            'Item Name' => 'Test',
            'location_Warehouse' => '100',
            'location_Store' => '50',
            'attribute_Color' => 'Red'
        ];

        $locationColumns = [];
        foreach (array_keys($row) as $key) {
            if (str_starts_with($key, 'location_')) {
                $locationColumns[$key] = substr($key, 9);
            }
        }

        $this->assertCount(2, $locationColumns);
        $this->assertArrayHasKey('location_Warehouse', $locationColumns);
        $this->assertArrayHasKey('location_Store', $locationColumns);
        $this->assertEquals('Warehouse', $locationColumns['location_Warehouse']);
        $this->assertEquals('Store', $locationColumns['location_Store']);
    }

    public function testValidateLocationNamesCaseSensitivity(): void
    {
        $allowedLocations = [1 => 'Warehouse', 2 => 'Store'];

        $csvLocationName = 'warehouse';

        $isValid = in_array($csvLocationName, $allowedLocations);
        $this->assertFalse($isValid, 'Location names should be case-sensitive');

        $csvLocationName = 'Warehouse';
        $isValid = in_array($csvLocationName, $allowedLocations);
        $this->assertTrue($isValid, 'Valid location name should pass validation');
    }
}
