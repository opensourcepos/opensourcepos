<?php

namespace Tests\Controllers;

use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Services;
use App\Models\Employee;
use Config\Database;
use Tests\Support\ItemFixtureTrait;
use Tests\Support\SaleFixtureTrait;

/**
 * Regression tests for GHSA-3xf6-8fmq-44wg.
 *
 * A cashier holding only the base "sales" grant (no "reports_sales") must
 * not be able to reach the per-sale endpoints that getManage() gates
 * behind reports_sales: getRow, getEdit, postSave, getReceipt, getInvoice,
 * getSendPdf, getSendReceipt.
 */
class SalesControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use ItemFixtureTrait;
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

    protected function createCashierEmployee(): int
    {
        $unique = uniqid();

        $personData = [
            'first_name'   => 'Cashier',
            'last_name'    => 'NoReports',
            'email'        => "cashier.$unique@test.com",
            'phone_number' => '555-0001',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ];

        $employeeData = [
            'username'      => "cashier.$unique",
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        // Deliberately grants "sales" (register access) but NOT "reports_sales".
        // "sales_stock" is also required: Employee::has_module_grant('sales', ...)
        // treats the bare "sales" grant as insufficient once any sales_* submodule
        // permission exists in the permissions table (see has_subpermissions()).
        $grantsData = [
            ['permission_id' => 'sales', 'menu_group' => 'home'],
            ['permission_id' => 'sales_stock', 'menu_group' => 'home']
        ];

        $employeeModel = model(Employee::class);
        $this->assertTrue($employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY));

        return (int) $personData['person_id'];
    }

    protected function createReportsSalesEmployee(): int
    {
        $unique = uniqid();

        $personData = [
            'first_name'   => 'Supervisor',
            'last_name'    => 'WithReports',
            'email'        => "supervisor.$unique@test.com",
            'phone_number' => '555-0002',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ];

        $employeeData = [
            'username'      => "supervisor.$unique",
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        $grantsData = [
            ['permission_id' => 'sales', 'menu_group' => 'home'],
            ['permission_id' => 'sales_stock', 'menu_group' => 'home'],
            ['permission_id' => 'reports_sales', 'menu_group' => 'home']
        ];

        $employeeModel = model(Employee::class);
        $this->assertTrue($employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY));

        return (int) $personData['person_id'];
    }

    protected function createCashierWithoutChangePriceGrant(): int
    {
        $unique = uniqid();

        $personData = [
            'first_name'   => 'Cashier',
            'last_name'    => 'NoChangePrice',
            'email'        => "cashier-nochangeprice.$unique@test.com",
            'phone_number' => '555-0001',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ];

        $employeeData = [
            'username'      => "cashier_nochangeprice.$unique",
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        // "sales_stock" is required alongside "sales": see the has_module_grant/
        // has_subpermissions note on createCashierEmployee() above.
        $grantsData = [
            ['permission_id' => 'sales', 'menu_group' => 'home'],
            ['permission_id' => 'sales_stock', 'menu_group' => 'home'],
        ];

        $employeeModel = model(Employee::class);
        $this->assertTrue($employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY));

        return (int) $personData['person_id'];
    }

    protected function createCashierWithChangePriceGrant(): int
    {
        $unique = uniqid();

        $personData = [
            'first_name'   => 'Cashier',
            'last_name'    => 'ChangePrice',
            'email'        => "cashier-changeprice.$unique@test.com",
            'phone_number' => '555-0002',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ];

        $employeeData = [
            'username'      => "cashier_changeprice.$unique",
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        $grantsData = [
            ['permission_id' => 'sales', 'menu_group' => 'home'],
            ['permission_id' => 'sales_stock', 'menu_group' => 'home'],
            ['permission_id' => 'sales_change_price', 'menu_group' => 'home'],
        ];

        $employeeModel = model(Employee::class);
        $this->assertTrue($employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY));

        return (int) $personData['person_id'];
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

    public function testCashierWithoutReportsSalesCannotGetSearch(): void
    {
        $cashierId = $this->createCashierEmployee();
        $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/search');

        $response->assertStatus(403);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
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
}
