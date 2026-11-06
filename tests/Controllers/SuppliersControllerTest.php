<?php

namespace Tests\Controllers;

use App\Models\Supplier;
use CodeIgniter\Database\Config;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use ReflectionProperty;
use Tests\Support\EmployeeFixtureTrait;

class SuppliersControllerTest extends CIUnitTestCase
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

    protected function createSuppliersEmployee(): int
    {
        return $this->createEmployee(
            grants: [
                ['permission_id' => 'suppliers', 'menu_group' => 'home'],
            ]
        );
    }

    protected function loginAsSuppliersEmployee(int $personId): void
    {
        $this->withSession([
            'person_id'  => $personId,
            'menu_group' => 'home',
        ]);
    }

    // Headers (Content-Disposition etc.) are only built in DownloadResponse::send(),
    // which the test harness never calls; force it so header assertions work.
    // getBody() is also empty for DownloadResponse - its content lives in the
    // private $binary property.
    protected function downloadResponseBody(ResponseInterface $response): string
    {
        if ($response instanceof DownloadResponse) {
            $response->buildHeaders();

            $reflection = new ReflectionProperty($response, 'binary');
            $reflection->setAccessible(true);

            return (string) $reflection->getValue($response);
        }

        return (string) $response->getBody();
    }

    private function seedSupplier(string $companyName): void
    {
        $personData = [
            'first_name'   => 'Richard',
            'last_name'    => 'Roe',
            'gender'       => 'male',
            'email'        => 'supplier.' . uniqid() . '@test.com',
            'phone_number' => '555-9999',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ];

        $supplierData = [
            'company_name'   => $companyName,
            'agency_name'    => '',
            'category'       => '',
            'account_number' => null,
            'tax_id'         => '',
        ];

        model(Supplier::class)->save_supplier($personData, $supplierData);
    }

    public function testGetExportAll_StreamsCsvDownload(): void
    {
        $employeeId = $this->createSuppliersEmployee();
        $this->loginAsSuppliersEmployee($employeeId);

        $companyName = 'Acme Corp ' . uniqid();
        $this->seedSupplier($companyName);

        $response = $this->get('/suppliers/exportAll/csv?search=');

        $response->assertStatus(200);
        $body = $this->downloadResponseBody($response->response());

        $contentDisposition = $response->response()->getHeaderLine('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('suppliers_', $contentDisposition);
        $this->assertStringContainsString($companyName, $body);
    }

    public function testGetExportAll_StreamsPdfDownload(): void
    {
        $employeeId = $this->createSuppliersEmployee();
        $this->loginAsSuppliersEmployee($employeeId);

        $this->seedSupplier('Acme Corp ' . uniqid());

        $response = $this->get('/suppliers/exportAll/pdf?search=');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->response()->getHeaderLine('Content-Type'));
    }

    public function testGetExportAll_DoesNotTruncateToTwentyFiveRows(): void
    {
        $employeeId = $this->createSuppliersEmployee();
        $this->loginAsSuppliersEmployee($employeeId);

        $uniqueSuffix = uniqid();
        $companyNames = [];
        for ($i = 0; $i < 26; $i++) {
            $companyName = "SupplierBulk{$uniqueSuffix}_{$i}";
            $companyNames[] = $companyName;
            $this->seedSupplier($companyName);
        }

        $response = $this->get('/suppliers/exportAll/csv?search=');

        $response->assertStatus(200);
        $body = $this->downloadResponseBody($response->response());

        foreach ($companyNames as $companyName) {
            $this->assertStringContainsString($companyName, $body);
        }
    }
}
