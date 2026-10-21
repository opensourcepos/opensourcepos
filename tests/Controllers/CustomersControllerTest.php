<?php

namespace Tests\Controllers;

use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\Customer;
use Tests\Support\EmployeeFixtureTrait;

class CustomersControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use EmployeeFixtureTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $refresh = false;
    protected $namespace = null;

    private static $doneBootstrap = false;

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

    protected function createCustomersEmployee(): int
    {
        return $this->createEmployee(
            grants: [
                ['permission_id' => 'customers', 'menu_group' => 'home'],
            ]
        );
    }

    protected function loginAsCustomersEmployee(int $personId): void
    {
        $this->withSession([
            'person_id'  => $personId,
            'menu_group' => 'home',
        ]);
    }

    public function testGetExportAll_StreamsCsvDownload(): void
    {
        $employeeId = $this->createCustomersEmployee();
        $this->loginAsCustomersEmployee($employeeId);

        $lastName = 'Exportsky' . uniqid();
        $personData = [
            'first_name'   => 'Export',
            'last_name'    => $lastName,
            'gender'       => 1,
            'email'        => 'export.' . uniqid() . '@test.com',
            'phone_number' => '',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => ''
        ];
        $customerData = [
            'consent'           => true,
            'account_number'    => null,
            'tax_id'            => '',
            'company_name'      => null,
            'discount'          => 0.00,
            'discount_type'     => 0,
            'package_id'        => null,
            'taxable'           => false,
            'date'              => date('Y-m-d H:i:s'),
            'employee_id'       => $employeeId,
            'sales_tax_code_id' => null
        ];

        $customer = model(Customer::class);
        $this->assertTrue($customer->save_customer($personData, $customerData));

        $response = $this->get('/customers/exportAll/csv');

        $response->assertStatus(200);
        $contentDisposition = $response->getHeaderLine('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('customers_', $contentDisposition);

        $body = (string) $response->getBody();
        $this->assertStringContainsString($lastName, $body);
    }

    public function testGetExportAll_StreamsPdfDownload(): void
    {
        $employeeId = $this->createCustomersEmployee();
        $this->loginAsCustomersEmployee($employeeId);

        $lastName = 'Exportsky' . uniqid();
        $personData = [
            'first_name'   => 'Export',
            'last_name'    => $lastName,
            'gender'       => 1,
            'email'        => 'export.' . uniqid() . '@test.com',
            'phone_number' => '',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => ''
        ];
        $customerData = [
            'consent'           => true,
            'account_number'    => null,
            'tax_id'            => '',
            'company_name'      => null,
            'discount'          => 0.00,
            'discount_type'     => 0,
            'package_id'        => null,
            'taxable'           => false,
            'date'              => date('Y-m-d H:i:s'),
            'employee_id'       => $employeeId,
            'sales_tax_code_id' => null
        ];

        $customer = model(Customer::class);
        $this->assertTrue($customer->save_customer($personData, $customerData));

        $response = $this->get('/customers/exportAll/pdf');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->getHeaderLine('Content-Type'));

        $body = (string) $response->getBody();
        $this->assertNotEmpty($body);
        $this->assertStringStartsWith('%PDF-', $body);
    }
}
