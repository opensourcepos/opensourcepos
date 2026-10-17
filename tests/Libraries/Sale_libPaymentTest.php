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
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\OSPOS;
use stdClass;

/**
 * @internal
 */
final class Sale_libPaymentTest extends CIUnitTestCase
{
    private Sale_lib $saleLib;
    protected int $priorBcscale;

    protected function setUp(): void
    {
        parent::setUp();

        // The app sets a global bcscale() from app config on config load
        // (see app/Events/Load_config.php), and whichever value a prior test
        // class triggered leaks into bcadd/bcmul here. Pin a known scale so the
        // accumulation math in these tests is deterministic regardless of suite
        // order; restore it in tearDown.
        $this->priorBcscale = bcscale();
        bcscale(0);

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
        bcscale($this->priorBcscale);
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
            ],
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

    public function testIsPrintAfterSaleAcceptsBooleanSessionValue(): void
    {
        $this->saleLib->set_print_after_sale(true);
        $this->assertTrue($this->saleLib->is_print_after_sale());

        $this->saleLib->set_print_after_sale(false);
        $this->assertFalse($this->saleLib->is_print_after_sale());
    }

    public function testApplyCustomerDiscountAppliesDiscountToStringZeroDiscount(): void
    {
        $cart = [
            1 => [
                'line'             => 1,
                'item_id'          => 1,
                'price'            => '100.00',
                'quantity'         => '1',
                'discount'         => '0.00',
                'discount_type'    => PERCENT,
                'total'            => '100.00',
                'discounted_total' => '100.00',
            ],
            2 => [
                'line'             => 2,
                'item_id'          => 2,
                'price'            => '100.00',
                'quantity'         => '1',
                'discount'         => '5.00',
                'discount_type'    => PERCENT,
                'total'            => '95.00',
                'discounted_total' => '95.00',
            ],
        ];

        $this->saleLib->set_cart($cart);
        $this->saleLib->apply_customer_discount('10', PERCENT);

        $updatedCart = $this->saleLib->get_cart();
        $this->assertSame('10', (string) $updatedCart[1]['discount']);
        $this->assertSame('5.00', (string) $updatedCart[2]['discount']);
    }

    public function testResetCashRoundingAcceptsStringRoundingMode(): void
    {
        $ospos           = new OSPOS();
        $ospos->settings = [
            'cash_rounding_code' => '7', // Rounding_mode::HALF_FIVE stored as string
            'cash_decimals'      => 2,
            'currency_decimals'  => 2,
            'tax_decimals'       => 2,
            'quantity_decimals'  => 2,
        ];
        Factories::injectMock('config', OSPOS::class, $ospos);

        $saleLib = new Sale_lib();
        $this->assertSame(1, $saleLib->reset_cash_rounding());
    }

    public function testRemoveTempItemsDeletesTemporaryItemsWithNumericType(): void
    {
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())->method('delete')->with(42);
        Factories::injectMock('models', Item::class, $itemMock);

        $saleLib = new Sale_lib();
        $cart    = [
            1 => [
                'line'      => 1,
                'item_id'   => 42,
                'item_type' => (string) ITEM_TEMP, // ITEM_TEMP as string '3'
            ],
            2 => [
                'line'      => 2,
                'item_id'   => 10,
                'item_type' => '0', // standard item as string
            ],
        ];

        $saleLib->set_cart($cart);
        $saleLib->remove_temp_items();
    }

    public function testAddItemKitAcceptsIntegerKitOptions(): void
    {
        $itemKitItemsMock = $this->createMock(Item_kit_items::class);
        $itemKitItemsMock->method('get_info')->willReturn([
            ['item_id' => 101, 'quantity' => '1'],
        ]);
        Factories::injectMock('models', Item_kit_items::class, $itemKitItemsMock);

        $itemMock                        = $this->createMock(Item::class);
        $itemInfo                        = new stdClass();
        $itemInfo->item_id               = 101;
        $itemInfo->item_type             = 0;
        $itemInfo->stock_type            = HAS_STOCK;
        $itemInfo->unit_price            = '25.00';
        $itemInfo->cost_price            = '15.00';
        $itemInfo->is_serialized         = false;
        $itemInfo->name                  = 'Kit Component';
        $itemInfo->item_number           = 'KIT001';
        $itemInfo->description           = '';
        $itemInfo->allow_alt_description = false;
        $itemInfo->reorder_level         = 0;
        $itemInfo->hsn_code              = '';
        $itemInfo->tax_category_id       = null;
        $itemMock->method('get_info_by_id_or_number')->willReturn($itemInfo);
        Factories::injectMock('models', Item::class, $itemMock);

        $attributeMock              = $this->createMock(Attribute::class);
        $rowObj                     = new stdClass();
        $rowObj->attribute_values   = '';
        $rowObj->attribute_dtvalues = '';
        $queryResult                = $this->createMock(ResultInterface::class);
        $queryResult->method('getRowObject')->willReturn($rowObj);
        $attributeMock->method('get_link_values')->willReturn($queryResult);
        Factories::injectMock('models', Attribute::class, $attributeMock);

        $itemQuantityMock = $this->createMock(Item_quantity::class);
        $qtyObj           = new stdClass();
        $qtyObj->quantity = 10;
        $itemQuantityMock->method('get_item_quantity')->willReturn($qtyObj);
        Factories::injectMock('models', Item_quantity::class, $itemQuantityMock);

        $stockLocationMock = $this->createMock(Stock_location::class);
        $stockLocationMock->method('get_location_name')->willReturn('Default');
        Factories::injectMock('models', Stock_location::class, $stockLocationMock);

        $saleLib      = new Sale_lib();
        $stockWarning = null;
        $result       = $saleLib->add_item_kit('KIT 1', 1, 0.0, '0', PRICE_OPTION_KIT_STOCK, PRINT_ALL, $stockWarning);

        $this->assertTrue($result);
        $cart = $saleLib->get_cart();
        $this->assertCount(1, $cart);
        $this->assertSame('25.00', $cart[1]['price']);
    }
}
