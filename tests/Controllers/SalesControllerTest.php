<?php

namespace Tests\Controllers;

use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Services;
use App\Models\Employee;
use Tests\Support\ItemFixtureTrait;

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
     * Inserts a minimal completed sale row directly, bypassing Sale::save_value()
     * (which requires a full cart/inventory/tax pipeline unrelated to this
     * authorization check). Sale::get_info() inner-joins sales_items, so a
     * matching item/sales_items row is required for the sale to be found.
     */
    protected function createSale(int $employeeId): int
    {
        $unique = uniqid();
        $db = \Config\Database::connect();

        $db->table('items')->insert([
            'name'        => "Test Item $unique",
            'category'    => 'Test',
            'description' => 'Test item',
            'cost_price'  => 1,
            'unit_price'  => 1,
            'item_number' => "TEST-$unique",
        ]);
        $itemId = (int) $db->insertID();

        $db->table('sales')->insert([
            'sale_time'      => date('Y-m-d H:i:s'),
            'customer_id'    => null,
            'employee_id'    => $employeeId,
            'comment'        => 'test sale',
            'invoice_number' => null,
        ]);
        $saleId = (int) $db->insertID();

        $db->table('sales_items')->insert([
            'sale_id'            => $saleId,
            'item_id'            => $itemId,
            'line'               => 1,
            'quantity_purchased' => 1,
            'item_cost_price'    => 1,
            'item_unit_price'    => 1,
            'item_location'      => 1,
        ]);

        return $saleId;
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
                    'print_option'          => 1,
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

    /**
     * Regression test for GHSA-92cx-fc8x-7wmm: a malicious `items_taxes.name` value
     * (bypassing the write-side validation, e.g. from data written before this fix)
     * must still render safely on the register page.
     */
    public function testRegisterEscapesMaliciousTaxName(): void
    {
        $cashierId = $this->createCashierEmployee();
        $itemId = $this->createTestItem();

        \Config\Database::connect()->table('items_taxes')->insert([
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
