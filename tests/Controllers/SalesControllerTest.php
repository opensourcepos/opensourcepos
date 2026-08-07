<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Services;
use App\Models\Employee;
use App\Models\Item;

/**
 * Includes regression tests for GHSA-3xf6-8fmq-44wg.
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

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createCashierWithoutChangePriceGrant(): int
    {
        $personData = [
            'first_name'   => 'Cashier',
            'last_name'    => 'NoChangePrice',
            'email'        => 'cashier-nochangeprice@test.com',
            'phone_number' => '555-0001'
        ];

        $employeeData = [
            'username'      => 'cashier_nochangeprice',
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        $grantsData = [
            ['permission_id' => 'sales', 'menu_group' => 'home'],
        ];

        $employeeModel = model(Employee::class);
        $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

        return $employeeModel->get_found_rows('');
    }

    protected function createCashierWithChangePriceGrant(): int
    {
        $personData = [
            'first_name'   => 'Cashier',
            'last_name'    => 'ChangePrice',
            'email'        => 'cashier-changeprice@test.com',
            'phone_number' => '555-0002'
        ];

        $employeeData = [
            'username'      => 'cashier_changeprice',
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        $grantsData = [
            ['permission_id' => 'sales', 'menu_group' => 'home'],
            ['permission_id' => 'sales_change_price', 'menu_group' => 'home'],
        ];

        $employeeModel = model(Employee::class);
        $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

        return $employeeModel->get_found_rows('');
    }

    protected function loginAsEmployee(int $personId): void
    {
        $session = Services::session();
        $session->destroy();
        $session->set('person_id', $personId);
        $session->set('menu_group', 'home');
    }

    protected function createCashierEmployee(): int
    {
        $personData = [
            'first_name'   => 'Cashier',
            'last_name'    => 'NoReports',
            'email'        => 'cashier@test.com',
            'phone_number' => '555-0001'
        ];

        $employeeData = [
            'username'      => 'cashier',
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        // Deliberately grants "sales" (register access) but NOT "reports_sales".
        $grantsData = [
            ['permission_id' => 'sales', 'menu_group' => 'home']
        ];

        $employeeModel = model(Employee::class);
        $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

        return $employeeModel->get_found_rows('');
    }

    protected function createReportsSalesEmployee(): int
    {
        $personData = [
            'first_name'   => 'Supervisor',
            'last_name'    => 'WithReports',
            'email'        => 'supervisor@test.com',
            'phone_number' => '555-0002'
        ];

        $employeeData = [
            'username'      => 'supervisor',
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        $grantsData = [
            ['permission_id' => 'sales', 'menu_group' => 'home'],
            ['permission_id' => 'reports_sales', 'menu_group' => 'home']
        ];

        $employeeModel = model(Employee::class);
        $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

        return $employeeModel->get_found_rows('');
    }

    /**
     * Inserts a minimal completed sale row directly, bypassing Sale::save_value()
     * (which requires a full cart/inventory/tax pipeline unrelated to this
     * authorization check).
     */
    protected function createSale(int $employeeId): int
    {
        $builder = \Config\Database::connect()->table('sales');
        $builder->insert([
            'sale_time'      => date('Y-m-d H:i:s'),
            'customer_id'    => null,
            'employee_id'    => $employeeId,
            'comment'        => 'test sale',
            'invoice_number' => null,
        ]);

        return (int) \Config\Database::connect()->insertID();
    }

    protected function createTestItem(): int
    {
        $itemData = [
            'item_id'               => null,
            'name'                  => 'Test Item',
            'description'           => 'Test Item',
            'category'              => 'Test Category',
            'cost_price'            => 1.00,
            'unit_price'            => 5.00,
            'reorder_level'         => 0,
            'item_number'           => 'TEST-' . uniqid(),
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
            'stock_type'            => HAS_NO_STOCK,
            'deleted'               => 0,
        ];

        $itemModel = model(Item::class);
        $itemModel->save_value($itemData);

        return (int) $itemData['item_id'];
    }

    /**
     * Seeds a single cart line directly in the session, mirroring the shape
     * Sale_lib::add_item() produces, so tests can target postEditItem's
     * authorization branch without exercising the full add-item flow.
     */
    protected function seedCartLine(int $line, string $price, int $itemId): void
    {
        $session = Services::session();
        $session->set('sales_cart', [
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
        ]);
    }

    public function testCashierWithoutGrantCannotChangePrice(): void
    {
        $cashierId = $this->createCashierWithoutChangePriceGrant();
        $this->loginAsEmployee($cashierId);
        $itemId = $this->createTestItem();
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
        $this->loginAsEmployee($cashierId);
        $itemId = $this->createTestItem();
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
        $this->loginAsEmployee($cashierId);
        $itemId = $this->createTestItem();
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

    public function testCashierWithoutReportsSalesCannotGetRow(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAsEmployee($cashierId);

        $response = $this->get('/sales/row/' . $saleId);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testCashierWithoutReportsSalesCannotGetEdit(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAsEmployee($cashierId);

        $response = $this->get('/sales/edit/' . $saleId);

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    public function testCashierWithoutReportsSalesCannotGetReceipt(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAsEmployee($cashierId);

        $response = $this->get('/sales/receipt/' . $saleId);

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    public function testCashierWithoutReportsSalesCannotGetInvoice(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAsEmployee($cashierId);

        $response = $this->get('/sales/invoice/' . $saleId);

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    public function testCashierWithoutReportsSalesCannotSendPdf(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAsEmployee($cashierId);

        $response = $this->get('/sales/sendpdf/' . $saleId);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testCashierWithoutReportsSalesCannotSendReceipt(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAsEmployee($cashierId);

        $response = $this->get('/sales/sendreceipt/' . $saleId);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testCashierWithoutReportsSalesCannotPostSave(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAsEmployee($cashierId);

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

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertSame(lang('Sales.not_authorized'), $result['message']);
    }

    public function testEmployeeWithReportsSalesCanGetRow(): void
    {
        $supervisorId = $this->createReportsSalesEmployee();
        $saleId = $this->createSale($supervisorId);
        $this->loginAsEmployee($supervisorId);

        $response = $this->get('/sales/row/' . $saleId);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertArrayNotHasKey('success', $result);
    }

    public function testEmployeeWithReportsSalesCanGetEdit(): void
    {
        $supervisorId = $this->createReportsSalesEmployee();
        $saleId = $this->createSale($supervisorId);
        $this->loginAsEmployee($supervisorId);

        $response = $this->get('/sales/edit/' . $saleId);

        $response->assertStatus(200);
    }
}
