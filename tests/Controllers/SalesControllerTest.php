<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\Employee;

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

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function createCashierEmployee(): int
    {
        $unique = uniqid();

        $personData = [
            'first_name'   => 'Cashier',
            'last_name'    => 'NoReports',
            'email'        => "cashier.$unique@test.com",
            'phone_number' => '555-0001'
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
        $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

        return (int) $personData['person_id'];
    }

    protected function createReportsSalesEmployee(): int
    {
        $unique = uniqid();

        $personData = [
            'first_name'   => 'Supervisor',
            'last_name'    => 'WithReports',
            'email'        => "supervisor.$unique@test.com",
            'phone_number' => '555-0002'
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
        $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

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

    public function testCashierWithoutReportsSalesCannotGetRow(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/row/' . $saleId);

        $response->assertStatus(200);
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

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testCashierWithoutReportsSalesCannotSendReceipt(): void
    {
        $cashierId = $this->createCashierEmployee();
        $saleId = $this->createSale($cashierId);
        $this->loginAs($cashierId);

        $response = $this->get('/sales/sendreceipt/' . $saleId);

        $response->assertStatus(200);
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

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertSame(lang('Sales.not_authorized'), $result['message']);
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
}
