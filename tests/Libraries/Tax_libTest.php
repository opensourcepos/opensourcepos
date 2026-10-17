<?php

namespace Tests\Libraries;

use App\Libraries\Sale_lib;
use App\Libraries\Tax_lib;
use App\Models\Attribute;
use App\Models\Customer;
use App\Models\Dinner_table;
use App\Models\Enums\Rounding_mode;
use App\Models\Item;
use App\Models\Item_kit_items;
use App\Models\Item_quantity;
use App\Models\Item_taxes;
use App\Models\Sale;
use App\Models\Stock_location;
use App\Models\Tax;
use App\Models\Tax_category;
use App\Models\Tax_code;
use App\Models\Tax_jurisdiction;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\OSPOS;
use stdClass;

/**
 * @internal
 */
final class Tax_libTest extends CIUnitTestCase
{
    private Tax_lib $taxLib;
    private Sale_lib $saleLib;
    private $itemTaxesMock;
    private $customerMock;
    private $saleMock;
    private $taxMock;
    private int $priorBcscale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->priorBcscale = bcscale();
        bcscale(4);

        $this->setupConfig(true);
        $this->setupMocks();

        session()->destroy();
        $this->saleLib = new Sale_lib();
        $this->taxLib  = new Tax_lib();
    }

    protected function tearDown(): void
    {
        bcscale($this->priorBcscale);
        Factories::reset();
        parent::tearDown();
    }

    private function setupConfig(bool $taxIncluded = true, bool $useDestinationBasedTax = false): void
    {
        $ospos           = new OSPOS();
        $ospos->settings = [
            'tax_included'                => $taxIncluded ? 1 : 0,
            'use_destination_based_tax'   => $useDestinationBasedTax ? 1 : 0,
            'cash_rounding_code'          => '',
            'cash_decimals'               => 2,
            'currency_decimals'           => 2,
            'tax_decimals'                => 2,
            'quantity_decimals'           => 2,
            'default_tax_category'        => 1,
            'default_tax_code'            => 1,
            'default_sales_discount_type' => 0,
            'default_sales_discount'      => 0,
            'dinner_table_enable'         => 0,
            'customer_reward_enable'      => 0,
            'multi_pack_enabled'          => 0,
        ];
        Factories::injectMock('config', OSPOS::class, $ospos);
    }

    private function setupMocks(): void
    {
        $this->itemTaxesMock = $this->createMock(Item_taxes::class);
        $this->customerMock  = $this->createMock(Customer::class);
        $this->saleMock      = $this->createMock(Sale::class);
        $this->taxMock       = $this->createMock(Tax::class);

        $customerInfo                    = new stdClass();
        $customerInfo->taxable           = 1;
        $customerInfo->city              = 'Mumbai';
        $customerInfo->state             = 'MH';
        $customerInfo->sales_tax_code_id = 1;
        $customerInfo->discount          = '';
        $customerInfo->discount_type     = 0;
        $this->customerMock->method('get_info')->willReturn($customerInfo);

        Factories::injectMock('models', Item_taxes::class, $this->itemTaxesMock);
        Factories::injectMock('models', Customer::class, $this->customerMock);
        Factories::injectMock('models', Sale::class, $this->saleMock);
        Factories::injectMock('models', Tax::class, $this->taxMock);

        foreach ([
            Attribute::class,
            Dinner_table::class,
            Item::class,
            Item_kit_items::class,
            Item_quantity::class,
            Stock_location::class,
            Tax_category::class,
            Tax_code::class,
            Tax_jurisdiction::class,
        ] as $modelClass) {
            $mock = $this->getMockBuilder($modelClass)->disableOriginalConstructor()->getMock();
            Factories::injectMock('models', $modelClass, $mock);
        }
    }

    public function testGetIncludedTaxCalculatesSingleInclusiveTax(): void
    {
        // 20% VAT included in gross price of 120.00 -> tax amount is 20.00
        $taxAmount = $this->taxLib->get_included_tax('1', '120.00', '0', 0, '20');

        $this->assertEqualsWithDelta(20.00, (float) $taxAmount, 0.0001);
    }

    public function testGetIncludedTaxCalculatesComponentWithCombinedRate(): void
    {
        // 9% CGST component of 18% total GST included in gross price of 50.00
        // Expected unrounded: 50 * 9 / 118 = 3.813559...
        $taxAmount = $this->taxLib->get_included_tax('1', '50.00', '0', 0, '9', 2, Rounding_mode::HALF_UP, '18');

        $this->assertEqualsWithDelta(3.813559, (float) $taxAmount, 0.0001);
    }

    public function testGetTaxesCalculatesGstInclusiveWithMultipleTaxes(): void
    {
        // Item gross price: 50.00, CGST 9%, SGST 9%, Combined 18%
        $this->itemTaxesMock->method('get_info')->with(1)->willReturn([
            ['item_id' => 1, 'name' => 'CGST', 'percent' => '9.0000'],
            ['item_id' => 1, 'name' => 'SGST', 'percent' => '9.0000'],
        ]);

        $cart = [
            1 => [
                'item_id'         => 1,
                'line'            => 1,
                'name'            => 'Item A',
                'price'           => '50.00',
                'quantity'        => '1',
                'discount'        => '0',
                'discount_type'   => 0,
                'tax_category_id' => null,
                'print_option'    => 0,
                'stock_type'      => 0,
                'item_type'       => 0,
                'description'     => '',
                'serialnumber'    => '',
            ],
        ];

        $taxDetails = $this->taxLib->get_taxes($cart);
        $taxes      = $taxDetails[0];
        $itemTaxes  = $taxDetails[1];

        $cgstGroup = $taxes['X9-CGST'];
        $sgstGroup = $taxes['X9-SGST'];

        $this->assertSame('3.81', (string) $cgstGroup['sale_tax_amount']);
        $this->assertSame('3.81', (string) $sgstGroup['sale_tax_amount']);

        // Taxable base in sales_taxes should reflect net taxable amount (42.3728...)
        $this->assertEqualsWithDelta(42.3728, (float) $cgstGroup['sale_tax_basis'], 0.001);
        $this->assertEqualsWithDelta(42.3728, (float) $sgstGroup['sale_tax_basis'], 0.001);

        // Verify unrounded item tax amount in item_taxes
        $this->assertEqualsWithDelta(3.8135, (float) $itemTaxes[0]['item_tax_amount'], 0.001);
        $this->assertEqualsWithDelta(3.8135, (float) $itemTaxes[1]['item_tax_amount'], 0.001);

        // Verify totals reconciliation
        $this->saleLib->set_cart($cart);
        $totals = $this->saleLib->get_totals($taxes);
        $this->assertSame('42.38', number_format((float) $totals['subtotal'], 2, '.', ''));
        $this->assertSame('50.00', number_format((float) $totals['total'], 2, '.', ''));
    }

    public function testGetTaxesCalculatesGstExclusiveWithMultipleTaxes(): void
    {
        $this->setupConfig(false);
        $this->taxLib = new Tax_lib();

        $this->itemTaxesMock->method('get_info')->with(1)->willReturn([
            ['item_id' => 1, 'name' => 'CGST', 'percent' => '9.0000'],
            ['item_id' => 1, 'name' => 'SGST', 'percent' => '9.0000'],
        ]);

        $cart = [
            1 => [
                'item_id'         => 1,
                'line'            => 1,
                'name'            => 'Item A',
                'price'           => '50.00',
                'quantity'        => '1',
                'discount'        => '0',
                'discount_type'   => 0,
                'tax_category_id' => null,
                'print_option'    => 0,
                'stock_type'      => 0,
                'item_type'       => 0,
                'description'     => '',
                'serialnumber'    => '',
            ],
        ];

        $taxDetails = $this->taxLib->get_taxes($cart);
        $taxes      = $taxDetails[0];

        $cgstGroup = $taxes['X9-CGST'];
        $sgstGroup = $taxes['X9-SGST'];

        $this->assertSame('4.5', (string) $cgstGroup['sale_tax_amount']);
        $this->assertSame('4.5', (string) $sgstGroup['sale_tax_amount']);
        $this->assertEqualsWithDelta(50.00, (float) $cgstGroup['sale_tax_basis'], 0.001);

        $this->saleLib->set_cart($cart);
        $totals = $this->saleLib->get_totals($taxes);
        $this->assertSame('50.00', number_format((float) $totals['subtotal'], 2, '.', ''));
        $this->assertSame('9.00', number_format((float) $totals['tax_total'], 2, '.', ''));
        $this->assertSame('59.00', number_format((float) $totals['total'], 2, '.', ''));
    }

    public function testGetTaxesCalculatesMultipleItemsWithDifferentInclusiveRates(): void
    {
        // Item 1: 50.00 @ 18% GST (CGST 9% + SGST 9%)
        // Item 2: 100.00 @ 12% GST (CGST 6% + SGST 6%)
        $this->itemTaxesMock->method('get_info')->willReturnCallback(static function ($itemId) {
            if ($itemId === 1) {
                return [
                    ['item_id' => 1, 'name' => 'CGST', 'percent' => '9.0000'],
                    ['item_id' => 1, 'name' => 'SGST', 'percent' => '9.0000'],
                ];
            }

            return [
                ['item_id' => 2, 'name' => 'CGST', 'percent' => '6.0000'],
                ['item_id' => 2, 'name' => 'SGST', 'percent' => '6.0000'],
            ];
        });

        $cart = [
            1 => [
                'item_id'         => 1,
                'line'            => 1,
                'name'            => 'Item 1',
                'price'           => '50.00',
                'quantity'        => '1',
                'discount'        => '0',
                'discount_type'   => 0,
                'tax_category_id' => null,
                'print_option'    => 0,
                'stock_type'      => 0,
                'item_type'       => 0,
                'description'     => '',
                'serialnumber'    => '',
            ],
            2 => [
                'item_id'         => 2,
                'line'            => 2,
                'name'            => 'Item 2',
                'price'           => '100.00',
                'quantity'        => '1',
                'discount'        => '0',
                'discount_type'   => 0,
                'tax_category_id' => null,
                'print_option'    => 0,
                'stock_type'      => 0,
                'item_type'       => 0,
                'description'     => '',
                'serialnumber'    => '',
            ],
        ];

        $taxDetails = $this->taxLib->get_taxes($cart);
        $taxes      = $taxDetails[0];

        // 9% taxes from Item 1
        $this->assertSame('3.81', (string) $taxes['X9-CGST']['sale_tax_amount']);
        $this->assertSame('3.81', (string) $taxes['X9-SGST']['sale_tax_amount']);

        // 6% taxes from Item 2 (100 / 1.12 = 89.2857 -> 89.2857 * 0.06 = 5.3571 -> 5.36)
        $this->assertSame('5.36', (string) $taxes['X6-CGST']['sale_tax_amount']);
        $this->assertSame('5.36', (string) $taxes['X6-SGST']['sale_tax_amount']);

        // Total should remain exactly 150.00
        $this->saleLib->set_cart($cart);
        $totals = $this->saleLib->get_totals($taxes);
        $this->assertSame('150.00', number_format((float) $totals['total'], 2, '.', ''));
        // Subtotal = 150 - (3.81*2 + 5.36*2) = 150 - 18.34 = 131.66
        $this->assertSame('131.66', number_format((float) $totals['subtotal'], 2, '.', ''));
    }

    public function testGetTaxesCalculatesInclusiveTaxWithQuantityAndDiscounts(): void
    {
        $this->itemTaxesMock->method('get_info')->with(1)->willReturn([
            ['item_id' => 1, 'name' => 'CGST', 'percent' => '9.0000'],
            ['item_id' => 1, 'name' => 'SGST', 'percent' => '9.0000'],
        ]);

        // Qty = 2, Gross price = 50.00 -> Item total = 100.00
        // Combined GST = 18%, Taxable base = 100 / 1.18 = 84.7457...
        // CGST = 84.7457... * 0.09 = 7.6271... -> 7.63
        // SGST = 84.7457... * 0.09 = 7.6271... -> 7.63
        $cart = [
            1 => [
                'item_id'         => 1,
                'line'            => 1,
                'name'            => 'Item A',
                'price'           => '50.00',
                'quantity'        => '2',
                'discount'        => '0',
                'discount_type'   => 0,
                'tax_category_id' => null,
                'print_option'    => 0,
                'stock_type'      => 0,
                'item_type'       => 0,
                'description'     => '',
                'serialnumber'    => '',
            ],
        ];

        $taxDetails = $this->taxLib->get_taxes($cart);
        $taxes      = $taxDetails[0];

        $this->assertSame('7.63', (string) $taxes['X9-CGST']['sale_tax_amount']);
        $this->assertSame('7.63', (string) $taxes['X9-SGST']['sale_tax_amount']);

        $this->saleLib->set_cart($cart);
        $totals = $this->saleLib->get_totals($taxes);
        $this->assertSame('100.00', number_format((float) $totals['total'], 2, '.', ''));
        $this->assertSame('84.74', number_format((float) $totals['subtotal'], 2, '.', ''));

        // Case with 10% discount on single item: net total = 45.00
        // Taxable base = 45 / 1.18 = 38.1355...
        // CGST = 38.1355... * 0.09 = 3.4322... -> 3.43
        $cartDiscounted = [
            1 => [
                'item_id'         => 1,
                'line'            => 1,
                'name'            => 'Item A',
                'price'           => '50.00',
                'quantity'        => '1',
                'discount'        => '10',
                'discount_type'   => 0,
                'tax_category_id' => null,
                'print_option'    => 0,
                'stock_type'      => 0,
                'item_type'       => 0,
                'description'     => '',
                'serialnumber'    => '',
            ],
        ];

        $taxDetailsDisc = $this->taxLib->get_taxes($cartDiscounted);
        $taxesDisc      = $taxDetailsDisc[0];

        $this->assertSame('3.43', (string) $taxesDisc['X9-CGST']['sale_tax_amount']);
        $this->assertSame('3.43', (string) $taxesDisc['X9-SGST']['sale_tax_amount']);

        $this->saleLib->set_cart($cartDiscounted);
        $totalsDisc = $this->saleLib->get_totals($taxesDisc);
        $this->assertSame('45.00', number_format((float) $totalsDisc['total'], 2, '.', ''));
        $this->assertSame('38.14', number_format((float) $totalsDisc['subtotal'], 2, '.', ''));
    }

    public function testApplyDestinationTaxCalculatesInclusiveDestinationTaxes(): void
    {
        $this->setupConfig(true, true);
        $this->taxLib = new Tax_lib();

        $this->taxMock->method('get_taxes')->willReturn([
            [
                'tax_rate_id'          => 1,
                'tax_group'            => 'CGST',
                'tax_rate'             => '9.0000',
                'tax_type'             => Tax_lib::TAX_TYPE_INCLUDED,
                'tax_rounding_code'    => Rounding_mode::HALF_UP,
                'tax_group_sequence'   => 1,
                'cascade_sequence'     => 0,
                'rate_jurisdiction_id' => 1,
                'rate_tax_category_id' => 1,
            ],
            [
                'tax_rate_id'          => 2,
                'tax_group'            => 'SGST',
                'tax_rate'             => '9.0000',
                'tax_type'             => Tax_lib::TAX_TYPE_INCLUDED,
                'tax_rounding_code'    => Rounding_mode::HALF_UP,
                'tax_group_sequence'   => 2,
                'cascade_sequence'     => 0,
                'rate_jurisdiction_id' => 2,
                'rate_tax_category_id' => 1,
            ],
        ]);

        $cart = [
            1 => [
                'item_id'         => 1,
                'line'            => 1,
                'name'            => 'Item A',
                'price'           => '50.00',
                'quantity'        => '1',
                'discount'        => '0',
                'discount_type'   => 0,
                'tax_category_id' => 1,
                'print_option'    => 0,
                'stock_type'      => 0,
                'item_type'       => 0,
                'description'     => '',
                'serialnumber'    => '',
            ],
        ];

        $taxDetails = $this->taxLib->get_taxes($cart);
        $taxes      = $taxDetails[0];

        $this->assertSame('3.81', (string) $taxes['X9-CGST']['sale_tax_amount']);
        $this->assertSame('3.81', (string) $taxes['X9-SGST']['sale_tax_amount']);
        $this->assertEqualsWithDelta(42.3728, (float) $taxes['X9-CGST']['sale_tax_basis'], 0.001);

        $this->saleLib->set_cart($cart);
        $totals = $this->saleLib->get_totals($taxes);
        $this->assertSame('42.38', number_format((float) $totals['subtotal'], 2, '.', ''));
        $this->assertSame('50.00', number_format((float) $totals['total'], 2, '.', ''));
    }

    public function testGetItemTotalTaxExclusiveReturnsAccurateTaxableBase(): void
    {
        $this->itemTaxesMock->method('get_info')->with(1)->willReturn([
            ['item_id' => 1, 'name' => 'CGST', 'percent' => '9.0000'],
            ['item_id' => 1, 'name' => 'SGST', 'percent' => '9.0000'],
        ]);

        $taxExclusiveTotal = $this->saleLib->get_item_total_tax_exclusive(1, '1', '50.00', '0', 0, false);

        // Expected 50 / 1.18 = 42.3728...
        $this->assertEqualsWithDelta(42.3728, (float) $taxExclusiveTotal, 0.001);
    }
}
