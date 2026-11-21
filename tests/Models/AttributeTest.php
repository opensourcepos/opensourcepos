<?php

namespace Tests\Models;

use App\Models\Attribute;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\OSPOS;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\ItemFixtureTrait;

class AttributeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use ItemFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private Attribute $attribute;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();

        config(OSPOS::class)->update_settings();
        $this->attribute = model(Attribute::class);
    }

    public function testDeleteDropdownAttributeValue_DeletesExistingAndHandlesMissingValue(): void
    {
        $definitionData = [
            'definition_name'  => 'DropdownDef_' . uniqid(),
            'definition_type'  => DROPDOWN,
            'definition_flags' => Attribute::SHOW_IN_ITEMS,
            'deleted'          => 0,
        ];
        $this->assertTrue($this->attribute->saveDefinition($definitionData));
        $definitionId = (int) $definitionData['definition_id'];

        $value = 'Option_' . uniqid();
        $attributeId = $this->attribute->saveAttributeValue($value, $definitionId, false, false, DROPDOWN);
        $this->assertGreaterThan(0, $attributeId);

        $this->assertTrue($this->attribute->deleteDropdownAttributeValue($value, $definitionId));
        $this->assertFalse($this->attribute->attributeValueExists($value, DROPDOWN));

        $this->assertTrue($this->attribute->deleteDropdownAttributeValue('NonExistent_' . uniqid(), $definitionId));
    }

    public function testSaveDefinition_CompletesTransactionWhenConvertingDropdownToCheckbox(): void
    {
        $definitionData = [
            'definition_name'  => 'ConvDropdown_' . uniqid(),
            'definition_type'  => DROPDOWN,
            'definition_flags' => Attribute::SHOW_IN_ITEMS,
            'deleted'          => 0,
        ];
        $this->assertTrue($this->attribute->saveDefinition($definitionData));
        $definitionId = (int) $definitionData['definition_id'];

        $itemId = $this->createTestItem();
        $attrId = $this->attribute->saveAttributeValue('1', $definitionId, $itemId, false, DROPDOWN);
        $this->assertGreaterThan(0, $attrId);

        $updateData = [
            'definition_name'  => $definitionData['definition_name'],
            'definition_type'  => CHECKBOX,
            'definition_flags' => Attribute::SHOW_IN_ITEMS,
            'deleted'          => 0,
        ];
        $this->assertTrue($this->attribute->saveDefinition($updateData, $definitionId));
        $this->assertSame(0, $this->db->transDepth);
    }

    public function testGetLinkValues_IgnoresInvalidForeignKeyAndNullFlags(): void
    {
        $itemId = $this->createTestItem();

        $result = $this->attribute->get_link_values($itemId, 'invalid_fk', 1, null);

        $this->assertNotNull($result->getRowObject());
    }

    public function testCopyAttributeLinks_IgnoresInvalidForeignKey(): void
    {
        $itemId = $this->createTestItem();
        $definitionData = [
            'definition_name'  => 'CopyTestDef_' . uniqid(),
            'definition_type'  => TEXT,
            'definition_flags' => Attribute::SHOW_IN_ITEMS,
            'deleted'          => 0,
        ];
        $this->assertTrue($this->attribute->saveDefinition($definitionData));
        $definitionId = (int) $definitionData['definition_id'];
        $this->attribute->saveAttributeValue('TestVal', $definitionId, $itemId, false, TEXT);

        $beforeCount = $this->db->table('attribute_links')->where('item_id', $itemId)->countAllResults();
        $this->attribute->copy_attribute_links($itemId, 'invalid_fk', 999);
        $afterCount = $this->db->table('attribute_links')->where('item_id', $itemId)->countAllResults();

        $this->assertSame($beforeCount, $afterCount);
    }
}
