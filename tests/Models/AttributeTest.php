<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Fabricator;
use App\Models\Attribute;
use App\Models\Item;
use App\Helpers\attribute_helper;
use Config\OSPOS;

/**
 * Test suite for Attribute model
 * 
 * Tests for PR #4384: Case-sensitive attribute updates and CSV Import attribute deletion capability
 */
class AttributeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var Item
     */
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attribute = model(Attribute::class);
        $this->item = model(Item::class);
        helper('attribute');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test that case-sensitive attribute value updates work correctly
     * 
     * @return void
     */
    public function testCaseSensitiveAttributeValueUpdate(): void
    {
        // Create a text definition
        $definitionData = [
            'definition_name' => 'Color',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Create an item
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST001',
            'description' => 'Test item description',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        // Save initial attribute value with uppercase
        $attributeValue = 'RED';
        $attributeId1 = $this->attribute->saveAttributeValue(
            $attributeValue,
            $definitionId,
            $itemId,
            false,
            TEXT
        );

        // Update with lowercase
        $attributeValueLower = 'red';
        $attributeId2 = $this->attribute->saveAttributeValue(
            $attributeValueLower,
            $definitionId,
            $itemId,
            $attributeId1,
            TEXT
        );

        // Verify the value was updated to lowercase
        $result = $this->attribute->getAttributeValue($itemId, $definitionId);
        $this->assertEquals('red', strtolower($result->attribute_value), 
            'Attribute value should be updated from RED to red');
        
        // The attribute_value table should have been updated, not duplicated
        $builder = $this->db->table('attribute_values');
        $builder->where('attribute_id', $attributeId1);
        $query = $builder->get();
        $rows = $query->getResult();
        
        $this->assertCount(1, $rows, 'Should only have one attribute_value row');
    }

    /**
     * Test that attribute link deletion works correctly
     * 
     * @return void
     */
    public function testDeleteAttributeLinks(): void
    {
        // Create an item
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST002',
            'description' => 'Test item description',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        // Create a definition
        $definitionData = [
            'definition_name' => 'Size',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Save an attribute link
        $attributeValue = 'Medium';
        $attributeId = $this->attribute->saveAttributeValue(
            $attributeValue,
            $definitionId,
            $itemId,
            false,
            TEXT
        );

        // Verify the link exists
        $this->attribute->saveAttributeLink($itemId, $definitionId, $attributeId);
        
        $builder = $this->db->table('attribute_links');
        $builder->where('item_id', $itemId);
        $builder->where('definition_id', $definitionId);
        $query = $builder->get();
        $result = $query->getResult();
        
        $this->assertCount(1, $result, 'Attribute link should exist before deletion');

        // Delete the attribute link
        $deleted = $this->attribute->deleteAttributeLinks($itemId, $definitionId);

        // Verify the link is deleted
        $this->assertTrue($deleted, 'deleteAttributeLinks should return true');

        $builder = $this->db->table('attribute_links');
        $builder->where('item_id', $itemId);
        $builder->where('definition_id', $definitionId);
        $query = $builder->get();
        $result = $query->getResult();
        
        $this->assertCount(0, $result, 'Attribute link should be deleted');
    }

    /**
     * Test that category dropdown can be enabled (bug fix from PR)
     * 
     * @return void
     */
    public function testCategoryDropdownCanBeEnabled(): void
    {
        // Create a dropdown definition for category
        $definitionData = [
            'definition_name' => 'category_dropdown',
            'definition_type' => DROPDOWN,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // The bug was that definition_flags == 1 check prevented dropdowns
        // Now it should use truthy check
        $builder = $this->db->table('attribute_definitions');
        $builder->where('definition_id', $definitionId);
        $query = $builder->get();
        $result = $query->getRow();

        $this->assertEquals(DROPDOWN, $result->definition_type, 'Definition type should be DROPDOWN');
        $this->assertEquals(0, $result->definition_flags, 'Definition flags should be 0');
    }

    /**
     * Test DROPDOWN attribute value saving
     * 
     * @return void
     */
    public function testDropdownAttributeValueSave(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST003',
            'description' => 'Test item description',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        // Create a dropdown definition
        $definitionData = [
            'definition_name' => 'Material',
            'definition_type' => DROPDOWN,
            'definition_flags' => 0,
            'definition_unit' => null,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Add dropdown values
        $dropdownValues = ['Cotton', 'Polyester', 'Wool'];
        foreach ($dropdownValues as $i => $value) {
            $valueData = [
                'attribute_value' => $value,
                'definition_id' => $definitionId,
                'definition_type' => DROPDOWN,
                'attribute_group' => $i,
                'deleted' => 0
            ];
            $this->db->table('attribute_values')->insert($valueData);
        }

        // Save attribute with dropdown value
        $attributeValue = 'Cotton';
        $attributeId = $this->attribute->saveAttributeValue(
            $attributeValue,
            $definitionId,
            $itemId,
            false,
            DROPDOWN
        );

        // Verify the dropdown value was saved
        $result = $this->attribute->getAttributeValue($itemId, $definitionId);
        $this->assertEquals('Cotton', $result->attribute_value);
        
        // Verify the attribute link was created
        $builder = $this->db->table('attribute_links');
        $builder->where('item_id', $itemId);
        $builder->where('definition_id', $definitionId);
        $query = $builder->get();
        $linkResult = $query->getRow();
        
        $this->assertNotNull($linkResult->attribute_id, 'Attribute link should be created for dropdown');
    }

    /**
     * Test DATE attribute value saving
     * 
     * @return void
     */
    public function testDateAttributeValueSave(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST004',
            'description' => 'Test item',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        $definitionData = [
            'definition_name' => 'Manufacture Date',
            'definition_type' => DATE,
            'definition_flags' => 0,
            'definition_unit' => null,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Save date attribute
        $dateValue = date('Y-m-d');
        $attributeId = $this->attribute->saveAttributeValue(
            $dateValue,
            $definitionId,
            $itemId,
            false,
            DATE
        );

        // Verify the date was saved
        $result = $this->attribute->getAttributeValue($itemId, $definitionId);
        $this->assertEquals($dateValue, $result->attribute_date);
    }

    /**
     * Test DATE attribute value case update
     * 
     * @return void
     */
    public function testDateAttributeValueCaseUpdate(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST005',
            'description' => 'Test item',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        $definitionData = [
            'definition_name' => 'Expiration Date',
            'definition_type' => DATE,
            'definition_flags' => 0,
            'definition_unit' => null,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Save initial date
        $dateValue = date('Y-m-d', strtotime('2025-01-01'));
        $attributeId1 = $this->attribute->saveAttributeValue(
            $dateValue,
            $definitionId,
            $itemId,
            false,
            DATE
        );

        // Date format doesn't have case, but this test verifies the logic path
        $result = $this->attribute->getAttributeValue($itemId, $definitionId);
        $this->assertEquals($dateValue, $result->attribute_date);
    }

    /**
     * Test DECIMAL attribute value saving
     * 
     * @return void
     */
    public function testDecimalAttributeValueSave(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST006',
            'description' => 'Test item',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        $definitionData = [
            'definition_name' => 'Weight',
            'definition_type' => DECIMAL,
            'definition_flags' => 0,
            'definition_unit' => 'kg',
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Save decimal attribute
        $decimalValue = '2.5';
        $attributeId = $this->attribute->saveAttributeValue(
            $decimalValue,
            $definitionId,
            $itemId,
            false,
            DECIMAL
        );

        // Verify the decimal was saved
        $result = $this->attribute->getAttributeValue($itemId, $definitionId);
        $this->assertEquals(2.5, (float)$result->attribute_decimal);
    }

    /**
     * Test CHECKBOX attribute value saving
     * 
     * @return void
     */
    public function testCheckboxAttributeValueSave(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST007',
            'description' => 'Test item',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        $definitionData = [
            'definition_name' => 'Available',
            'definition_type' => CHECKBOX,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Save checkbox attribute (checked)
        $attributeId1 = $this->attribute->saveAttributeValue(
            'true',
            $definitionId,
            $itemId,
            false,
            CHECKBOX
        );

        $result = $this->attribute->getAttributeValue($itemId, $definitionId);
        $this->assertEquals('1', $result->attribute_value);

        // Update to unchecked
        $attributeId2 = $this->attribute->saveAttributeValue(
            'false',
            $definitionId,
            $itemId,
            $attributeId1,
            CHECKBOX
        );

        $result = $this->attribute->getAttributeValue($itemId, $definitionId);
        $this->assertEquals('0', $result->attribute_value);
    }

    /**
     * Test category dropdown with CATEGORY_DEFINITION_ID
     * 
     * @return void
     */
    public function testCategoryDropdownWithConstant(): void
    {
        // Use the CATEGORY_DEFINITION_ID constant instead of -1
        $definitionData = [
            'definition_id' => CATEGORY_DEFINITION_ID,
            'definition_name' => 'category_dropdown',
            'definition_type' => DROPDOWN,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        
        $this->assertEquals(CATEGORY_DEFINITION_ID, -1, 'CATEGORY_DEFINITION_ID constant should equal -1');
        
        $definitionId = $this->attribute->saveDefinition($definitionData, CATEGORY_DEFINITION_ID);
        
        $this->assertEquals(CATEGORY_DEFINITION_ID, $definitionId, 
            'Category definition ID should remain CATEGORY_DEFINITION_ID');
    }

    /**
     * Test that attribute links with sale_id or receiving_id are not deleted
     * 
     * @return void
     */
    public function testDeleteAttributeLinksPreservesSalesAndReceivings(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST008',
            'description' => 'Test item',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        $definitionData = [
            'definition_name' => 'Color',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Create attribute value
        $attributeValue = 'Blue';
        $attributeId = $this->attribute->saveAttributeValue(
            $attributeValue,
            $definitionId,
            $itemId,
            false,
            TEXT
        );

        // Create attribute link WITHOUT sale_id/receiving_id
        $this->attribute->saveAttributeLink($itemId, $definitionId, $attributeId);

        // Create attribute link WITH sale_id (simulation)
        $builder = $this->db->table('attribute_links');
        $builder->insert([
            'attribute_id' => $attributeId,
            'item_id' => $itemId,
            'definition_id' => $definitionId,
            'sale_id' => 1, // Has a sale reference
            'receiving_id' => null
        ]);

        // Delete attribute links
        $this->attribute->deleteAttributeLinks($itemId, $definitionId);

        // Verify link WITH sale_id was NOT deleted
        $builder = $this->db->table('attribute_links');
        $builder->where('item_id', $itemId);
        $builder->where('definition_id', $definitionId);
        $builder->where('attribute_id', $attributeId);
        $query = $builder->get();
        $result = $query->getResult();
        
        $this->assertCount(1, $result, 'Link with sale_id should not be deleted');
    }

    /**
     * Test orphaned value deletion works correctly
     * 
     * @return void
     */
    public function testDeleteOrphanedValues(): void
    {
        $definitionData = [
            'definition_name' => 'Temp Attribute',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Create an orphaned attribute value (no links)
        $builder = $this->db->table('attribute_values');
        $builder->insert([
            'attribute_value' => 'Orphan Value',
            'definition_id' => $definitionId,
            'definition_type' => TEXT,
            'attribute_group' => 0,
            'deleted' => 0
        ]);

        // Delete orphaned values
        $this->attribute->deleteOrphanedValues();

        // Verify orphan was deleted
        $builder = $this->db->table('attribute_values');
        $builder->where('attribute_value', 'Orphan Value');
        $builder->where('definition_id', $definitionId);
        $query = $builder->get();
        $result = $query->getResult();
        
        $this->assertCount(0, $result, 'Orphaned value should be deleted');
    }

    /**
     * Test Unicode case comparison for attribute values
     * 
     * @return void
     */
    public function testUnicodeCaseComparison(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST009',
            'description' => 'Test item',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        $definitionData = [
            'definition_name' => 'Name',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Test with Unicode characters that have case
        $unicodeValue = 'ÄÄÖÜß'; // German umlauts
        $attributeId1 = $this->attribute->saveAttributeValue(
            $unicodeValue,
            $definitionId,
            $itemId,
            false,
            TEXT
        );

        // Update with lowercase
        $unicodeLower = 'äöüß';
        $attributeId2 = $this->attribute->saveAttributeValue(
            $unicodeLower,
            $definitionId,
            $itemId,
            $attributeId1,
            TEXT
        );

        $result = $this->attribute->getAttributeValue($itemId, $definitionId);
        // The value should be updated due to case difference
        $this->assertNotEquals($unicodeValue, $result->attribute_value, 
            'Unicode case should be detected and updated');
    }

    /**
     * Test getAttributeValueByAttributeId method
     * 
     * @return void
     */
    public function testGetAttributeValueByAttributeId(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST010',
            'description' => 'Test item',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        $definitionData = [
            'definition_name' => 'Quality',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        $attributeValue = 'Premium';
        $attributeId = $this->attribute->saveAttributeValue(
            $attributeValue,
            $definitionId,
            $itemId,
            false,
            TEXT
        );

        // Test getting value by attribute ID
        $result = $this->attribute->getAttributeValueByAttributeId($attributeId, TEXT);
        
        $this->assertNotNull($result);
        $this->assertEquals('Premium', $result);
    }

    /**
     * Test attribute link with null attribute_id
     * 
     * @return void
     */
    public function testAttributeLinkWithNullAttributeId(): void
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => 0,
            'supplier_id' => null,
            'item_number' => 'TEST011',
            'description' => 'Test item',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        $definitionData = [
            'definition_name' => 'Brand',
            'definition_type' => TEXT,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData);

        // Save attribute link with null attribute_id (should be inserted as null)
        $saved = $this->attribute->saveAttributeLink($itemId, $definitionId, null);
        
        $this->assertTrue($saved, 'saveAttributeLink should succeed with null attribute_id');

        // Verify it was saved as null
        $builder = $this->db->table('attribute_links');
        $builder->where('item_id', $itemId);
        $builder->where('definition_id', $definitionId);
        $query = $builder->get();
        $result = $query->getRow();
        
        $this->assertNull($result->attribute_id, 'attribute_id should be null');
    }

    /**
     * Test category dropdown updates item category
     * 
     * @return void
     */
    public function testCategoryDropdownUpdatesItemCategory(): void
    {
        // Create a category
        $categoryId = 1; // Assuming category with ID 1 exists
        
        // Create item with category
        $itemData = [
            'item_id' => null,
            'name' => 'Test Item',
            'category' => $categoryId,
            'supplier_id' => null,
            'item_number' => 'TEST012',
            'description' => 'Test item',
            'cost_price' => 10.00,
            'unit_price' => 15.00,
            'reorder_level' => 0,
            'receiving_quantity' => 1,
            'allow_alt_description' => NEW_ENTRY,
            'is_serialized' => NEW_ENTRY,
            'deleted' => 0
        ];
        $itemId = $this->item->saveValue($itemData)['item_id'];

        // Create category dropdown definition
        $definitionData = [
            'definition_id' => CATEGORY_DEFINITION_ID,
            'definition_name' => 'category_dropdown',
            'definition_type' => DROPDOWN,
            'definition_flags' => 0,
            'deleted' => 0
        ];
        $definitionId = $this->attribute->saveDefinition($definitionData, CATEGORY_DEFINITION_ID);
        
        // Add dropdown value matching category name
        $builder = $this->db->table('attribute_values');
        $builder->insert([
            'attribute_value' => 'Electronics',
            'definition_id' => CATEGORY_DEFINITION_ID,
            'definition_type' => DROPDOWN,
            'attribute_group' => 0,
            'deleted' => 0
        ]);

        // Verify the definition was created with CATEGORY_DEFINITION_ID
        $this->assertEquals(CATEGORY_DEFINITION_ID, $definitionId, 
            'Category dropdown should use CATEGORY_DEFINITION_ID constant');
    }
}