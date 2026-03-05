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

class ItemsCsvImportTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = null;

    protected $item;
    protected $item_quantity;
    protected $inventory;
    protected $item_taxes;
    protected $attribute;
    protected $stock_location;
    protected $supplier;

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
        $stock_locations = ['Warehouse'];
        $attributes = [];

        $csv = generate_import_items_csv($stock_locations, $attributes);

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
        $stock_locations = ['Warehouse', 'Store', 'Backroom'];
        $attributes = [];

        $csv = generate_import_items_csv($stock_locations, $attributes);

        $this->assertStringContainsString('"location_Warehouse"', $csv);
        $this->assertStringContainsString('"location_Store"', $csv);
        $this->assertStringContainsString('"location_Backroom"', $csv);
    }

    public function testGenerateCsvHeaderWithAttributes(): void
    {
        $stock_locations = ['Warehouse'];
        $attributes = ['Color', 'Size', 'Weight'];

        $csv = generate_import_items_csv($stock_locations, $attributes);

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
        $csv_content = "Id,Barcode,\"Item Name\",Category,\"Supplier ID\",\"Cost Price\",\"Unit Price\",\"Tax 1 Name\",\"Tax 1 Percent\",\"Tax 2 Name\",\"Tax 2 Percent\",\"Reorder Level\",Description,\"Allow Alt Description\",\"Item has Serial Number\",Image,HSN\n";
        $csv_content .= ",ITEM001,Test Item,Electronics,1,10.00,15.00,,,,,5,Test Description,0,0,,HSN001\n";

        $temp_file = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($temp_file, $csv_content);

        $rows = get_csv_file($temp_file);

        $this->assertCount(1, $rows);
        $this->assertEquals('', $rows[0]['Id']);
        $this->assertEquals('ITEM001', $rows[0]['Barcode']);
        $this->assertEquals('Test Item', $rows[0]['Item Name']);
        $this->assertEquals('Electronics', $rows[0]['Category']);

        unlink($temp_file);
    }

    public function testGetCsvFileWithBom(): void
    {
        $bom = pack('CCC', 0xef, 0xbb, 0xbf);
        $csv_content = $bom . "Id,\"Item Name\",Category\n";
        $csv_content .= "1,Test Item,Electronics\n";

        $temp_file = tempnam(sys_get_temp_dir(), 'csv_test_bom_');
        file_put_contents($temp_file, $csv_content);

        $rows = get_csv_file($temp_file);

        $this->assertCount(1, $rows);
        $this->assertEquals('1', $rows[0]['Id']);
        $this->assertEquals('Test Item', $rows[0]['Item Name']);

        unlink($temp_file);
    }

    public function testGetCsvFileMultipleRows(): void
    {
        $csv_content = "Id,\"Item Name\",Category\n";
        $csv_content .= "1,Item One,Cat A\n";
        $csv_content .= "2,Item Two,Cat B\n";
        $csv_content .= "3,Item Three,Cat C\n";

        $temp_file = tempnam(sys_get_temp_dir(), 'csv_test_multi_');
        file_put_contents($temp_file, $csv_content);

        $rows = get_csv_file($temp_file);

        $this->assertCount(3, $rows);
        $this->assertEquals('Item One', $rows[0]['Item Name']);
        $this->assertEquals('Item Two', $rows[1]['Item Name']);
        $this->assertEquals('Item Three', $rows[2]['Item Name']);

        unlink($temp_file);
    }

    public function testBomExists(): void
    {
        $bom = pack('CCC', 0xef, 0xbb, 0xbf);
        $content_with_bom = $bom . "test content";

        $temp_file = tempnam(sys_get_temp_dir(), 'bom_test_');
        file_put_contents($temp_file, $content_with_bom);

        $handle = fopen($temp_file, 'r');
        $result = bom_exists($handle);
        fclose($handle);

        $this->assertTrue($result);
        unlink($temp_file);
    }

    public function testBomNotExists(): void
    {
        $content_without_bom = "test content without BOM";

        $temp_file = tempnam(sys_get_temp_dir(), 'no_bom_test_');
        file_put_contents($temp_file, $content_without_bom);

        $handle = fopen($temp_file, 'r');
        $result = bom_exists($handle);
        fclose($handle);

        $this->assertFalse($result);
        unlink($temp_file);
    }

    public function testImportItemBasicFields(): void
    {
        $item_data = [
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

        $item_id = $this->item->save_value($item_data);

        $this->assertIsInt($item_id);
        $this->assertGreaterThan(0, $item_id);

        $saved_item = $this->item->get_info($item_id);
        $this->assertEquals('CSV Imported Item', $saved_item->name);
        $this->assertEquals('Description from CSV', $saved_item->description);
        $this->assertEquals('Electronics', $saved_item->category);
        $this->assertEquals(10.50, (float)$saved_item->cost_price);
        $this->assertEquals(25.99, (float)$saved_item->unit_price);
    }

    public function testImportItemWithQuantity(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Item With Quantity',
            'category' => 'Test Category',
            'cost_price' => 5.00,
            'unit_price' => 10.00,
            'reorder_level' => 2,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $location_id = 1;
        $quantity = 100;

        $item_quantity_data = [
            'item_id' => $item_id,
            'location_id' => $location_id,
            'quantity' => $quantity
        ];

        $result = $this->item_quantity->save_value($item_quantity_data, $item_id, $location_id);
        $this->assertTrue($result);

        $saved_quantity = $this->item_quantity->get_item_quantity($item_id, $location_id);
        $this->assertEquals($quantity, $saved_quantity->quantity);
    }

    public function testImportItemCreatesInventoryRecord(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Item With Inventory',
            'category' => 'Test',
            'cost_price' => 5.00,
            'unit_price' => 10.00,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $inventory_data = [
            'trans_inventory' => 50,
            'trans_items' => $item_id,
            'trans_location' => 1,
            'trans_comment' => 'CSV Import',
            'trans_user' => 1
        ];

        $trans_id = $this->inventory->insert($inventory_data);

        $this->assertIsInt($trans_id);
        $this->assertGreaterThan(0, $trans_id);

        $inventory_records = $this->inventory->get_inventory_data_for_item($item_id, 1);
        $this->assertGreaterThanOrEqual(1, $inventory_records->getNumRows());
    }

    public function testImportItemWithTaxes(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Taxable Item',
            'category' => 'Test',
            'cost_price' => 100.00,
            'unit_price' => 150.00,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $taxes_data = [
            ['name' => 'VAT', 'percent' => 20],
            ['name' => 'GST', 'percent' => 10]
        ];

        $result = $this->item_taxes->save_value($taxes_data, $item_id);
        $this->assertTrue($result);

        $saved_taxes = $this->item_taxes->get_info($item_id);
        
        $tax_names = array_column($saved_taxes, 'name');
        $this->assertContains('VAT', $tax_names);
        $this->assertContains('GST', $tax_names);
    }

    public function testImportMultipleItemsFromSimulatedCsv(): void
    {
        $csv_data = [
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

        $imported_item_ids = [];

        foreach ($csv_data as $row) {
            $item_data = [
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

            $item_id = $this->item->save_value($item_data);
            $imported_item_ids[] = $item_id;
        }

        $this->assertCount(2, $imported_item_ids);

        $item1 = $this->item->get_info($imported_item_ids[0]);
        $this->assertEquals('First Item', $item1->name);
        $this->assertEquals(10.00, (float)$item1->cost_price);

        $item2 = $this->item->get_info($imported_item_ids[1]);
        $this->assertEquals('Second Item', $item2->name);
        $this->assertEquals(15.00, (float)$item2->cost_price);
    }

    public function testImportUpdateExistingItem(): void
    {
        $original_data = [
            'item_id' => null,
            'name' => 'Original Name',
            'category' => 'Original Category',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($original_data);

        $updated_data = [
            'item_id' => $item_id,
            'name' => 'Updated Name',
            'category' => 'Updated Category',
            'cost_price' => 15.00,
            'unit_price' => 30.00,
            'description' => 'New description',
            'reorder_level' => 10,
            'deleted' => 0
        ];

        $this->item->save_value($updated_data);

        $updated_item = $this->item->get_info($item_id);
        $this->assertEquals('Updated Name', $updated_item->name);
        $this->assertEquals('Updated Category', $updated_item->category);
        $this->assertEquals(15.00, (float)$updated_item->cost_price);
        $this->assertEquals(30.00, (float)$updated_item->unit_price);
    }

    public function testImportItemWithAttributeText(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Item With Attribute',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $definition_data = [
            'definition_name' => 'Color',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definition_id = $this->attribute->saveDefinition($definition_data);

        $attribute_value = 'Red';
        $attribute_id = $this->attribute->saveAttributeValue(
            $attribute_value,
            $definition_id,
            $item_id,
            false,
            TEXT
        );

        $this->assertNotFalse($attribute_id);

        $saved_value = $this->attribute->getAttributeValue($item_id, $definition_id);
        $this->assertEquals('Red', $saved_value->attribute_value);
    }

    public function testImportItemWithAttributeDropdown(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Item With Dropdown',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $definition_data = [
            'definition_name' => 'Size',
            'definition_type' => DROPDOWN,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definition_id = $this->attribute->saveDefinition($definition_data);

        $dropdown_values = ['Small', 'Medium', 'Large'];
        foreach ($dropdown_values as $i => $value) {
            $this->db->table('attribute_values')->insert([
                'attribute_value' => $value,
                'definition_id' => $definition_id,
                'definition_type' => DROPDOWN,
                'attribute_group' => $i,
                'deleted' => 0
            ]);
        }

        $attribute_value = 'Medium';
        $attribute_id = $this->attribute->saveAttributeValue(
            $attribute_value,
            $definition_id,
            $item_id,
            false,
            DROPDOWN
        );

        $this->assertNotFalse($attribute_id);

        $saved_value = $this->attribute->getAttributeValue($item_id, $definition_id);
        $this->assertEquals('Medium', $saved_value->attribute_value);
    }

    public function testImportItemQuantityZero(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Item Zero Quantity',
            'category' => 'Test',
            'cost_price' => 5.00,
            'unit_price' => 10.00,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $location_id = 1;

        $item_quantity_data = [
            'item_id' => $item_id,
            'location_id' => $location_id,
            'quantity' => 0
        ];

        $result = $this->item_quantity->save_value($item_quantity_data, $item_id, $location_id);
        $this->assertTrue($result);

        $saved_quantity = $this->item_quantity->get_item_quantity($item_id, $location_id);
        $this->assertEquals(0, (int)$saved_quantity->quantity);
    }

    public function testImportItemWithNegativeReorderLevel(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Item Negative Reorder',
            'category' => 'Test',
            'cost_price' => 5.00,
            'unit_price' => 10.00,
            'reorder_level' => -1,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $saved_item = $this->item->get_info($item_id);
        $this->assertEquals(-1, (int)$saved_item->reorder_level);
    }

    public function testImportItemWithHighPrecisionPrices(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'High Precision Item',
            'category' => 'Test',
            'cost_price' => 10.123456,
            'unit_price' => 25.876543,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $saved_item = $this->item->get_info($item_id);
        $cost_diff = abs(10.123456 - (float)$saved_item->cost_price);
        $price_diff = abs(25.876543 - (float)$saved_item->unit_price);

        $this->assertLessThan(0.001, $cost_diff, 'Cost price should maintain precision');
        $this->assertLessThan(0.001, $price_diff, 'Unit price should maintain precision');
    }

    public function testImportItemWithHsnCode(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Item With HSN',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'hsn_code' => '8471',
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $saved_item = $this->item->get_info($item_id);
        $this->assertEquals('8471', $saved_item->hsn_code);
    }

    public function testImportItemQuantityMultipleLocations(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Item Multi Location',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $quantities = [
            ['location_id' => 1, 'quantity' => 100],
            ['location_id' => 2, 'quantity' => 50],
            ['location_id' => 3, 'quantity' => 25]
        ];

        foreach ($quantities as $q) {
            $result = $this->item_quantity->save_value(
                ['item_id' => $item_id, 'location_id' => $q['location_id'], 'quantity' => $q['quantity']],
                $item_id,
                $q['location_id']
            );
            $this->assertTrue($result);
        }

        foreach ($quantities as $q) {
            $saved = $this->item_quantity->get_item_quantity($item_id, $q['location_id']);
            $this->assertEquals($q['quantity'], (int)$saved->quantity, "Quantity at location {$q['location_id']} should match");
        }
    }

    public function testCsvImportQuantityValidationNumeric(): void
    {
        $csv_data = [
            'Id' => '',
            'Barcode' => 'VALID-ITEM',
            'Item Name' => 'Valid Item',
            'Category' => 'Test',
            'Cost Price' => '10.00',
            'Unit Price' => '20.00',
            'location_Warehouse' => '100'
        ];

        $this->assertTrue(is_numeric($csv_data['location_Warehouse']));
        $this->assertTrue(is_numeric($csv_data['Cost Price']));
        $this->assertTrue(is_numeric($csv_data['Unit Price']));
    }

    public function testCsvImportEmptyBarcodeAllowed(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Item Without Barcode',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'item_number' => null,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $this->assertIsInt($item_id);
        $this->assertGreaterThan(0, $item_id);

        $saved_item = $this->item->get_info($item_id);
        $this->assertEquals('Item Without Barcode', $saved_item->name);
    }

    public function testCsvImportItemExistsCheck(): void
    {
        $item_data = [
            'item_id' => null,
            'name' => 'Existing Item',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $exists = $this->item->exists($item_id);
        $this->assertTrue($exists);

        $not_exists = $this->item->exists(999999);
        $this->assertFalse($not_exists);
    }

    public function testFullCsvImportFlowSimulated(): void
    {
        $csv_row = [
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

        $item_data = [
            'item_id' => (int)$csv_row['Id'] ?: null,
            'name' => $csv_row['Item Name'],
            'description' => $csv_row['Description'],
            'category' => $csv_row['Category'],
            'cost_price' => (float)$csv_row['Cost Price'],
            'unit_price' => (float)$csv_row['Unit Price'],
            'reorder_level' => (int)$csv_row['Reorder Level'],
            'item_number' => $csv_row['Barcode'] ?: null,
            'allow_alt_description' => empty($csv_row['Allow Alt Description']) ? '0' : '1',
            'is_serialized' => empty($csv_row['Item has Serial Number']) ? '0' : '1',
            'hsn_code' => $csv_row['HSN'],
            'deleted' => 0
        ];

        $item_id = $this->item->save_value($item_data);

        $taxes_data = [];
        if (is_numeric($csv_row['Tax 1 Percent']) && $csv_row['Tax 1 Name'] !== '') {
            $taxes_data[] = ['name' => $csv_row['Tax 1 Name'], 'percent' => $csv_row['Tax 1 Percent']];
        }
        if (is_numeric($csv_row['Tax 2 Percent']) && $csv_row['Tax 2 Name'] !== '') {
            $taxes_data[] = ['name' => $csv_row['Tax 2 Name'], 'percent' => $csv_row['Tax 2 Percent']];
        }

        if (!empty($taxes_data)) {
            $this->item_taxes->save_value($taxes_data, $item_id);
        }

        $location_id = 1;
        $quantity = 75;

        $quantity_data = [
            'item_id' => $item_id,
            'location_id' => $location_id,
            'quantity' => $quantity
        ];
        $this->item_quantity->save_value($quantity_data, $item_id, $location_id);

        $inventory_data = [
            'trans_inventory' => $quantity,
            'trans_items' => $item_id,
            'trans_location' => $location_id,
            'trans_comment' => 'CSV import quantity',
            'trans_user' => 1
        ];
        $this->inventory->insert($inventory_data);

        $saved_item = $this->item->get_info($item_id);
        $this->assertEquals('Complete Test Item', $saved_item->name);
        $this->assertEquals('Electronics', $saved_item->category);
        $this->assertEquals(50.00, (float)$saved_item->cost_price);
        $this->assertEquals(100.00, (float)$saved_item->unit_price);
        $this->assertEquals('84713020', $saved_item->hsn_code);

        $saved_quantity = $this->item_quantity->get_item_quantity($item_id, $location_id);
        $this->assertEquals($quantity, (int)$saved_quantity->quantity);

        $saved_taxes = $this->item_taxes->get_info($item_id);
        $this->assertCount(1, $saved_taxes);
        $this->assertEquals('VAT', $saved_taxes[0]['name']);
        $this->assertEquals(20, (float)$saved_taxes[0]['percent']);

        $inventory_records = $this->inventory->get_inventory_data_for_item($item_id, $location_id);
        $this->assertGreaterThanOrEqual(1, $inventory_records->getNumRows());
    }
}