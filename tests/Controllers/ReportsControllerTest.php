<?php

namespace Tests\Controllers;

use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\Employee;
use Config\OSPOS;
use Tests\Support\DatabaseBootstrapTrait;

/**
 * Regression tests for the reports permission bypass
 *
 * Reports::__construct() previously derived the report method name from
 * $request->getUri()->getSegment(2), which CodeIgniter decodes once, while
 * the router decodes the same path a second time before dispatch. Encoding
 * the report name's underscore as %255F meant the constructor saw no
 * underscore, skipped the has_grant() check entirely, and the router still
 * dispatched to the real (double-decoded) method — letting any authenticated
 * employee read reports they had no grant for.
 */
class ReportsControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();

        config(OSPOS::class)->update_settings();
    }

    /**
     * Create a non-admin employee for testing
     *
     * @param array $overrides
     * @return int
     */
    protected function createNonAdminEmployee(array $overrides = []): int
    {
        $uniqueSuffix = uniqid();

        $personData = [
            'first_name'   => $overrides['first_name'] ?? 'NonAdmin',
            'last_name'    => $overrides['last_name'] ?? 'User',
            'email'        => $overrides['email'] ?? "nonadmin{$uniqueSuffix}@test.com",
            'phone_number' => $overrides['phone_number'] ?? '555-1234'
        ];

        $employeeData = [
            'username'      => $overrides['username'] ?? "nonadmin{$uniqueSuffix}",
            'password'      => password_hash($overrides['password'] ?? 'password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        $grantsData = $overrides['grants'] ?? [
            ['permission_id' => 'customers', 'menu_group' => 'home'],
            ['permission_id' => 'sales', 'menu_group' => 'home']
        ];

        $employeeModel = model(Employee::class);
        $saved = $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

        $this->assertTrue($saved, 'Failed to save non-admin employee fixture.');
        $this->assertArrayHasKey('person_id', $personData, 'Saved employee fixture missing person_id.');

        return (int) $personData['person_id'];
    }

    /**
     * Log in as the given employee
     *
     * @param int $personId
     * @return void
     */
    protected function loginAs(int $personId): void
    {
        $this->withSession([
            'person_id'  => $personId,
            'menu_group' => 'home',
        ]);
    }

    // Headers (Content-Disposition etc.) are only built in DownloadResponse::send(),
    // which the test harness never calls; force it so header assertions work.
    protected function buildDownloadHeaders($response): void
    {
        if ($response instanceof DownloadResponse) {
            $response->buildHeaders();
        }
    }

    /**
     * A non-admin employee with no reports_customers grant must be denied
     * access to the summary_customers report.
     *
     * @return void
     */
    public function testNonAdminWithoutReportGrantIsDeniedSummaryCustomers(): void
    {
        $nonAdminId = $this->createNonAdminEmployee([
            'grants' => [
                ['permission_id' => 'reports_sales', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($nonAdminId);

        $response = $this->get('/reports/summary_customers');

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    /**
     * Regression test for the double-URL-encoding bypass itself: replacing
     * the underscore with %255F must not change the outcome versus the
     * plain request in testNonAdminWithoutReportGrantIsDeniedSummaryCustomers().
     *
     * @return void
     */
    public function testDoubleEncodedUnderscoreCannotBypassSummaryCustomersGrantCheck(): void
    {
        $nonAdminId = $this->createNonAdminEmployee([
            'grants' => [
                ['permission_id' => 'reports_sales', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($nonAdminId);

        $response = $this->get('/reports/summary%255Fcustomers');

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    /**
     * Same bypass attempt against a different report prefix, to confirm the
     * fix isn't narrowly specific to the summary_ prefix's regex path.
     *
     * @return void
     */
    public function testDoubleEncodedUnderscoreCannotBypassDetailedSalesGrantCheck(): void
    {
        $nonAdminId = $this->createNonAdminEmployee([
            'grants' => [
                ['permission_id' => 'reports_customers', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($nonAdminId);

        $response = $this->get('/reports/detailed%255Fsales');

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    /**
     * An employee with the reports_customers grant must be able to access
     * the summary_customers report.
     *
     * @return void
     */
    public function testEmployeeWithReportGrantCanAccessSummaryCustomers(): void
    {
        $employeeId = $this->createNonAdminEmployee([
            'username' => 'reportviewer',
            'email'    => 'reportviewer@test.com',
            'grants'   => [
                ['permission_id' => 'reports', 'menu_group' => 'home'],
                ['permission_id' => 'reports_customers', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($employeeId);

        $response = $this->get('/reports/summary_customers');

        $response->assertStatus(200);
    }

    /**
     * An employee with the base reports grant plus a submodule grant must be
     * able to access the base /reports listing route (no submodule id derivable).
     *
     * @return void
     */
    public function testEmployeeWithReportsGrantCanAccessBaseReportsIndex(): void
    {
        $employeeId = $this->createNonAdminEmployee([
            'username' => 'reportsindexviewer',
            'email'    => 'reportsindexviewer@test.com',
            'grants'   => [
                ['permission_id' => 'reports', 'menu_group' => 'home'],
                ['permission_id' => 'reports_customers', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($employeeId);

        $response = $this->get('/reports');

        $response->assertStatus(200);
    }

    public function testSummarySalesExportAll_StreamsCsvDownloadForGrantedEmployee(): void
    {
        $employeeId = $this->createNonAdminEmployee([
            'username' => 'summarysalesexporter',
            'email'    => 'summarysalesexporter@test.com',
            'grants'   => [
                ['permission_id' => 'reports', 'menu_group' => 'home'],
                ['permission_id' => 'reports_sales', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($employeeId);

        $response = $this->get('/reports/summary_sales/exportAll/csv/2024-01-01/2024-12-31/sales/all');

        $response->assertStatus(200);
        $this->buildDownloadHeaders($response->response());
        $this->assertStringContainsString('attachment', $response->response()->getHeaderLine('Content-Disposition'));
    }

    public function testSummarySalesExportAll_DeniesEmployeeWithoutReportsSalesGrant(): void
    {
        $employeeId = $this->createNonAdminEmployee([
            'username' => 'summarysalesdenied',
            'email'    => 'summarysalesdenied@test.com',
            'grants'   => [
                ['permission_id' => 'reports', 'menu_group' => 'home'],
                ['permission_id' => 'reports_customers', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($employeeId);

        $response = $this->get('/reports/summary_sales/exportAll/csv/2024-01-01/2024-12-31/sales/all');

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    public function testSpecificCustomersExportAll_StreamsCsvDownloadForGrantedEmployee(): void
    {
        $employeeId = $this->createNonAdminEmployee([
            'username' => 'specificcustomersexporter',
            'email'    => 'specificcustomersexporter@test.com',
            'grants'   => [
                ['permission_id' => 'reports', 'menu_group' => 'home'],
                ['permission_id' => 'reports_customers', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($employeeId);

        $response = $this->get('/reports/specific_customers/exportAll/csv/2024-01-01/2024-12-31/1/sales/all');

        $response->assertStatus(200);
        $this->buildDownloadHeaders($response->response());
        $this->assertStringContainsString('attachment', $response->response()->getHeaderLine('Content-Disposition'));
    }

    public function testInventorySummaryExportAll_StreamsCsvDownloadForGrantedEmployee(): void
    {
        $employeeId = $this->createNonAdminEmployee([
            'username' => 'inventorysummaryexporter',
            'email'    => 'inventorysummaryexporter@test.com',
            'grants'   => [
                ['permission_id' => 'reports', 'menu_group' => 'home'],
                ['permission_id' => 'reports_inventory', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($employeeId);

        $response = $this->get('/reports/inventory_summary/exportAll/csv/all/all');

        $response->assertStatus(200);
        $this->buildDownloadHeaders($response->response());
        $this->assertStringContainsString('attachment', $response->response()->getHeaderLine('Content-Disposition'));
    }

    public function testAllReportExportAllMethods_ReturnDownloadableResponseForGrantedEmployee(): void
    {
        $employeeId = $this->createNonAdminEmployee([
            'username' => 'allexportsviewer',
            'email'    => 'allexportsviewer@test.com',
            'grants'   => [
                ['permission_id' => 'reports', 'menu_group' => 'home'],
                ['permission_id' => 'reports_sales', 'menu_group' => 'home'],
                ['permission_id' => 'reports_customers', 'menu_group' => 'home'],
                ['permission_id' => 'reports_categories', 'menu_group' => 'home'],
                ['permission_id' => 'reports_suppliers', 'menu_group' => 'home'],
                ['permission_id' => 'reports_items', 'menu_group' => 'home'],
                ['permission_id' => 'reports_employees', 'menu_group' => 'home'],
                ['permission_id' => 'reports_taxes', 'menu_group' => 'home'],
                ['permission_id' => 'reports_discounts', 'menu_group' => 'home'],
                ['permission_id' => 'reports_payments', 'menu_group' => 'home'],
                ['permission_id' => 'reports_receivings', 'menu_group' => 'home'],
                ['permission_id' => 'reports_inventory', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($employeeId);

        $routes = [
            'summary_salesExportAll'              => '/reports/summary_sales/exportAll/csv/2024-01-01/2024-12-31/sales/all',
            'summary_categoriesExportAll'         => '/reports/summary_categories/exportAll/csv/2024-01-01/2024-12-31/sales/all',
            'summary_expenses_categoriesExportAll' => '/reports/summary_expenses_categories/exportAll/csv/2024-01-01/2024-12-31/sales',
            'summary_customersExportAll'          => '/reports/summary_customers/exportAll/csv/2024-01-01/2024-12-31/sales/all',
            'summary_suppliersExportAll'          => '/reports/summary_suppliers/exportAll/csv/2024-01-01/2024-12-31/sales/all',
            'summary_itemsExportAll'              => '/reports/summary_items/exportAll/csv/2024-01-01/2024-12-31/sales/all',
            'summary_employeesExportAll'          => '/reports/summary_employees/exportAll/csv/2024-01-01/2024-12-31/sales/all',
            'summary_taxesExportAll'              => '/reports/summary_taxes/exportAll/csv/2024-01-01/2024-12-31/sales/all',
            'summary_sales_taxesExportAll'        => '/reports/summary_sales_taxes/exportAll/csv/2024-01-01/2024-12-31/sales/all',
            'summary_discountsExportAll'          => '/reports/summary_discounts/exportAll/csv/2024-01-01/2024-12-31/sales/all/0',
            'summary_paymentsExportAll'           => '/reports/summary_payments/exportAll/csv/2024-01-01/2024-12-31',
            'inventory_lowExportAll'              => '/reports/inventory_low/exportAll/csv',
            'inventory_summaryExportAll'          => '/reports/inventory_summary/exportAll/csv/all/all',
            'detailedItemSalesExportAll'          => '/reports/detailed_item_sales/exportAll/csv/2024-01-01/2024-12-31/sales/all/all',
            'detailed_salesExportAll'             => '/reports/detailed_sales/exportAll/csv/2024-01-01/2024-12-31/sales/all',
            'detailed_receivingsExportAll'        => '/reports/detailed_receivings/exportAll/csv/2024-01-01/2024-12-31/receiving/all',
            'specific_customersExportAll'         => '/reports/specific_customers/exportAll/csv/2024-01-01/2024-12-31/1/sales/all',
            'specific_employeesExportAll'         => '/reports/specific_employees/exportAll/csv/2024-01-01/2024-12-31/1/sales',
            'specific_discountsExportAll'         => '/reports/specific_discounts/exportAll/csv/2024-01-01/2024-12-31/0/sales/0',
            'specific_suppliersExportAll'         => '/reports/specific_suppliers/exportAll/csv/2024-01-01/2024-12-31/1/sales',
        ];

        foreach ($routes as $methodName => $path) {
            $response = $this->get($path);

            $this->assertEquals(200, $response->response()->getStatusCode(), "Expected 200 for $methodName at $path");
            $this->buildDownloadHeaders($response->response());
            $this->assertStringContainsString(
                'attachment',
                $response->response()->getHeaderLine('Content-Disposition'),
                "Expected attachment Content-Disposition for $methodName at $path"
            );
        }
    }
}
