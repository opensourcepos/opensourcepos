<?php

namespace Tests\Controllers;

use App\Models\Expense_category;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
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

        $response = $this->get('/expenses_categories/exportAll/csv');

        $response->assertStatus(200);
        $contentDisposition = $response->getHeaderLine('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('expenses_categories_', $contentDisposition);
        $this->assertStringContainsString($categoryName, $response->getBody());
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

        $response = $this->get('/expenses_categories/exportAll/pdf');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->getHeaderLine('Content-Type'));
    }
}
