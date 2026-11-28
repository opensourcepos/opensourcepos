<?php

namespace Tests\Controllers;

use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Services;
use App\Models\Employee;
use Config\Database;
use Config\OSPOS;
use DOMDocument;
use DOMXPath;
use Tests\Support\ItemFixtureTrait;
use Tests\Support\EmployeeFixtureTrait;
use Tests\Support\SaleFixtureTrait;

/**
 * Regression tests for the Sales per-endpoint access-control bypass.
 *
 * A cashier holding only the base "sales" grant (no "reports_sales") must
 * not be able to reach the per-sale endpoints that getManage() gates
 * behind reports_sales: getSearch, getRow, getEdit, postSave, getReceipt,
 * getInvoice, getSendPdf, getSendReceipt.
 *
 * Also covers: getSearch() (the AJAX endpoint that
 * supplies every row of the Sales Takings list) was the one sibling that
 * returned the full ledger and was missing the reports_sales check.
 */
class SalesControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use ItemFixtureTrait;
    use EmployeeFixtureTrait;
    use SaleFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private static bool $doneBootstrap = false;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Cashier with bare "sales" + "sales_stock" grants. "sales_stock" is
     * required alongside "sales": Employee::has_module_grant('sales', ...)
     * treats the bare "sales" grant as insufficient once any sales_*
     * submodule permission exists in the permissions table (see
     * has_subpermissions()). Deliberately NOT "reports_sales" — that is the
     * grant this test exercises the gate on.
     */
    protected function createCashierEmployee(): int
    {
        return $this->createEmployee(
            first_name: 'Cashier',
            last_name:  'NoReports',
            email:      'cashier.' . uniqid() . '@test.com',
            username:   'cashier_' . uniqid(),
            grants: [
                ['permission_id' => 'sales', 'menu_group' => 'home'],
                ['permission_id' => 'sales_stock', 'menu_group' => 'home'],
            ],
        );
    }

    protected function createReportsSalesEmployee(): int
    {
        return $this->createEmployee(
            first_name: 'Supervisor',
            last_name:  'WithReports',
            email:      'supervisor.' . uniqid() . '@test.com',
            username:   'supervisor_' . uniqid(),
            grants: [
                ['permission_id' => 'sales', 'menu_group' => 'home'],
                ['permission_id' => 'sales_stock', 'menu_group' => 'home'],
                ['permission_id' => 'reports_sales', 'menu_group' => 'home'],
            ],
        );
    }

    protected function createCashierWithoutChangePriceGrant(): int
    {
        return $this->createEmployee(
            first_name: 'Cashier',
            last_name:  'NoChangePrice',
            email:      'cashier-nochangeprice.' . uniqid() . '@test.com',
            username:   'cashier_nochangeprice_' . uniqid(),
            grants: [
                ['permission_id' => 'sales', 'menu_group' => 'home'],
                ['permission_id' => 'sales_stock', 'menu_group' => 'home'],
            ],
        );
    }

    protected function createCashierWithChangePriceGrant(): int
    {
        return $this->createEmployee(
            first_name: 'Cashier',
            last_name:  'ChangePrice',
            email:      'cashier-changeprice.' . uniqid() . '@test.com',
            username:   'cashier_changeprice_' . uniqid(),
            grants: [
                ['permission_id' => 'sales', 'menu_group' => 'home'],
                ['permission_id' => 'sales_stock', 'menu_group' => 'home'],
                ['permission_id' => 'sales_change_price', 'menu_group' => 'home'],
            ],
        );
    }

    protected function loginAs(int $personId): void
    {
        $this->withSession([
            'person_id'  => $personId,
            'menu_group' => 'home',
        ]);
    }

    /**
     * Seeds a single cart line directly in the session, mirroring the shape
     * Sale_lib::add_item() produces, so tests can target postEditItem's
     * authorization branch without exercising the full add-item flow.
     */
    protected function seedCartLine(int $line, string $price, int $itemId): void
    {
        $this->withSession(array_merge($this->session, [
            'sales_cart' => [
                $line => [
                    'item_id'               => $itemId,
                    'item_location'         => 1,
                    'stock_name'            => 'Test Location',
                    'line'                  => $line,
                    'name'                  => 'Test Item',
                    'item_number'           => 'TEST-1',
                    'attribute_values'      => null,
                    'attribute_dtvalues'    => null,
                    'description'           => 'Test Item',
                    'serialnumber'          => '',
                    'allow_alt_description' => false,
                    'is_serialized'         => false,
                    'quantity'              => '1',
                    'discount'              => '0',
                    'discount_type'         => 0,
                    'in_stock'              => '10',
                    'price'                 => $price,
                    'cost_price'            => '1.00',
                    'total'                 => $price,
                    'discounted_total'      => $price,
                    'print_option'          => PRINT_YES,
                    'stock_type'            => HAS_NO_STOCK,
                    'item_type'             => ITEM,
                    'hsn_code'              => null,
                    'tax_category_id'       => null,
                ],
            ],
        ]));
    }

    public function testCashierWithoutReportsSalesCannotGetRow(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/row/' . $saleId);

        $response->assertStatus(403);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testCashierWithoutReportsSalesCannotGetSearch(): void
    {
        $cashierId = $this->createCashierEmployee();
        $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/search');

        $response->assertStatus(403);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertSame(lang('Sales.not_authorized'), $result['message']);
    }

    public function testCashierWithoutReportsSalesCannotGetEdit(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/edit/' . $saleId);

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    public function testCashierWithoutReportsSalesCannotGetReceipt(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/receipt/' . $saleId);

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    public function testCashierWithoutReportsSalesCannotGetInvoice(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/invoice/' . $saleId);

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    public function testCashierWithoutReportsSalesCannotSendPdf(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/sendpdf/' . $saleId);

        $response->assertStatus(403);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testCashierWithoutReportsSalesCannotSendReceipt(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/sendreceipt/' . $saleId);

        $response->assertStatus(403);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testCashierWithoutReportsSalesCannotPostSave(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->post('/sales/save/' . $saleId, [
            'date'              => date('m/d/Y H:i:s'),
            'customer_id'       => '',
            'employee_id'       => $cashierId,
            'comment'           => 'tampered',
            'invoice_number'    => '',
            'number_of_payments'=> 0,
            'payment_type_new'  => '--',
            'payment_amount_new'=> ''
        ]);

        $response->assertStatus(403);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertSame(lang('Sales.not_authorized'), $result['message']);
    }

    public function testEmployeeWithReportsSalesCanGetSearch(): void
    {
        $supervisorId = $this->createReportsSalesEmployee();
        $this->createSale($supervisorId);
        $this->loginAs($supervisorId);

        $response = $this->get('/sales/search');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertArrayNotHasKey('success', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('payment_summary', $result);
    }

    public function testEmployeeWithReportsSalesCanGetRow(): void
    {
        $supervisorId = $this->createReportsSalesEmployee();
        $saleId = $this->createSale($supervisorId);
        $this->loginAs($supervisorId);

        $response = $this->get('/sales/row/' . $saleId);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertArrayNotHasKey('success', $result);
    }

    public function testEmployeeWithReportsSalesCanGetEdit(): void
    {
        $supervisorId = $this->createReportsSalesEmployee();
        $saleId = $this->createSale($supervisorId);
        $this->loginAs($supervisorId);

        $response = $this->get('/sales/edit/' . $saleId);

        $response->assertStatus(200);
    }

    public function testCashierWithoutGrantCannotChangePrice(): void
    {
        $cashierId = $this->createCashierWithoutChangePriceGrant();
        $this->loginAs($cashierId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);

        $response = $this->post('/sales/editItem/1', [
            'description'  => 'Test Item',
            'serialnumber' => '',
            'price'        => '0.01',
            'quantity'     => '1',
            'discount'     => '0',
            'location'     => '1',
        ]);

        $response->assertStatus(200);
        $response->assertSee(lang('Sales.not_authorized'));

        $session = Services::session();
        $cart = $session->get('sales_cart');
        $this->assertEquals('5.00', $cart[1]['price']);
    }

    public function testCashierWithoutGrantCanEditQuantityAtSamePrice(): void
    {
        $cashierId = $this->createCashierWithoutChangePriceGrant();
        $this->loginAs($cashierId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);

        $response = $this->post('/sales/editItem/1', [
            'description'  => 'Test Item',
            'serialnumber' => '',
            'price'        => '5.00',
            'quantity'     => '3',
            'discount'     => '0',
            'location'     => '1',
        ]);

        $response->assertStatus(200);

        $session = Services::session();
        $cart = $session->get('sales_cart');
        $this->assertEquals('3', $cart[1]['quantity']);
        $this->assertEquals('5.00', $cart[1]['price']);
    }

    public function testCashierWithGrantCanChangePrice(): void
    {
        $cashierId = $this->createCashierWithChangePriceGrant();
        $this->loginAs($cashierId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);

        $response = $this->post('/sales/editItem/1', [
            'description'  => 'Test Item',
            'serialnumber' => '',
            'price'        => '0.01',
            'quantity'     => '1',
            'discount'     => '0',
            'location'     => '1',
        ]);

        $response->assertStatus(200);

        $session = Services::session();
        $cart = $session->get('sales_cart');
        $this->assertEquals('0.01', $cart[1]['price']);
    }

    public function testPostAddPaymentRejectsNegativeAmountTendered(): void
    {
        $cashierId = $this->createCashierWithoutChangePriceGrant();
        $this->loginAs($cashierId);
        $itemId = $this->createTestItem();
        $this->seedCartLine(1, '1.00', $itemId);

        $forgedPaymentType = lang('Sales.giftcard') . ':AUDIT-100';

        $response = $this->post('/sales/addPayment', [
            'payment_type'    => $forgedPaymentType,
            'amount_tendered' => '-500',
        ]);

        $response->assertStatus(200);
        $response->assertSee(lang('Sales.must_enter_numeric'));

        // ... and the negative payment must NOT have been added to the cart.
        $session  = Services::session();
        $payments = $session->get('sales_payments');
        $this->assertArrayNotHasKey($forgedPaymentType, (array) $payments);
    }

    public function testPostAddPaymentRejectsMalformedAmountTendered(): void
    {
        $cashierId = $this->createCashierWithoutChangePriceGrant();
        $this->loginAs($cashierId);
        $itemId = $this->createTestItem();
        $this->seedCartLine(1, '1.00', $itemId);

        $response = $this->post('/sales/addPayment', [
            'payment_type'    => lang('Sales.cash'),
            'amount_tendered' => 'ABC123',
        ]);

        $response->assertStatus(200);
        $response->assertSee(lang('Sales.must_enter_numeric'));

        $session  = Services::session();
        $payments = $session->get('sales_payments');
        $this->assertArrayNotHasKey(lang('Sales.cash'), (array) $payments);
    }

    public function testCashierWithoutReportsSalesCannotUnsuspend(): void
    {
        $victimId = $this->createReportsSalesEmployee();
        $saleId = $this->createSuspendedSale($victimId);

        $cashierId = $this->createCashierEmployee();
        $this->loginAs($cashierId);

        $response = $this->post('/sales/unsuspend', [
            'suspended_sale_id' => $saleId,
        ]);

        $response->assertStatus(403);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);

        $session = Services::session();
        $this->assertNotEquals($saleId, $session->get('sale_id'));
    }

    public function testEmployeeWithReportsSalesCannotUnsuspendCompletedSale(): void
    {
        $victimId = $this->createReportsSalesEmployee();
        $saleId = $this->createSale($victimId);

        $supervisorId = $this->createReportsSalesEmployee();
        $this->loginAs($supervisorId);

        $this->post('/sales/unsuspend', [
            'suspended_sale_id' => $saleId,
        ]);

        $session = Services::session();
        $this->assertNotEquals($saleId, $session->get('sale_id'));
    }

    public function testEmployeeWithReportsSalesCanUnsuspendSale(): void
    {
        $ownerId = $this->createReportsSalesEmployee();
        $saleId = $this->createSuspendedSale($ownerId);

        $supervisorId = $this->createReportsSalesEmployee();
        $this->loginAs($supervisorId);

        $this->post('/sales/unsuspend', [
            'suspended_sale_id' => $saleId,
        ]);

        $session = Services::session();
        $this->assertEquals($saleId, $session->get('sale_id'));
    }

    protected function createGiftcard(float $value): int
    {
        $giftcardNumber = random_int(1000000, 9999999);

        Database::connect()->table('giftcards')->insert([
            'giftcard_number' => $giftcardNumber,
            'value'           => $value,
            'deleted'         => 0,
            'person_id'       => null,
        ]);

        return $giftcardNumber;
    }

    protected function getGiftcardValue(int $giftcardNumber): float
    {
        $row = Database::connect()->table('giftcards')
            ->where('giftcard_number', $giftcardNumber)
            ->get()
            ->getRow();

        return (float) $row->value;
    }

    public function testCashierCannotForgeGiftcardPaymentTypeWithNegativeAmount(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $giftcardNumber = $this->createGiftcard(100.00);
        $this->loginAs($cashierId);
        $this->seedCartLine(1, '1.00', $itemId);

        $this->post('/sales/addPayment', [
            'payment_type'    => lang('Sales.giftcard') . ':' . $giftcardNumber,
            'amount_tendered' => '-500',
        ]);

        $payments = Services::session()->get('sales_payments');
        $this->assertEmpty($payments);
        $this->assertEqualsWithDelta(100.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testCashierCanUseLegitimateGiftcardPayment(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $giftcardNumber = $this->createGiftcard(100.00);
        $this->loginAs($cashierId);
        $this->seedCartLine(1, '1.00', $itemId);

        $this->post('/sales/addPayment', [
            'payment_type'    => lang('Sales.giftcard'),
            'amount_tendered' => (string) $giftcardNumber,
        ]);

        $payments = Services::session()->get('sales_payments');
        $this->assertNotEmpty($payments);
        $this->assertArrayHasKey(lang('Sales.giftcard') . ':' . $giftcardNumber, $payments);
    }

    public function testCashierCannotSubmitNegativeAmountForReferenceCodePayment(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->loginAs($cashierId);
        $this->seedCartLine(1, '1.00', $itemId);

        $this->post('/sales/addPayment', [
            'payment_type'    => lang('Sales.debit'),
            'amount_tendered' => '-25',
            'reference_code'  => 'ABC123',
        ]);

        $payments = Services::session()->get('sales_payments');
        $this->assertEmpty($payments);
    }

    public function testCashierCanUseLegitimateReferenceCodePayment(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->loginAs($cashierId);
        $this->seedCartLine(1, '1.00', $itemId);

        $this->post('/sales/addPayment', [
            'payment_type'    => lang('Sales.debit'),
            'amount_tendered' => '25.00',
            'reference_code'  => 'ABC123',
        ]);

        $payments = Services::session()->get('sales_payments');
        $this->assertNotEmpty($payments);
        $this->assertArrayHasKey(lang('Sales.debit'), $payments);
        $this->assertSame('ABC123', $payments[lang('Sales.debit')]['reference_code']);
    }

    public function testCashierCannotSubmitNegativeAmountForCashPayment(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->loginAs($cashierId);
        $this->seedCartLine(1, '1.00', $itemId);

        $this->post('/sales/addPayment', [
            'payment_type'    => lang('Sales.cash'),
            'amount_tendered' => '-10',
        ]);

        $payments = Services::session()->get('sales_payments');
        $this->assertEmpty($payments);
    }

    public function testCashierCanUseLegitimateCashPayment(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->loginAs($cashierId);
        $this->seedCartLine(1, '1.00', $itemId);

        $this->post('/sales/addPayment', [
            'payment_type'    => lang('Sales.cash'),
            'amount_tendered' => '10.00',
        ]);

        $payments = Services::session()->get('sales_payments');
        $this->assertNotEmpty($payments);
        $this->assertArrayHasKey(lang('Sales.cash'), $payments);
    }

    public function testPostCompleteRejectsSaleWithInsufficientPayments(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->loginAs($cashierId);
        $this->seedCartLine(1, '100.00', $itemId);
        $this->withSession(array_merge($this->session, ['sale_id' => NEW_ENTRY]));

        $salesCountBefore = Database::connect()->table('sales')->countAllResults();

        $this->post('/sales/complete');

        $salesCountAfter = Database::connect()->table('sales')->countAllResults();
        $this->assertSame($salesCountBefore, $salesCountAfter);
    }

    public function testPostCompleteAllowsZeroPaymentQuoteCompletion(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->loginAs($cashierId);
        $this->seedCartLine(1, '100.00', $itemId);
        $this->withSession(array_merge($this->session, ['sale_id' => NEW_ENTRY, 'sales_mode' => 'sale_quote']));

        $salesCountBefore = Database::connect()->table('sales')->countAllResults();

        $response = $this->post('/sales/complete');

        $salesCountAfter = Database::connect()->table('sales')->countAllResults();
        $this->assertSame($salesCountBefore + 1, $salesCountAfter);
        $response->assertDontSee(lang('Sales.amount_due_not_covered'));
    }

    public function testPostCompleteAllowsZeroPaymentInvoiceCompletion(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->loginAs($cashierId);
        $this->seedCartLine(1, '100.00', $itemId);
        $this->withSession(array_merge($this->session, ['sale_id' => NEW_ENTRY, 'sales_mode' => 'sale_invoice']));

        $salesCountBefore = Database::connect()->table('sales')->countAllResults();

        $response = $this->post('/sales/complete');

        $salesCountAfter = Database::connect()->table('sales')->countAllResults();
        $this->assertSame($salesCountBefore + 1, $salesCountAfter);
        $response->assertDontSee(lang('Sales.amount_due_not_covered'));
    }

    public function testRegisterEscapesMaliciousTaxName(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem();

        Database::connect()->table('items_taxes')->insert([
            'item_id' => $itemId,
            'name'    => '<svg onload=alert(1)>',
            'percent' => 5,
        ]);

        $this->loginAs($cashierId);
        $this->seedCartLine(1, '5.00', $itemId);

        $response = $this->get('/sales');

        $response->assertStatus(200);
        $body = $response->getBody();
        $this->assertStringNotContainsString('<svg onload', $body);
        $this->assertStringContainsString('&lt;svg', $body);
    }

    public function testPostUnsuspendRejectsNonSuspendedSaleWithoutClearingActiveCart(): void
    {
        $employeeId = $this->createReportsSalesEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $notSuspendedSaleId = $this->createSale($employeeId);
        $this->loginAs($employeeId);
        $this->seedCartLine(1, '1.00', $itemId);
        $this->withSession(array_merge($this->session, ['sale_id' => NEW_ENTRY]));

        $this->post('/sales/unsuspend', [
            'suspended_sale_id' => (string) $notSuspendedSaleId,
        ]);

        $cart = Services::session()->get('sales_cart');
        $this->assertNotEmpty($cart);
        $this->assertArrayHasKey(1, $cart);
        $this->assertSame($itemId, $cart[1]['item_id']);
        $this->assertSame(NEW_ENTRY, Services::session()->get('sale_id'));
    }

    public function testPostUnsuspendReplacesCartForValidSuspendedSale(): void
    {
        $employeeId = $this->createReportsSalesEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $suspendedSaleId = $this->createSuspendedSale($employeeId);
        $this->loginAs($employeeId);
        $this->seedCartLine(1, '1.00', $itemId);

        $this->post('/sales/unsuspend', [
            'suspended_sale_id' => (string) $suspendedSaleId,
        ]);

        $this->assertSame($suspendedSaleId, Services::session()->get('sale_id'));
        $cart = Services::session()->get('sales_cart');
        $this->assertNotEmpty($cart);
        $this->assertNotSame($itemId, array_values($cart)[0]['item_id']);
    }
    public function testPostApplyBillDiscount_AppliesPercentage(): void
    {
        $employeeId = $this->createCashierEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);

        $response = $this->post('/sales/applyBillDiscount', [
            'bill_discount_value' => '20',
            'bill_discount_type' => 'percent',
        ]);

        $response->assertStatus(200);
        $cart = Services::session()->get('sales_cart');
        $this->assertEqualsWithDelta(1.0, (float) $cart[1]['bill_discount'], 0.00001);
    }

    public function testPostApplyBillDiscount_RejectsExcessAmount(): void
    {
        $employeeId = $this->createCashierEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);

        $response = $this->post('/sales/applyBillDiscount', [
            'bill_discount_value' => '6',
            'bill_discount_type' => 'fixed',
        ]);

        $response->assertStatus(200);
        $response->assertSee(lang('Sales.bill_discount_invalid'));
        $cart = Services::session()->get('sales_cart');
        $this->assertEmpty($cart[1]['bill_discount'] ?? null);
    }

    public function testPostComplete_SavesAndPrintsBillDiscount(): void
    {
        $employeeId = $this->createReportsSalesEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);
        $cart = $this->session['sales_cart'];
        $cart[1]['bill_discount'] = '1.00';
        $cart[1]['name'] = 'Receipt sale name';
        $this->withSession(array_merge($this->session, [
            'sale_id' => NEW_ENTRY,
            'sales_cart' => $cart,
            'sales_payments' => [lang('Sales.cash') => [
                'payment_type' => lang('Sales.cash'),
                'payment_amount' => '4.00',
                'cash_refund' => 0,
                'cash_adjustment' => 0,
                'reference_code' => null,
            ]],
        ]));

        $response = $this->post('/sales/complete');

        $response->assertStatus(200);
        $response->assertSee(lang('Sales.discount'));
        $response->assertDontSee(lang('Sales.bill_discount'));
        $saved = Database::connect()->table('sales_items')->where('item_id', $itemId)->get()->getRow();
        $this->assertNotNull($saved);
        $this->assertEqualsWithDelta(1.0, (float) $saved->bill_discount, 0.00001);
        $this->assertEqualsWithDelta(4.0, (float) model(\App\Models\Sale::class)->get_info((int) $saved->sale_id)->getRowArray()['amount_due'], 0.00001);
        $response->assertSee('Receipt sale name');
        $receiptDocument = new DOMDocument();
        @$receiptDocument->loadHTML($response->getBody());
        $receiptXpath = new DOMXPath($receiptDocument);
        $subtotalRow = $receiptXpath->query('//table[@id="receipt_items"]//tr[td[contains(normalize-space(.), "Subtotal")]]')->item(0);
        $this->assertNotNull($subtotalRow);
        $this->assertSame(to_currency('5.00'), trim($receiptXpath->query('./td[last()]', $subtotalRow)->item(0)->textContent));


        $db = Database::connect();
        foreach (['sales_items_taxes_temp', 'sales_payments_temp', 'sales_items_temp'] as $table) {
            $db->query('DROP TEMPORARY TABLE IF EXISTS ' . $db->prefixTable($table));
        }
        $this->withSession(['person_id' => $employeeId, 'menu_group' => 'home']);
        $reprint = $this->get('/sales/receipt/' . $saved->sale_id);
        $reprint->assertStatus(200);
        $reprint->assertSee(lang('Sales.discount'));
        $reprint->assertDontSee(lang('Sales.bill_discount'));
        $reprint->assertSee('Receipt sale name');
    }

    public function testPostComplete_CombinesItemAndBillDiscountOnReceipt(): void
    {
        $config = config(OSPOS::class);
        $previous = $config->settings['receipt_show_total_discount'];
        $previousTaxes = $config->settings['receipt_show_taxes'];
        try {
            $config->settings['receipt_show_total_discount'] = '1';
            $config->settings['receipt_show_taxes'] = '0';
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_NO_STOCK);
            $this->seedCartLine(1, '5.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['discount'] = '10';
            $cart[1]['discount_type'] = PERCENT;
            $cart[1]['discounted_total'] = '4.50';
            $cart[1]['bill_discount'] = '0.50';
            $this->withSession(array_merge($this->session, [
                'sale_id' => NEW_ENTRY, 'sales_cart' => $cart,
                'sales_payments' => [lang('Sales.cash') => [
                    'payment_type' => lang('Sales.cash'), 'payment_amount' => '4.00',
                    'cash_refund' => 0, 'cash_adjustment' => 0, 'reference_code' => null,
                ]],
            ]));
            $response = $this->post('/sales/complete');
            $response->assertStatus(200);
            $document = new DOMDocument();
            @$document->loadHTML($response->getBody());
            $xpath = new DOMXPath($document);
            $rows = $xpath->query('//table[@id="receipt_items"]//tr[td[contains(@class,"total-value") and contains(normalize-space(.), "Discount:")]]');
            $this->assertSame(1, $rows->length);
            $this->assertSame(to_currency('-1.00'), trim($xpath->query('./td[last()]', $rows->item(0))->item(0)->textContent));
            $this->assertStringNotContainsString(lang('Sales.bill_discount'), $response->getBody());
        } finally {
            $config->settings['receipt_show_total_discount'] = $previous;
            $config->settings['receipt_show_taxes'] = $previousTaxes;
        }
    }

    public function testPostComplete_ChargesTaxAfterBillDiscount(): void
    {
        $employeeId = $this->createCashierEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        Database::connect()->table('items_taxes')->insert(['item_id' => $itemId, 'name' => 'VAT', 'percent' => 10]);
        $this->seedCartLine(1, '10.00', $itemId);
        $cart = $this->session['sales_cart'];
        $cart[1]['bill_discount'] = '1.00';
        $this->withSession(array_merge($this->session, [
            'sale_id' => NEW_ENTRY,
            'sales_cart' => $cart,
            'sales_payments' => [lang('Sales.cash') => [
                'payment_type' => lang('Sales.cash'), 'payment_amount' => '9.90',
                'cash_refund' => 0, 'cash_adjustment' => 0, 'reference_code' => null,
            ]],
        ]));

        $response = $this->post('/sales/complete');

        $response->assertStatus(200);
        $response->assertSee(lang('Sales.discount'));
        $response->assertDontSee(lang('Sales.bill_discount'));
        $saved = Database::connect()->table('sales_items')->where('item_id', $itemId)->get()->getRow();
        $this->assertNotNull($saved);
        $tax = Database::connect()->table('sales_items_taxes')->where('sale_id', $saved->sale_id)->get()->getRow();
        $this->assertNotNull($tax);
        $this->assertEqualsWithDelta(0.90, (float) $tax->item_tax_amount, 0.00001);
        // Feature tests share one connection; production requests get fresh temporary tables.
        $db = Database::connect();
        foreach (['sales_items_taxes_temp', 'sales_payments_temp', 'sales_items_temp'] as $table) {
            $db->query('DROP TEMPORARY TABLE IF EXISTS ' . $db->prefixTable($table));
        }
        $this->assertEqualsWithDelta(9.90, (float) model(\App\Models\Sale::class)->get_info((int) $saved->sale_id)->getRowArray()['amount_due'], 0.00001);
    }

    public function testPostEditItem_ChangesSaleNameWithoutRenamingInventory(): void
    {
        $employeeId = $this->createCashierEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);

        $response = $this->post('/sales/editItem/1', [
            'name' => 'Custom sale name', 'description' => 'Test Item', 'serialnumber' => '',
            'price' => '5.00', 'quantity' => '1', 'discount' => '0', 'discount_type' => '0', 'location' => '1',
        ]);

        $response->assertStatus(200);
        $cart = Services::session()->get('sales_cart');
        $this->assertSame('Custom sale name', $cart[1]['name']);
        $item = Database::connect()->table('items')->where('item_id', $itemId)->get()->getRow();
        $this->assertSame('Test Item', $item->name);
    }

    public function testPostComplete_HandlesIncludedTaxAfterBillDiscount(): void
    {
        $config = config(OSPOS::class);
        $prior = $config->settings['tax_included'];
        $config->settings['tax_included'] = '1';
        try {
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_NO_STOCK);
            Database::connect()->table('items_taxes')->insert(['item_id' => $itemId, 'name' => 'VAT', 'percent' => 10]);
            $this->seedCartLine(1, '11.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['bill_discount'] = '1.10';
            $this->withSession(array_merge($this->session, [
                'sale_id' => NEW_ENTRY,
                'sales_cart' => $cart,
                'sales_payments' => [lang('Sales.cash') => [
                    'payment_type' => lang('Sales.cash'), 'payment_amount' => '9.90',
                    'cash_refund' => 0, 'cash_adjustment' => 0, 'reference_code' => null,
                ]],
            ]));

            $response = $this->post('/sales/complete');

            $response->assertStatus(200);
            $response->assertSee(lang('Sales.discount'));
        $response->assertDontSee(lang('Sales.bill_discount'));
            $saved = Database::connect()->table('sales_items')->where('item_id', $itemId)->get()->getRow();
            $this->assertNotNull($saved);
            $tax = Database::connect()->table('sales_items_taxes')->where('sale_id', $saved->sale_id)->get()->getRow();
            $this->assertNotNull($tax);
            $this->assertEqualsWithDelta(0.90, (float) $tax->item_tax_amount, 0.0001);
        } finally {
            $config->settings['tax_included'] = $prior;
        }
    }

    public function testPostApplyBillDiscount_AppliesFixedAmount(): void
    {
        $employeeId = $this->createCashierEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);

        $response = $this->post('/sales/applyBillDiscount', [
            'bill_discount_value' => '1.25', 'bill_discount_type' => 'fixed',
        ]);

        $response->assertStatus(200);
        $cart = Services::session()->get('sales_cart');
        $this->assertEqualsWithDelta(1.25, (float) $cart[1]['bill_discount'], 0.00001);
    }

    public function testPostComplete_HandlesMultipleTaxRatesAndSplitPayment(): void
    {
        $employeeId = $this->createCashierEmployee();
        $this->loginAs($employeeId);
        $firstItem = $this->createTestItem(HAS_NO_STOCK);
        $secondItem = $this->createTestItem(HAS_NO_STOCK);
        $db = Database::connect();
        $db->table('items_taxes')->insert(['item_id' => $firstItem, 'name' => 'VAT5', 'percent' => 5]);
        $db->table('items_taxes')->insert(['item_id' => $secondItem, 'name' => 'VAT10', 'percent' => 10]);
        $this->seedCartLine(1, '10.00', $firstItem);
        $cart = $this->session['sales_cart'];
        $cart[2] = $cart[1];
        $cart[2]['line'] = 2;
        $cart[2]['item_id'] = $secondItem;
        $this->withSession(array_merge($this->session, ['sale_id' => NEW_ENTRY, 'sales_cart' => $cart]));

        $apply = $this->post('/sales/applyBillDiscount', ['bill_discount_value' => '10', 'bill_discount_type' => 'percent']);
        $apply->assertStatus(200);
        $cart = Services::session()->get('sales_cart');
        $this->assertEqualsWithDelta(1, (float) $cart[1]['bill_discount'], 0.0001);
        $this->assertEqualsWithDelta(1, (float) $cart[2]['bill_discount'], 0.0001);
        $this->withSession(array_merge($this->session, [
            'sale_id' => NEW_ENTRY,
            'sales_cart' => $cart,
            'sales_payments' => [
                lang('Sales.cash') => ['payment_type' => lang('Sales.cash'), 'payment_amount' => '10', 'cash_refund' => 0, 'cash_adjustment' => 0, 'reference_code' => null],
                lang('Sales.debit') => ['payment_type' => lang('Sales.debit'), 'payment_amount' => '9.35', 'cash_refund' => 0, 'cash_adjustment' => 0, 'reference_code' => null],
            ],
        ]));

        $response = $this->post('/sales/complete');

        $response->assertStatus(200);
        $response->assertSee(lang('Sales.discount'));
        $response->assertDontSee(lang('Sales.bill_discount'));
        $line = $db->table('sales_items')->where('item_id', $firstItem)->get()->getRow();
        $this->assertNotNull($line);
        $taxes = $db->table('sales_items_taxes')->selectSum('item_tax_amount')->where('sale_id', $line->sale_id)->get()->getRow();
        $this->assertEqualsWithDelta(1.35, (float) $taxes->item_tax_amount, 0.0001);
        $payments = $db->table('sales_payments')->selectSum('payment_amount')->where('sale_id', $line->sale_id)->get()->getRow();
        $this->assertEqualsWithDelta(19.35, (float) $payments->payment_amount, 0.0001);
    }

    public function testPostAdd_RestoresDiscountAndNameForReturn(): void
    {
        $employeeId = $this->createCashierEmployee();
        $saleId = $this->createSale($employeeId);
        Database::connect()->table('sales_items')->where('sale_id', $saleId)->update([
            'sale_item_name' => 'Original receipt name',
            'bill_discount' => '0.25',
        ]);
        $this->loginAs($employeeId);
        $this->withSession(array_merge($this->session, ['sales_mode' => 'return', 'sale_id' => NEW_ENTRY]));

        $response = $this->post('/sales/add', ['item' => 'POS ' . $saleId]);

        $response->assertStatus(200);
        $cart = array_values(Services::session()->get('sales_cart'));
        $this->assertCount(1, $cart);
        $this->assertSame('Original receipt name', $cart[0]['name']);
        $this->assertEqualsWithDelta(-1, (float) $cart[0]['quantity'], 0.0001);
        $this->assertEqualsWithDelta(-0.25, (float) $cart[0]['bill_discount'], 0.0001);
    }

    public function testPostUnsuspend_RestoresBillDiscountSettings(): void
    {
        $employeeId = $this->createReportsSalesEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);
        $this->withSession(array_merge($this->session, ['sale_id' => NEW_ENTRY]));
        $apply = $this->post('/sales/applyBillDiscount', ['bill_discount_value' => '10', 'bill_discount_type' => 'percent']);
        $apply->assertStatus(200);
        $cart = Services::session()->get('sales_cart');
        $settings = Services::session()->get('sales_bill_discount');
        $this->withSession(array_merge($this->session, [
            'sale_id' => NEW_ENTRY, 'sales_cart' => $cart, 'sales_bill_discount' => $settings,
        ]));

        $suspend = $this->post('/sales/suspend');
        $suspend->assertStatus(200);
        $line = Database::connect()->table('sales_items')->where('item_id', $itemId)->get()->getRow();
        $this->assertNotNull($line);
        $this->withSession(['person_id' => $employeeId, 'menu_group' => 'home']);
        $resume = $this->post('/sales/unsuspend', ['suspended_sale_id' => (string) $line->sale_id]);

        $resume->assertStatus(200);
        $restored = array_values(Services::session()->get('sales_cart'));
        $this->assertEqualsWithDelta(0.5, (float) $restored[0]['bill_discount'], 0.0001);
        $restoredSettings = Services::session()->get('sales_bill_discount');
        $this->assertSame('percent', $restoredSettings['type']);
        $this->assertEqualsWithDelta(10, (float) $restoredSettings['value'], 0.0001);
    }

    public function testGetRegister_HidesDescriptionWhenDisabled(): void
    {
        $config = config(OSPOS::class);
        $previous = $config->settings['receipt_show_description'];
        $config->settings['receipt_show_description'] = '0';
        try {
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_NO_STOCK);
            $this->seedCartLine(1, '5.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['description'] = 'Distinct hidden description';
            $this->withSession(array_merge($this->session, ['sales_cart' => $cart]));

            $response = $this->get('/sales');

            $response->assertStatus(200);
            $document = new DOMDocument();
            @$document->loadHTML($response->getBody());
            $xpath = new DOMXPath($document);
            $this->assertSame(0, $xpath->query('//td[contains(normalize-space(.), "Distinct hidden description")]')->length);
            $hidden = $xpath->query('//input[@type="hidden" and @name="description"]')->item(0);
            $this->assertNotNull($hidden);
            $this->assertSame('Distinct hidden description', $hidden->getAttribute('value'));
            $this->assertSame(8, $xpath->query('//tr[contains(@class, "register-item-row")]/td')->length);
            $this->assertSame(1, $xpath->query('//tr[contains(@class, "register-item-row")]/td[1][contains(@class, "register-row-text")]')->length);
            $this->assertSame(1, $xpath->query('//tr[contains(@class, "register-item-row")]/td[2][contains(@class, "register-row-text")]')->length);
            $this->assertSame(1, $xpath->query('//tr[contains(@class, "register-item-spacer")]/td[@colspan="8"]')->length);
        } finally {
            $config->settings['receipt_show_description'] = $previous;
        }
    }

    public function testGetRegister_ShowsDescriptionWhenEnabled(): void
    {
        $config = config(OSPOS::class);
        $previous = $config->settings['receipt_show_description'];
        $config->settings['receipt_show_description'] = '1';
        try {
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_NO_STOCK);
            $this->seedCartLine(1, '5.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['description'] = 'Distinct visible description';
            $this->withSession(array_merge($this->session, ['sales_cart' => $cart]));

            $response = $this->get('/sales');

            $response->assertStatus(200);
            $document = new DOMDocument();
            @$document->loadHTML($response->getBody());
            $xpath = new DOMXPath($document);
            $this->assertGreaterThan(0, $xpath->query('//td[contains(normalize-space(.), "Distinct visible description")]')->length);
            $detailRow = $xpath->query('//tr[contains(@class, "register-item-row")]/following-sibling::tr[1]')->item(0);
            $this->assertNotNull($detailRow);
            $columns = 0;
            foreach ($xpath->query('./td', $detailRow) as $cell) {
                $columns += max(1, (int) $cell->getAttribute('colspan'));
            }
            $this->assertSame(8, $columns);
        } finally {
            $config->settings['receipt_show_description'] = $previous;
        }
    }

    public function testGetRegister_PreservesSerialInputWhenDescriptionDisabled(): void
    {
        $config = config(OSPOS::class);
        $previous = $config->settings['receipt_show_description'];
        $config->settings['receipt_show_description'] = '0';
        try {
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_NO_STOCK);
            $this->seedCartLine(1, '5.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['is_serialized'] = true;
            $cart[1]['serialnumber'] = 'SERIAL-42';
            $this->withSession(array_merge($this->session, ['sales_cart' => $cart]));

            $response = $this->get('/sales');

            $response->assertStatus(200);
            $document = new DOMDocument();
            @$document->loadHTML($response->getBody());
            $xpath = new DOMXPath($document);
            $serial = $xpath->query('//input[@name="serialnumber" and not(@type="hidden")]')->item(0);
            $this->assertNotNull($serial);
            $this->assertSame('SERIAL-42', $serial->getAttribute('value'));
        } finally {
            $config->settings['receipt_show_description'] = $previous;
        }
    }

    public function testPostEditItem_PreservesHiddenDescription(): void
    {
        $config = config(OSPOS::class);
        $previous = $config->settings['receipt_show_description'];
        $config->settings['receipt_show_description'] = '0';
        try {
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_NO_STOCK);
            $this->seedCartLine(1, '5.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['description'] = 'Keep this description';
            $this->withSession(array_merge($this->session, ['sales_cart' => $cart]));

            $response = $this->post('/sales/editItem/1', [
                'name' => 'Updated sale name', 'description' => 'Keep this description', 'serialnumber' => '',
                'price' => '5.00', 'quantity' => '1', 'discount' => '0', 'discount_type' => '0', 'location' => '1',
            ]);

            $response->assertStatus(200);
            $savedCart = Services::session()->get('sales_cart');
            $this->assertSame('Keep this description', $savedCart[1]['description']);
            $this->assertSame('Updated sale name', $savedCart[1]['name']);
        } finally {
            $config->settings['receipt_show_description'] = $previous;
        }
    }

    public function testPostComplete_HidesInvoiceDescriptionWhenDisabled(): void
    {
        $config = config(OSPOS::class);
        $previous = $config->settings['receipt_show_description'];
        $previousInvoice = $config->settings['invoice_type'];
        $config->settings['receipt_show_description'] = '0';
        $config->settings['invoice_type'] = 'invoice';
        try {
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_NO_STOCK);
            $this->seedCartLine(1, '5.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['name'] = 'Invoice item name';
            $cart[1]['description'] = 'Hidden invoice description';
            $cart[1]['allow_alt_description'] = true;
            $this->withSession(array_merge($this->session, [
                'sale_id' => NEW_ENTRY, 'sales_mode' => 'sale_invoice', 'sales_cart' => $cart,
            ]));

            $response = $this->post('/sales/complete');

            $response->assertStatus(200);
            $response->assertSee('Invoice item name');
            $response->assertDontSee('Hidden invoice description');
        } finally {
            $config->settings['receipt_show_description'] = $previous;
            $config->settings['invoice_type'] = $previousInvoice;
        }
    }

    public function testPostComplete_HidesTaxInvoiceDescriptionWhenDisabled(): void
    {
        $config = config(OSPOS::class);
        $previousDescription = $config->settings['receipt_show_description'];
        $previousInvoice = $config->settings['invoice_type'];
        $config->settings['receipt_show_description'] = '0';
        $config->settings['invoice_type'] = 'tax_invoice';
        try {
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_NO_STOCK);
            $this->seedCartLine(1, '5.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['name'] = 'Tax invoice item';
            $cart[1]['description'] = 'Hidden tax invoice description';
            $cart[1]['allow_alt_description'] = true;
            $this->withSession(array_merge($this->session, [
                'sale_id' => NEW_ENTRY, 'sales_mode' => 'sale_invoice', 'sales_cart' => $cart,
            ]));

            $response = $this->post('/sales/complete');

            $response->assertStatus(200);
            $response->assertSee('Tax invoice item');
            $response->assertDontSee('Hidden tax invoice description');
        } finally {
            $config->settings['receipt_show_description'] = $previousDescription;
            $config->settings['invoice_type'] = $previousInvoice;
        }
    }

    public function testPostComplete_PreservesWorkOrderSerialWhenDescriptionDisabled(): void
    {
        $config = config(OSPOS::class);
        $previous = $config->settings['receipt_show_description'];
        $config->settings['receipt_show_description'] = '0';
        try {
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_NO_STOCK);
            $this->seedCartLine(1, '5.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['description'] = 'Hidden work order description';
            $cart[1]['is_serialized'] = true;
            $cart[1]['serialnumber'] = 'WORK-SERIAL-42';
            $this->withSession(array_merge($this->session, [
                'sale_id' => NEW_ENTRY, 'sales_mode' => 'sale_work_order', 'sales_cart' => $cart,
                'sales_payments' => [lang('Sales.cash') => [
                    'payment_type' => lang('Sales.cash'), 'payment_amount' => '5.00',
                    'cash_refund' => 0, 'cash_adjustment' => 0, 'reference_code' => null,
                ]],
            ]));

            $response = $this->post('/sales/complete');

            $response->assertStatus(200);
            $response->assertSee('WORK-SERIAL-42');
            $response->assertDontSee('Hidden work order description');
        } finally {
            $config->settings['receipt_show_description'] = $previous;
        }
    }

    public function testGetRegister_ShowsSubtotalBeforeBillDiscount(): void
    {
        $employeeId = $this->createCashierEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);
        $cart = $this->session['sales_cart'];
        $cart[1]['bill_discount'] = '1.00';
        $this->withSession(array_merge($this->session, ['sales_cart' => $cart]));

        $response = $this->get('/sales');
        $response->assertStatus(200);
        $document = new DOMDocument();
        @$document->loadHTML($response->getBody());
        $xpath = new DOMXPath($document);
        $rows = $xpath->query('//table[@id="sale_totals"]/tr');
        $this->assertSame(to_currency('5.00'), trim($xpath->query('./th[2]', $rows->item(1))->item(0)->textContent));
        $this->assertSame('-' . to_currency('1.00'), trim($xpath->query('./th[2]', $rows->item(2))->item(0)->textContent));
        $this->assertSame(to_currency('4.00'), trim($xpath->query('./th[2]', $rows->item(3))->item(0)->textContent));
    }

    public function testGetRegister_ShowsStockAndDescriptionInSeparateDetailColumns(): void
    {
        $config = config(OSPOS::class);
        $previous = $config->settings['receipt_show_description'];
        $config->settings['receipt_show_description'] = '1';
        try {
            $employeeId = $this->createCashierEmployee();
            $this->loginAs($employeeId);
            $itemId = $this->createTestItem(HAS_STOCK);
            $this->seedCartLine(1, '5.00', $itemId);
            $cart = $this->session['sales_cart'];
            $cart[1]['description'] = 'Description for this item';
            $cart[1]['stock_type'] = '0';
            $cart[1]['in_stock'] = '14';
            $cart[1]['stock_name'] = 'stock';
            $this->withSession(array_merge($this->session, ['sales_cart' => $cart]));
            $response = $this->get('/sales');
            $response->assertStatus(200);
            $document = new DOMDocument();
            @$document->loadHTML($response->getBody());
            $xpath = new DOMXPath($document);
            $this->assertSame(1, $xpath->query('//tr[contains(@class,"register-item-details")]/td[3][contains(.,"Description for this item")]')->length);
            $this->assertSame(1, $xpath->query('//tr[contains(@class,"register-item-details")]/td[4][contains(.,"14 in stock")]')->length);
        } finally {
            $config->settings['receipt_show_description'] = $previous;
        }
    }

    public function testReports_UseBillDiscountAndSaleSpecificNames(): void
    {
        $employeeId = $this->createReportsSalesEmployee();
        $db = Database::connect();
        $saleId = $this->createSale($employeeId);
        $line = $db->table('sales_items')->where('sale_id', $saleId)->get()->getRowArray();
        $db->table('sales')->where('sale_id', $saleId)->update([
            'sale_time' => '2035-04-03 00:00:00', 'bill_discount_type' => 'percent', 'bill_discount_value' => '10.0000',
        ]);
        $db->table('sales_items')->where('sale_id', $saleId)->update([
            'item_unit_price' => '100', 'bill_discount' => '10', 'sale_item_name' => 'First sale name',
        ]);
        $line['line'] = 2;
        $line['item_unit_price'] = '50';
        $line['bill_discount'] = '5';
        $line['sale_item_name'] = 'Second sale name';
        $db->table('sales_items')->insert($line);
        $inputs = ['start_date' => '2035-04-03', 'end_date' => '2035-04-03', 'location_id' => 'all', 'sale_type' => 'sales', 'definition_ids' => [], 'discount_type' => PERCENT];
        foreach (['sales_items_taxes_temp', 'sales_payments_temp', 'sales_items_temp', 'sales_report_payments_temp', 'sumpay_taxes_temp', 'sumpay_items_temp', 'sumpay_payments_temp'] as $table) {
            $db->query('DROP TEMPORARY TABLE IF EXISTS ' . $db->prefixTable($table));
        }
        try {
            $summary = model(\App\Models\Reports\Summary_sales::class)->getSummaryData($inputs);
        } catch (\Throwable $e) {
            throw new \RuntimeException((string) $db->getLastQuery(), 0, $e);
        }
        $this->assertEqualsWithDelta(135, (float) $summary['total'], 0.001, (string) $db->getLastQuery());
        $this->assertEqualsWithDelta(133, (float) $summary['profit'], 0.001);
        $details = model(\App\Models\Reports\Detailed_sales::class);
        $details->create($inputs);
        $data = $details->getData($inputs);
        $this->assertCount(2, $data['details'][0]);
        $this->assertEqualsCanonicalizing(['First sale name', 'Second sale name'], array_column($data['details'][0], 'name'));
        $this->assertEqualsWithDelta(135, (float) $data['summary'][0]['total'], 0.001);
        $discounts = model(\App\Models\Reports\Summary_discounts::class)->getData($inputs);
        $bill = array_values(array_filter($discounts, static fn ($row) => str_contains($row['discount'], lang('Sales.bill_discount'))));
        $this->assertCount(1, $bill);
        $this->assertEqualsWithDelta(15, (float) $bill[0]['total'], 0.001);
        $this->assertSame(1, (int) $bill[0]['count']);
        $payments = model(\App\Models\Reports\Summary_payments::class)->getData($inputs);
        $this->assertEqualsWithDelta(135, (float) $payments[0]['trans_amount'], 0.001);
        $itemReport = model(\App\Models\Reports\Detailed_item_sales::class)->getData($inputs);
        $this->assertCount(2, $itemReport);
        $this->assertEqualsWithDelta(15, array_sum(array_column($itemReport, 'bill_discount')), 0.001);
        $inputs['discount'] = '10';
        $specific = model(\App\Models\Reports\Specific_discount::class)->getData($inputs);
        $this->assertCount(1, $specific['summary']);
        $this->assertEqualsWithDelta(135, (float) $specific['summary'][0]['total'], 0.001);

        foreach ([1 => '9.00', 2 => '4.50'] as $lineNumber => $tax) {
            $db->table('sales_items_taxes')->insert([
                'sale_id' => $saleId, 'item_id' => $line['item_id'], 'line' => $lineNumber,
                'name' => 'VAT', 'percent' => '10', 'tax_type' => 1, 'item_tax_amount' => $tax,
            ]);
        }
        $taxReport = model(\App\Models\Reports\Summary_taxes::class)->getData($inputs);
        $this->assertEqualsWithDelta(135, (float) $taxReport[0]['subtotal'], 0.001);
        $this->assertEqualsWithDelta(13.5, (float) $taxReport[0]['tax'], 0.001);
        $this->assertEqualsWithDelta(148.5, (float) $taxReport[0]['total'], 0.001);
        $config = config(OSPOS::class);
        $previousTaxIncluded = $config->settings['tax_included'];
        $config->settings['tax_included'] = '1';
        try {
            $inclusiveTaxes = (new \App\Models\Reports\Summary_taxes())->getData($inputs);
            $this->assertEqualsWithDelta(121.5, (float) $inclusiveTaxes[0]['subtotal'], 0.001);
            $this->assertEqualsWithDelta(135, (float) $inclusiveTaxes[0]['total'], 0.001);
        } finally {
            $config->settings['tax_included'] = $previousTaxIncluded;
        }

        $db->table('sales')->where('sale_id', $saleId)->update(['sale_type' => SALE_TYPE_RETURN]);
        $db->query('UPDATE ' . $db->prefixTable('sales_items') . ' SET quantity_purchased = -quantity_purchased, bill_discount = -bill_discount WHERE sale_id = ?', [$saleId]);
        $db->query('UPDATE ' . $db->prefixTable('sales_items_taxes') . ' SET item_tax_amount = -item_tax_amount WHERE sale_id = ?', [$saleId]);
        foreach (['sales_items_taxes_temp', 'sales_report_payments_temp'] as $table) {
            $db->query('DROP TEMPORARY TABLE IF EXISTS ' . $db->prefixTable($table));
        }
        $inputs['sale_type'] = 'returns';
        $returnSummary = model(\App\Models\Reports\Summary_sales::class)->getSummaryData($inputs);
        $this->assertEqualsWithDelta(-148.5, (float) $returnSummary['total'], 0.001);
        $returnDiscounts = model(\App\Models\Reports\Summary_discounts::class)->getData($inputs);
        $this->assertEqualsWithDelta(-15, (float) $returnDiscounts[0]['total'], 0.001);
        $db->table('sales')->where('sale_id', $saleId)->update(['bill_discount_type' => null, 'bill_discount_value' => null]);
        $inputs['discount_type'] = FIXED;
        $returnDiscounts = model(\App\Models\Reports\Summary_discounts::class)->getData($inputs);
        $this->assertEqualsWithDelta(-15, (float) $returnDiscounts[0]['total'], 0.001);

    }

    public function testGetRegister_ShowsFixedBillDiscountToggle(): void
    {
        $employeeId = $this->createCashierEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);

        $response = $this->get('/sales');

        $response->assertStatus(200);
        $document = new DOMDocument();
        @$document->loadHTML($response->getBody());
        $xpath = new DOMXPath($document);
        $this->assertSame(1, $xpath->query('//form[@id="bill_discount_form"]//input[@id="bill_discount_toggle" and @type="checkbox" and @checked]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="bill_discount_form"]//input[@name="bill_discount_type" and @value="fixed"]')->length);
        $this->assertSame(0, $xpath->query('//form[@id="bill_discount_form"]//select | //form[@id="bill_discount_form"]//input[@type="submit"] | //form[@id="bill_discount_form"]//button[@type="submit"]')->length);
    }

    public function testGetRegister_RestoresPercentageBillDiscountToggle(): void
    {
        $employeeId = $this->createCashierEmployee();
        $this->loginAs($employeeId);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $this->seedCartLine(1, '5.00', $itemId);
        $cart = $this->session['sales_cart'];
        $cart[1]['bill_discount'] = '0.50';
        $this->withSession(array_merge($this->session, [
            'sales_cart' => $cart,
            'sales_bill_discount' => ['type' => 'percent', 'value' => '10.0000'],
        ]));

        $response = $this->get('/sales');

        $response->assertStatus(200);
        $document = new DOMDocument();
        @$document->loadHTML($response->getBody());
        $xpath = new DOMXPath($document);
        $this->assertSame(1, $xpath->query('//form[@id="bill_discount_form"]//input[@id="bill_discount_toggle" and not(@checked)]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="bill_discount_form"]//input[@name="bill_discount_type" and @value="percent"]')->length);
        $input = $xpath->query('//form[@id="bill_discount_form"]//input[@name="bill_discount_value"]')->item(0);
        $this->assertNotNull($input);
        $this->assertEqualsWithDelta(10, (float) $input->getAttribute('value'), 0.0001);
    }

}
