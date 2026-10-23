<?php

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\ExportFixtureTrait;
use Tests\Support\OsposConfigFixtureTrait;

class tabular_helperTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use ExportFixtureTrait;
    use OsposConfigFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../app/Helpers/tabular_helper.php';
        parent::setUp();

        $this->injectOsposSettings([
            'invoice_enable'            => false,
            'use_destination_based_tax' => false,
            'multi_pack_enabled'        => false,
            'number_locale'             => 'en_US',
            'thousands_separator'       => ',',
            'currency_symbol'           => '$',
            'quantity_decimals'         => 4,
            'currency_decimals'         => 2,
        ]);
    }

    protected function tearDown(): void
    {
        Factories::reset();
        parent::tearDown();
    }

    public function testSalesExportHeaders_ReturnsBaseColumnsWhenInvoiceDisabled(): void
    {
        $keys = array_map('array_key_first', salesExportHeaders());

        $this->assertSame(
            ['sale_id', 'sale_time', 'customer_name', 'amount_due', 'amount_tendered', 'change_due', 'payment_type'],
            $keys
        );
    }

    public function testSalesExportHeaders_AppendsInvoiceNumberWhenInvoiceEnabled(): void
    {
        $ospos = config(OSPOS::class);
        $ospos->settings['invoice_enable'] = true;

        $keys = array_map('array_key_first', salesExportHeaders());

        $this->assertSame(
            ['sale_id', 'sale_time', 'customer_name', 'amount_due', 'amount_tendered', 'change_due', 'payment_type', 'invoice_number'],
            $keys
        );
    }

    public function testGetSaleExportRow_MapsRawFieldsFromSaleObject(): void
    {
        $sale = $this->saleExportFixture();

        $row = getSaleExportRow($sale);

        $this->assertSame(
            [
                'sale_id'         => 1,
                'sale_time'       => '2024-01-15 10:30:00',
                'customer_name'   => 'Jane Doe',
                'amount_due'      => '10.00',
                'amount_tendered' => '20.00',
                'change_due'      => '10.00',
                'payment_type'    => 'Cash',
            ],
            $row
        );
    }

    public function testGetSaleExportRow_IncludesInvoiceNumberWhenInvoiceEnabled(): void
    {
        $ospos = config(OSPOS::class);
        $ospos->settings['invoice_enable'] = true;
        $sale = $this->saleExportFixture();

        $row = getSaleExportRow($sale);

        $this->assertSame('INV-1', $row['invoice_number']);
    }

    public function testEmployeeExportHeaders_ReturnsPersonColumnSet(): void
    {
        $keys = array_map('array_key_first', employeeExportHeaders());

        $this->assertSame(
            ['people.person_id', 'last_name', 'first_name', 'email', 'phone_number'],
            $keys
        );
    }

    public function testGetEmployeeExportRow_MapsRawFieldsFromPersonObject(): void
    {
        $person = $this->employeeExportFixture();

        $row = getEmployeeExportRow($person);

        $this->assertSame(
            [
                'people.person_id' => 1,
                'last_name'        => 'Smith',
                'first_name'       => 'John',
                'email'            => 'john@example.com',
                'phone_number'     => '555-1234',
                'address_1'        => '123 Main St',
                'address_2'        => '',
                'city'             => 'Springfield',
                'state'            => 'IL',
                'zip'              => '62701',
                'country'          => 'US',
                'comments'         => '',
            ],
            $row
        );
    }

    public function testCustomerExportHeaders_ReturnsCustomerColumnSet(): void
    {
        $keys = array_map('array_key_first', customerExportHeaders());

        $this->assertSame(
            ['people.person_id', 'last_name', 'first_name', 'email', 'phone_number', 'total'],
            $keys
        );
    }

    public function testGetCustomerExportRow_MapsRawFieldsFromPersonAndStatsObjects(): void
    {
        $person = $this->customerExportFixture();
        $stats = $this->customerStatsFixture();

        $row = getCustomerExportRow($person, $stats);

        $this->assertSame(
            [
                'people.person_id' => 1,
                'last_name'        => 'Doe',
                'first_name'       => 'Jane',
                'email'            => 'jane@example.com',
                'phone_number'     => '555-5678',
                'total'            => '150.00',
                'employee_id'      => 2,
            ],
            $row
        );
    }

    public function testSupplierExportHeaders_ReturnsSupplierColumnSet(): void
    {
        $keys = array_map('array_key_first', supplierExportHeaders());

        $this->assertSame(
            ['people.person_id', 'company_name', 'agency_name', 'category', 'last_name', 'first_name', 'email', 'phone_number'],
            $keys
        );
    }

    public function testGetSupplierExportRow_MapsRawFieldsFromSupplierObject(): void
    {
        $supplier = $this->supplierExportFixture();

        $row = getSupplierExportRow($supplier);

        $this->assertSame(
            [
                'people.person_id' => 1,
                'company_name'     => 'Acme Corp',
                'agency_name'      => 'Acme Agency',
                'category'         => 'Wholesale',
                'last_name'        => 'Roe',
                'first_name'       => 'Richard',
                'email'            => 'richard@acme.com',
                'phone_number'     => '555-9999',
            ],
            $row
        );
    }

    public function testGetSupplierExportRow_DecodesHtmlEntitiesInCompanyName(): void
    {
        $supplier = $this->supplierExportFixture(['company_name' => 'Acme &amp; Co']);

        $row = getSupplierExportRow($supplier);

        $this->assertSame('Acme & Co', $row['company_name']);
    }

    public function testItemExportHeaders_ReturnsBaseColumnsWhenNoAttributesDefined(): void
    {
        $keys = array_map('array_key_first', itemExportHeaders());

        $this->assertSame(
            ['items.item_id', 'item_number', 'name', 'category', 'company_name', 'cost_price', 'unit_price', 'quantity', 'tax_percents'],
            $keys
        );
    }

    public function testItemExportHeaders_UsesTaxCategoryLabelWhenDestinationBasedTaxEnabled(): void
    {
        $ospos = config(OSPOS::class);
        $ospos->settings['use_destination_based_tax'] = true;

        $headers = itemExportHeaders();
        $taxHeader = array_values(array_filter($headers, fn ($h) => array_key_first($h) === 'tax_percents'))[0];

        $this->assertSame(lang('Items.tax_category'), $taxHeader['tax_percents']);
    }

    public function testGetItemExportRow_MapsRawFieldsFromItemObjectWithNoAttributes(): void
    {
        $item = $this->itemExportFixture();

        $row = getItemExportRow($item, [], []);

        $this->assertSame(
            [
                'item_id'               => 1,
                'item_number'           => 'ITEM-001',
                'name'                  => 'Widget',
                'category'              => 'Hardware',
                'company_name'          => 'Acme Corp',
                'cost_price'            => '5.00',
                'unit_price'            => '10.00',
                'quantity'              => '25',
                'tax_percents'          => '',
                'description'           => 'A widget',
                'allow_alt_description' => 0,
                'is_serialized'         => 0,
            ],
            $row
        );
    }

    public function testGetItemExportRow_AppendsNameSeparatorAndPackNameWhenMultiPackEnabled(): void
    {
        $ospos = config(OSPOS::class);
        $ospos->settings['multi_pack_enabled'] = true;
        $item = $this->itemExportFixture(['pack_name' => 'Case of 6']);

        $row = getItemExportRow($item, [], []);

        $this->assertSame('Widget' . NAME_SEPARATOR . 'Case of 6', $row['name']);
    }

    public function testGetItemExportRow_UsesPrecomputedTaxPercentForItemId(): void
    {
        $item = $this->itemExportFixture();

        $row = getItemExportRow($item, [], [1 => '7.25%']);

        $this->assertSame('7.25%', $row['tax_percents']);
    }

    public function testGetItemExportRow_AddsAttributeColumnsWhenDefinitionNamesProvided(): void
    {
        $item = $this->itemExportFixture(['attribute_values' => '5_Red']);
        $definitionNames = [5 => ['name' => 'Color', 'type' => TEXT]];

        $row = getItemExportRow($item, $definitionNames, []);

        $this->assertArrayHasKey('attribute_Color', $row);
        $this->assertSame('Red', $row['attribute_Color']);
    }

    public function testGiftcardExportHeaders_ReturnsGiftcardColumnSet(): void
    {
        $keys = array_map('array_key_first', giftcardExportHeaders());

        $this->assertSame(
            ['giftcard_id', 'last_name', 'first_name', 'giftcard_number', 'value'],
            $keys
        );
    }

    public function testGetGiftcardExportRow_MapsRawFieldsFromGiftcardObject(): void
    {
        $giftcard = $this->giftcardExportFixture();

        $row = getGiftcardExportRow($giftcard);

        $this->assertSame(
            [
                'giftcard_id'     => 1,
                'last_name'       => 'Doe',
                'first_name'      => 'Jane',
                'giftcard_number' => 'GC-001',
                'value'           => '25.00',
            ],
            $row
        );
    }

    public function testItemKitExportHeaders_ReturnsItemKitColumnSet(): void
    {
        $keys = array_map('array_key_first', itemKitExportHeaders());

        $this->assertSame(
            ['item_kit_id', 'item_kit_number', 'name', 'description', 'total_cost_price', 'total_unit_price'],
            $keys
        );
    }

    public function testGetItemKitExportRow_MapsRawFieldsFromItemKitObject(): void
    {
        $itemKit = $this->itemKitExportFixture();

        $row = getItemKitExportRow($itemKit);

        $this->assertSame(
            [
                'item_kit_id'      => 1,
                'item_kit_number'  => 'KIT-001',
                'name'             => 'Starter Kit',
                'description'      => 'A starter kit',
                'total_cost_price' => '15.00',
                'total_unit_price' => '30.00',
            ],
            $row
        );
    }

    public function testAttributeExportHeaders_ReturnsAttributeDefinitionColumnSet(): void
    {
        $keys = array_map('array_key_first', attributeExportHeaders());

        $this->assertSame(
            ['definition_id', 'definition_name', 'definition_type', 'definition_flags', 'definition_group'],
            $keys
        );
    }

    public function testGetAttributeExportRow_KeepsRawFlagsSeparateFromResolvedLabel(): void
    {
        $attributeRow = $this->attributeExportFixture();

        $row = getAttributeExportRow($attributeRow, 1);

        $this->assertSame(1, $row['definition_flags_raw']);
        $this->assertSame(implode(', ', $attributeRow->definition_flags), $row['definition_flags']);
    }

    public function testGetAttributeExportRow_MapsRemainingRawFieldsFromAttributeRowObject(): void
    {
        $attributeRow = $this->attributeExportFixture();

        $row = getAttributeExportRow($attributeRow, 1);

        $this->assertSame(1, $row['definition_id']);
        $this->assertSame('Color', $row['definition_name']);
        $this->assertSame('TEXT', $row['definition_type']);
        $this->assertSame('', $row['definition_group']);
    }

    public function testExpenseCategoryExportHeaders_ReturnsExpenseCategoryColumnSet(): void
    {
        $keys = array_map('array_key_first', expenseCategoryExportHeaders());

        $this->assertSame(
            ['expense_category_id', 'category_name', 'category_description'],
            $keys
        );
    }

    public function testGetExpenseCategoryExportRow_MapsRawFieldsFromExpenseCategoryObject(): void
    {
        $expenseCategory = $this->expenseCategoryExportFixture();

        $row = getExpenseCategoryExportRow($expenseCategory);

        $this->assertSame(
            [
                'expense_category_id'  => 1,
                'category_name'        => 'Utilities',
                'category_description' => 'Utility bills',
            ],
            $row
        );
    }

    public function testExpenseExportHeaders_ReturnsExpenseColumnSet(): void
    {
        $keys = array_map('array_key_first', expenseExportHeaders());

        $this->assertSame(
            ['expense_id', 'date', 'supplier_name', 'supplier_tax_code', 'amount', 'tax_amount', 'payment_type', 'category_name', 'description', 'created_by'],
            $keys
        );
    }

    public function testGetExpenseExportRow_MapsRawFieldsFromExpenseObject(): void
    {
        $expense = $this->expenseExportFixture();

        $row = getExpenseExportRow($expense);

        $this->assertSame(
            [
                'expense_id'          => 1,
                'date'                => '2024-01-15 00:00:00',
                'supplier_name'       => 'Acme Corp',
                'supplier_tax_code'   => 'TX1',
                'amount'              => '100.00',
                'tax_amount'          => '10.00',
                'payment_type'        => 'Cash',
                'category_name'       => 'Utilities',
                'description'         => 'Monthly bill',
                'created_by'          => 'John Smith',
                'employee_id'         => 2,
                'expense_category_id' => 1,
            ],
            $row
        );
    }

    public function testCashupExportHeaders_ReturnsCashupColumnSet(): void
    {
        $keys = array_map('array_key_first', cashupExportHeaders());

        $this->assertSame(
            [
                'cashup_id', 'open_date', 'open_employee_id', 'open_amount_cash', 'transfer_amount_cash',
                'close_date', 'close_employee_id', 'closed_amount_cash', 'note', 'closed_amount_due',
                'closed_amount_card', 'closed_amount_check', 'closed_amount_total',
            ],
            $keys
        );
    }

    public function testGetCashupExportRow_KeepsRawEmployeeIdsSeparateFromConcatenatedNames(): void
    {
        $cashUp = $this->cashupExportFixture();

        $row = getCashupExportRow($cashUp);

        $this->assertSame(2, $row['open_employee_id_raw']);
        $this->assertSame('John Smith', $row['open_employee_id']);
        $this->assertSame(2, $row['close_employee_id_raw']);
        $this->assertSame('John Smith', $row['close_employee_id']);
    }

    public function testGetCashupExportRow_MapsRemainingRawFieldsFromCashupObject(): void
    {
        $cashUp = $this->cashupExportFixture();

        $row = getCashupExportRow($cashUp);

        $this->assertSame(
            [
                'cashup_id'             => 1,
                'open_date'             => '2024-01-15 08:00:00',
                'open_employee_id'      => 'John Smith',
                'open_employee_id_raw'  => 2,
                'open_amount_cash'      => '100.00',
                'transfer_amount_cash'  => '0.00',
                'close_date'            => '2024-01-15 18:00:00',
                'close_employee_id'     => 'John Smith',
                'close_employee_id_raw' => 2,
                'closed_amount_cash'    => '500.00',
                'note'                  => 1,
                'description'           => 'End of day',
                'closed_amount_due'     => '0.00',
                'closed_amount_card'    => '200.00',
                'closed_amount_check'   => '0.00',
                'closed_amount_total'   => '400.00',
            ],
            $row
        );
    }

    public function testGetCashupExportRow_RequiresTruthyNoteValueToEncodeAsOne(): void
    {
        $cashUp = $this->cashupExportFixture(['note' => 0]);

        $row = getCashupExportRow($cashUp);

        $this->assertSame(0, $row['note']);
    }

    public function testExpandAttributeValues_FormatsDecimalAttributeWhenRawFalse(): void
    {
        $definitionNames = [5 => ['name' => 'Weight', 'type' => DECIMAL]];
        $row = ['attribute_values' => '5_1234.5'];

        $result = expandAttributeValues($definitionNames, $row);

        $this->assertSame(to_decimals('1234.5'), $result[5]);
    }

    public function testExpandAttributeValues_SkipsFormattingDecimalAttributeWhenRawTrue(): void
    {
        $definitionNames = [5 => ['name' => 'Weight', 'type' => DECIMAL]];
        $row = ['attribute_values' => '5_1234.5'];

        $result = expandAttributeValues($definitionNames, $row, raw: true);

        $this->assertSame('1234.5', $result[5]);
    }

    public function testExpandAttributeValues_ReadsFromAttributeDtvaluesAndDvaluesKeys(): void
    {
        $definitionNames = [5 => ['name' => 'Expiry', 'type' => TEXT], 6 => ['name' => 'Rating', 'type' => TEXT]];
        $row = ['attribute_dtvalues' => '5_2024-01-01', 'attribute_dvalues' => '6_9001'];

        $result = expandAttributeValues($definitionNames, $row);

        $this->assertSame('2024-01-01', $result[5]);
        $this->assertSame('9001', $result[6]);
    }

    public function testExpandAttributeValues_ReturnsEmptyStringWhenDefinitionHasNoValue(): void
    {
        $definitionNames = [5 => ['name' => 'Color', 'type' => TEXT]];
        $row = [];

        $result = expandAttributeValues($definitionNames, $row);

        $this->assertSame('', $result[5]);
    }
}
