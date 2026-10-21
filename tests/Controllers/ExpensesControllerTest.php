<?php

namespace Tests\Controllers;

use App\Models\Expense;
use App\Models\Expense_category;
use CodeIgniter\Database\Config;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use ReflectionProperty;
use Tests\Support\EmployeeFixtureTrait;

class ExpensesControllerTest extends CIUnitTestCase
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
        $this->db->resetTransStatus();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createExpensesEmployee(): int
    {
        return $this->createEmployee(
            grants: [
                ['permission_id' => 'expenses', 'menu_group' => 'home'],
            ]
        );
    }

    protected function loginAsExpensesEmployee(int $personId): void
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

    private function seedExpense(int $employeeId, string $description): void
    {
        $categoryData = [
            'category_name'        => 'Category' . uniqid(),
            'category_description' => 'desc',
        ];
        model(Expense_category::class)->save_value($categoryData);

        $expenseData = [
            'date'                => date('Y-m-d H:i:s'),
            'supplier_id'         => null,
            'supplier_tax_code'   => '',
            'amount'              => '100.00',
            'tax_amount'          => '0.00',
            'payment_type'        => 'Cash',
            'expense_category_id' => $categoryData['expense_category_id'],
            'description'         => $description,
            'employee_id'         => $employeeId,
            'deleted'             => false,
        ];

        model(Expense::class)->save_value($expenseData);
    }

    public function testGetExportAll_StreamsCsvDownload(): void
    {
        $employeeId = $this->createExpensesEmployee();
        $this->loginAsExpensesEmployee($employeeId);

        $description = 'Office supplies ' . uniqid();
        $this->seedExpense($employeeId, $description);

        $response = $this->get('/expenses/exportAll/csv', [
            'search'     => '',
            'start_date' => '2000-01-01',
            'end_date'   => date('Y-m-d', strtotime('+1 day')),
        ]);

        $response->assertStatus(200);
        $body = $this->downloadResponseBody($response->response());

        $contentDisposition = $response->response()->getHeaderLine('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('expenses_', $contentDisposition);
        $this->assertStringContainsString($description, $body);
    }

    public function testGetExportAll_StreamsPdfDownload(): void
    {
        $employeeId = $this->createExpensesEmployee();
        $this->loginAsExpensesEmployee($employeeId);

        $this->seedExpense($employeeId, 'Office supplies ' . uniqid());

        $response = $this->get('/expenses/exportAll/pdf', [
            'search'     => '',
            'start_date' => '2000-01-01',
            'end_date'   => date('Y-m-d', strtotime('+1 day')),
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->response()->getHeaderLine('Content-Type'));
    }
}
