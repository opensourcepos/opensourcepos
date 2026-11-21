<?php

namespace Tests\Libraries;

use App\Libraries\Sale_lib;
use App\Models\Attribute;
use App\Models\Customer;
use App\Models\Dinner_table;
use App\Models\Item;
use App\Models\Item_kit_items;
use App\Models\Item_quantity;
use App\Models\Item_taxes;
use App\Models\Sale;
use App\Models\Stock_location;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\OsposConfigFixtureTrait;

class Sale_libTest extends CIUnitTestCase
{
    use OsposConfigFixtureTrait;

    private Sale_lib $saleLib;

    protected int $priorBcscale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->priorBcscale = bcscale();
        bcscale(0);

        $this->injectOsposSettings([
            'cash_rounding_code' => '',
            'cash_decimals'      => 2,
            'currency_decimals'  => 2,
            'tax_decimals'       => 2,
            'quantity_decimals'  => 2,
        ]);

        // Inject stub models so model() calls in Sale_lib::__construct() skip DB
        $stubMethods = ['__construct'];
        foreach ([
            Attribute::class,
            Customer::class,
            Dinner_table::class,
            Item::class,
            Item_kit_items::class,
            Item_quantity::class,
            Item_taxes::class,
            Sale::class,
            Stock_location::class,
        ] as $modelClass) {
            $mock = $this->getMockBuilder($modelClass)
                ->disableOriginalConstructor()
                ->getMock();
            Factories::injectMock('models', $modelClass, $mock);
        }

        session()->destroy();
        $this->saleLib = new Sale_lib();
    }

    protected function tearDown(): void
    {
        bcscale($this->priorBcscale);
        Factories::reset();
        parent::tearDown();
    }

    public function testGetPayments_ReturnsEmptyArrayInitially(): void
    {
        $payments = $this->saleLib->getPayments();
        $this->assertIsArray($payments);
        $this->assertEmpty($payments);
    }

    public function testSetPayments_PersistsToSession(): void
    {
        $data = [
            'cash' => [
                'payment_type'    => 'cash',
                'payment_amount'  => '10.00',
                'cash_refund'     => 0,
                'cash_adjustment' => CASH_ADJUSTMENT_FALSE,
                'reference_code'  => null,
            ]
        ];
        $this->saleLib->setPayments($data);
        $this->assertSame($data, $this->saleLib->getPayments());
    }

    public function testAddPayment_CreatesNewEntry(): void
    {
        $this->saleLib->addPayment('credit', '25.00', 'ABC123');

        $payments = $this->saleLib->getPayments();
        $this->assertArrayHasKey('credit', $payments);
        $this->assertSame('credit', $payments['credit']['payment_type']);
        $this->assertSame('25.00', $payments['credit']['payment_amount']);
        $this->assertSame(0, $payments['credit']['cash_refund']);
        $this->assertSame(CASH_ADJUSTMENT_FALSE, $payments['credit']['cash_adjustment']);
    }

    public function testAddPayment_StoresReferenceCode(): void
    {
        $this->saleLib->addPayment('debit', '50.00', 'REF9876');

        $payments = $this->saleLib->getPayments();
        $this->assertSame('REF9876', $payments['debit']['reference_code']);
    }

    public function testAddPayment_StoresNullReferenceCodeAsNull(): void
    {
        $this->saleLib->addPayment('cash', '15.00');

        $payments = $this->saleLib->getPayments();
        $this->assertNull($payments['cash']['reference_code']);
    }

    public function testAddPayment_AccumulatesAmountForExistingId(): void
    {
        $this->saleLib->addPayment('credit', '10.00', 'REF001');
        $this->saleLib->addPayment('credit', '5.00', 'REF001');

        $payments = $this->saleLib->getPayments();
        $this->assertSame('15', $payments['credit']['payment_amount']);
    }

    public function testAddPayment_StoresCashAdjustmentFlag(): void
    {
        $this->saleLib->addPayment('cash_adjustment', '0.05', null, CASH_ADJUSTMENT_TRUE);

        $payments = $this->saleLib->getPayments();
        $this->assertSame(CASH_ADJUSTMENT_TRUE, $payments['cash_adjustment']['cash_adjustment']);
    }

    public function testAddPayment_StoresMultipleDistinctTypes(): void
    {
        $this->saleLib->addPayment('credit', '30.00', 'REF1');
        $this->saleLib->addPayment('debit', '20.00', 'REF2');

        $payments = $this->saleLib->getPayments();
        $this->assertCount(2, $payments);
        $this->assertArrayHasKey('credit', $payments);
        $this->assertArrayHasKey('debit', $payments);
    }

    public function testEditPayment_UpdatesAmount(): void
    {
        $this->saleLib->addPayment('credit', '10.00', 'REF001');
        $result = $this->saleLib->edit_payment('credit', 99.99);

        $this->assertTrue($result);
        $payments = $this->saleLib->getPayments();
        $this->assertSame(99.99, $payments['credit']['payment_amount']);
    }

    public function testEditPayment_ReturnsFalseForMissingId(): void
    {
        $result = $this->saleLib->edit_payment('nonexistent', 10.00);
        $this->assertFalse($result);
    }

    public function testDeletePayment_RemovesEntry(): void
    {
        $this->saleLib->addPayment('credit', '25.00', 'REF999');
        $this->saleLib->delete_payment('credit');

        $payments = $this->saleLib->getPayments();
        $this->assertArrayNotHasKey('credit', $payments);
    }

    public function testDeletePayment_LeavesOtherEntriesIntact(): void
    {
        $this->saleLib->addPayment('credit', '25.00', 'REF1');
        $this->saleLib->addPayment('debit', '10.00', 'REF2');
        $this->saleLib->delete_payment('credit');

        $payments = $this->saleLib->getPayments();
        $this->assertArrayNotHasKey('credit', $payments);
        $this->assertArrayHasKey('debit', $payments);
    }

    public function testSetSaleLocation_FallsBackToDefaultWhenLocationDisallowed(): void
    {
        $stockLocationMock = $this->getMockBuilder(Stock_location::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockLocationMock->method('is_allowed_location')
            ->willReturnCallback(static fn (int $loc, string $mod): bool => $loc === 1 && $mod === 'sales');
        $stockLocationMock->method('getDefaultLocationId')
            ->with('sales')
            ->willReturn(1);

        Factories::injectMock('models', Stock_location::class, $stockLocationMock);
        $saleLib = new Sale_lib();

        $saleLib->set_sale_location(999);
        $this->assertSame(1, $saleLib->get_sale_location());

        $saleLib->set_sale_location(1);
        $this->assertSame(1, $saleLib->get_sale_location());
    }

    public function testIsCustomerTaxable_ReturnsTrueWhenNoCustomerSelected(): void
    {
        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerMock->method('getInfo')
            ->willReturn((object) ['taxable' => 0]);

        Factories::injectMock('models', Customer::class, $customerMock);
        $saleLib = new Sale_lib();

        $this->assertTrue($saleLib->is_customer_taxable());

        $saleLib->set_customer(42);
        $this->assertFalse($saleLib->is_customer_taxable());
    }

    public function testGetQuantitySold_ReturnsZeroWhenPriceIsZero(): void
    {
        $this->assertSame('0', $this->saleLib->get_quantity_sold('10.00', '0'));
        $this->assertSame('0', $this->saleLib->get_quantity_sold('10.00', '0.00'));
    }

    public function testEditItem_ReturnsTrueWhenLineExists(): void
    {
        $this->saleLib->set_cart([
            1 => [
                'line'             => 1,
                'description'      => 'Old',
                'serialnumber'     => '',
                'quantity'         => '1',
                'discount'         => '0',
                'discount_type'    => 0,
                'price'            => '10.00',
                'total'            => '10.00',
                'discounted_total' => '10.00',
            ],
        ]);

        $this->assertTrue($this->saleLib->edit_item('1', 'New', 'SN1', '2', '0', '0', '10.00'));
        $this->assertFalse($this->saleLib->edit_item('999', 'New', 'SN1', '2', '0', '0', '10.00'));
    }

    public function testEditItem_CastsDiscountTypeToInt(): void
    {
        $this->saleLib->set_cart([
            1 => [
                'line'             => 1,
                'description'      => 'Item',
                'serialnumber'     => '',
                'quantity'         => '1',
                'discount'         => '5',
                'discount_type'    => PERCENT,
                'price'            => '20.00',
                'total'            => '20.00',
                'discounted_total' => '19.00',
            ],
        ]);

        $this->assertTrue($this->saleLib->edit_item('1', 'Item', '', '1', '5', '1', '20.00'));
        $this->assertSame(FIXED, $this->saleLib->get_cart()[1]['discount_type']);

        $this->assertTrue($this->saleLib->edit_item('1', 'Item', '', '1', '5', '0', '20.00'));
        $this->assertSame(PERCENT, $this->saleLib->get_cart()[1]['discount_type']);

        $this->assertTrue($this->saleLib->edit_item('1', 'Item', '', '1', '5', '1', '20.00'));
        $this->assertTrue($this->saleLib->edit_item('1', 'Item', '', '1', '5', null, '20.00'));
        $this->assertSame(FIXED, $this->saleLib->get_cart()[1]['discount_type']);
    }

    public function testGetSaleId_ReturnsNewEntryWhenUnset(): void
    {
        $this->assertSame(NEW_ENTRY, $this->saleLib->get_sale_id());
    }

    public function testGetSuspendedId_ReturnsNewEntryWhenUnset(): void
    {
        $this->assertSame(NEW_ENTRY, $this->saleLib->get_suspended_id());
    }

    public function testSetInvoiceNumber_AcceptsStringAndNull(): void
    {
        $this->saleLib->set_invoice_number('INV-2026-01');
        $this->assertSame('INV-2026-01', $this->saleLib->get_invoice_number());

        $this->saleLib->set_invoice_number(null);
        $this->assertNull($this->saleLib->get_invoice_number());
    }
}
