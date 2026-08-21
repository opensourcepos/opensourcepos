<?php

namespace Tests\Helpers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\OSPOS;
use stdClass;

class GetItemDataRowTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        helper('tabular');

        $this->injectSettings();
    }

    protected function injectSettings(array $overrides = []): void
    {
        $config = new OSPOS();
        $config->settings = array_merge([
            'multi_pack_enabled'        => 0,
            'use_destination_based_tax' => 0,
            'number_locale'             => 'en_US',
            'currency_decimals'         => 2,
            'quantity_decimals'         => 0,
            'thousands_separator'       => 1,
            'currency_symbol'           => '$',
        ], $overrides);

        Factories::injectMock('config', OSPOS::class, $config);
    }

    protected function makeItem(array $overrides = []): stdClass
    {
        $item = new stdClass();

        $defaults = [
            'item_id'      => 1,
            'item_number'  => 'ITEM-001',
            'name'         => 'Test Item',
            'category'     => 'Test Category',
            'company_name' => 'Test Supplier',
            'cost_price'   => 10.00,
            'unit_price'   => 20.00,
            'quantity'     => 5,
            'pic_filename' => null,
            'pack_name'    => null,
            'tax_category_id' => null,
        ];

        foreach (array_merge($defaults, $overrides) as $key => $value) {
            $item->$key = $value;
        }

        return $item;
    }

    public function testTaxPercentsPulledFromPassedMapNotQueried(): void
    {
        $item = $this->makeItem(['item_id' => 42]);

        $columns = getItemDataRow($item, [], [42 => '20.00%']);

        $this->assertEquals('20.00%', $columns['tax_percents']);
    }

    public function testTaxPercentsDefaultsToDashWhenItemIdMissingFromMap(): void
    {
        $item = $this->makeItem(['item_id' => 99]);

        $columns = getItemDataRow($item, [], []);

        $this->assertEquals('-', $columns['tax_percents']);
    }

    public function testDefinitionNamesPassedThroughToExpandAttributeValues(): void
    {
        $item = $this->makeItem(['item_id' => 7]);
        $item->attribute_values = '3_Red';
        $item->attribute_dtvalues = '';
        $item->attribute_dvalues = '';

        $definitionNames = [
            3 => ['name' => 'Color', 'type' => TEXT],
        ];

        $columns = getItemDataRow($item, $definitionNames, []);

        $this->assertArrayHasKey(3, $columns);
        $this->assertEquals('Red', $columns[3]);
    }

    public function testPackNameAppendedToItemName(): void
    {
        $this->injectSettings(['multi_pack_enabled' => 1]);

        $item = $this->makeItem(['name' => 'Base Item', 'pack_name' => 'Pack of 6']);

        $columns = getItemDataRow($item, [], []);

        $this->assertEquals('Base Item' . NAME_SEPARATOR . 'Pack of 6', $columns['name']);
    }
}
