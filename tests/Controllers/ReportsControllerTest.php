<?php

namespace Tests\Controllers;

use App\Controllers\Reports;
use App\Models\Employee;
use App\Models\Reports\Detailed_receivings;
use App\Models\Reports\Detailed_sales;
use App\Models\Reports\Summary_categories;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\OSPOS;
use ReflectionMethod;
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

    public function testGetPaymentType_IsPrivateHelper(): void
    {
        $paymentTypeReflection = new ReflectionMethod(Reports::class, 'get_payment_type');
        $saleTypeReflection    = new ReflectionMethod(Reports::class, 'get_sale_type_options');

        $this->assertTrue($paymentTypeReflection->isPrivate());
        $this->assertTrue($saleTypeReflection->isPrivate());
    }

    public function testDetailedSales_RendersEmptyDateRangeWithoutError(): void
    {
        $this->loginAs(1);

        $response = $this->get('/reports/detailed_sales/2020-01-01/2020-01-02/complete/all');

        $response->assertStatus(200);
    }

    public function testDetailedReceivings_RendersEmptyDateRangeWithoutError(): void
    {
        $this->loginAs(1);

        $response = $this->get('/reports/detailed_receivings/2020-01-01/2020-01-02/receiving/all');

        $response->assertStatus(200);
    }

    public function testSummaryPayments_RendersEmptyDateRangeWithoutError(): void
    {
        $this->loginAs(1);

        $response = $this->get('/reports/summary_payments/2020-01-01/2020-01-02/complete/all');

        $response->assertStatus(200);
    }

    public function testGraphicalSummaryDiscounts_OutputsUnescapedJsonArrays(): void
    {
        $this->loginAs(1);

        $response = $this->get('/reports/graphical_summary_discounts/2020-01-01/2020-01-02/complete/all/0');

        $response->assertStatus(200);
        $this->assertStringContainsString('labels: []', $response->getBody());
    }

    public function testGraphicalSummaryCategories_HandlesZeroTotalWithoutDivisionByZero(): void
    {
        $this->loginAs(1);

        $mockSummaryCategories = $this->createMock(Summary_categories::class);
        $mockSummaryCategories->method('getData')->willReturn([
            ['category' => 'ZeroCategory', 'total' => 0.0],
        ]);
        $mockSummaryCategories->method('getSummaryData')->willReturn([
            'subtotal' => 0.0,
            'tax'      => 0.0,
            'total'    => 0.0,
            'cost'     => 0.0,
            'profit'   => 0.0,
        ]);
        Factories::injectMock('models', Summary_categories::class, $mockSummaryCategories);

        $response = $this->get('/reports/graphical_summary_categories/2020-01-01/2020-01-02/complete/all');

        $response->assertStatus(200);
        $this->assertStringContainsString('ZeroCategory 0%', $response->getBody());
    }

    public function testGetDataBySaleId_ReturnsEmptyArrayWhenNotFound(): void
    {
        $detailedSales = model(Detailed_sales::class);
        $detailedSales->create([
            'start_date'  => '2020-01-01',
            'end_date'    => '2020-01-02',
            'sale_type'   => 'complete',
            'location_id' => 'all',
        ]);

        $this->assertSame([], $detailedSales->getDataBySaleId(999999));

        $detailedReceivings = model(Detailed_receivings::class);
        $detailedReceivings->create([
            'start_date'     => '2020-01-01',
            'end_date'       => '2020-01-02',
            'receiving_type' => 'receiving',
            'location_id'    => 'all',
        ]);

        $this->assertSame([], $detailedReceivings->getDataByReceivingId('999999'));
    }
}
