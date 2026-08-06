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
use Config\OSPOS;

class Sale_libPaymentTest extends CIUnitTestCase
{
    private Sale_lib $saleLib;

    protected function setUp(): void
    {
        parent::setUp();

        // Inject mock OSPOS config so Sale_lib constructor and helpers don't need real settings
        $ospos           = new OSPOS();
        $ospos->settings = [
            'cash_rounding_code' => '',
            'cash_decimals'      => 2,
            'currency_decimals'  => 2,
            'tax_decimals'       => 2,
            'quantity_decimals'  => 2,
        ];
        Factories::injectMock('config', OSPOS::class, $ospos);

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
        Factories::reset();
        parent::tearDown();
    }

    // ========== getPayments / setPayments ==========

    public function testGetPaymentsReturnsEmptyArrayInitially(): void
    {
        $payments = $this->saleLib->getPayments();
        $this->assertIsArray($payments);
        $this->assertEmpty($payments);
    }

    public function testSetPaymentsPersistsToSession(): void
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

    // ========== addPayment ==========

    public function testAddPaymentCreatesNewEntry(): void
    {
        $this->saleLib->addPayment('credit', '25.00', 'ABC123');

        $payments = $this->saleLib->getPayments();
        $this->assertArrayHasKey('credit', $payments);
        $this->assertSame('credit', $payments['credit']['payment_type']);
        $this->assertSame('25.00', $payments['credit']['payment_amount']);
        $this->assertSame(0, $payments['credit']['cash_refund']);
        $this->assertSame(CASH_ADJUSTMENT_FALSE, $payments['credit']['cash_adjustment']);
    }

    public function testAddPaymentStoresReferenceCode(): void
    {
        $this->saleLib->addPayment('debit', '50.00', 'REF9876');

        $payments = $this->saleLib->getPayments();
        $this->assertSame('REF9876', $payments['debit']['reference_code']);
    }

    public function testAddPaymentNullReferenceCodeStoredAsNull(): void
    {
        $this->saleLib->addPayment('cash', '15.00');

        $payments = $this->saleLib->getPayments();
        $this->assertNull($payments['cash']['reference_code']);
    }

    public function testAddPaymentAccumulatesAmountForExistingId(): void
    {
        $this->saleLib->addPayment('credit', '10.00', 'REF001');
        $this->saleLib->addPayment('credit', '5.00', 'REF001');

        $payments = $this->saleLib->getPayments();
        // bcadd strips trailing zeros: '10.00' + '5.00' = '15'
        $this->assertSame('15', $payments['credit']['payment_amount']);
    }

    public function testAddPaymentCashAdjustmentFlagStored(): void
    {
        $this->saleLib->addPayment('cash_adjustment', '0.05', null, CASH_ADJUSTMENT_TRUE);

        $payments = $this->saleLib->getPayments();
        $this->assertSame(CASH_ADJUSTMENT_TRUE, $payments['cash_adjustment']['cash_adjustment']);
    }

    public function testAddPaymentMultipleDistinctTypesAllStored(): void
    {
        $this->saleLib->addPayment('credit', '30.00', 'REF1');
        $this->saleLib->addPayment('debit', '20.00', 'REF2');

        $payments = $this->saleLib->getPayments();
        $this->assertCount(2, $payments);
        $this->assertArrayHasKey('credit', $payments);
        $this->assertArrayHasKey('debit', $payments);
    }

    // ========== edit_payment ==========

    public function testEditPaymentUpdatesAmount(): void
    {
        $this->saleLib->addPayment('credit', '10.00', 'REF001');
        $result = $this->saleLib->edit_payment('credit', 99.99);

        $this->assertTrue($result);
        $payments = $this->saleLib->getPayments();
        $this->assertSame(99.99, $payments['credit']['payment_amount']);
    }

    public function testEditPaymentReturnsFalseForMissingId(): void
    {
        $result = $this->saleLib->edit_payment('nonexistent', 10.00);
        $this->assertFalse($result);
    }

    // ========== delete_payment ==========

    public function testDeletePaymentRemovesEntry(): void
    {
        $this->saleLib->addPayment('credit', '25.00', 'REF999');
        $this->saleLib->delete_payment('credit');

        $payments = $this->saleLib->getPayments();
        $this->assertArrayNotHasKey('credit', $payments);
    }

    public function testDeletePaymentLeavesOtherEntriesIntact(): void
    {
        $this->saleLib->addPayment('credit', '25.00', 'REF1');
        $this->saleLib->addPayment('debit', '10.00', 'REF2');
        $this->saleLib->delete_payment('credit');

        $payments = $this->saleLib->getPayments();
        $this->assertArrayNotHasKey('credit', $payments);
        $this->assertArrayHasKey('debit', $payments);
    }
}
