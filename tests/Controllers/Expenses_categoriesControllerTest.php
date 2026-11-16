<?php

namespace Tests\Controllers;

use App\Models\Expense_category;
use CodeIgniter\Database\Config;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use ReflectionProperty;
use Tests\Support\EmployeeFixtureTrait;

class Expenses_categoriesControllerTest extends CIUnitTestCase
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

    protected function createExpensesCategoriesEmployee(): int
    {
        return $this->createEmployee(
            grants: [
                ['permission_id' => 'expenses_categories', 'menu_group' => 'home'],
            ]
        );
    }

    protected function loginAsExpensesCategoriesEmployee(int $personId): void
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

    public function testGetExportAll_StreamsCsvDownload(): void
    {
        $employeeId = $this->createExpensesCategoriesEmployee();
        $this->loginAsExpensesCategoriesEmployee($employeeId);

        $categoryName = 'Utilities' . uniqid();
        $categoryData = [
            'category_name'        => $categoryName,
            'category_description' => 'Utility bills',
        ];
        model(Expense_category::class)->save_value($categoryData);

        $response = $this->get('/expenses_categories/exportAll/csv?search=');

        $response->assertStatus(200);
        $body = $this->downloadResponseBody($response->response());

        $contentDisposition = $response->response()->getHeaderLine('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('expenses_categories_', $contentDisposition);
        $this->assertStringContainsString($categoryName, $body);
    }

    public function testGetExportAll_StreamsPdfDownload(): void
    {
        $employeeId = $this->createExpensesCategoriesEmployee();
        $this->loginAsExpensesCategoriesEmployee($employeeId);

        $categoryData = [
            'category_name'        => 'Utilities' . uniqid(),
            'category_description' => 'Utility bills',
        ];
        model(Expense_category::class)->save_value($categoryData);

        $response = $this->get('/expenses_categories/exportAll/pdf?search=');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->response()->getHeaderLine('Content-Type'));
    }
}
