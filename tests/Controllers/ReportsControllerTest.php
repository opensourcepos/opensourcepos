<?php

namespace Tests\Controllers;

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

    /**
     * Seed a customer whose company_name holds an attacker-controlled XSS payload.
     * company_name is stored raw on save (not nameize()'d), mirroring what an
     * attacker with customer-management permission can persist.
     *
     * @param string $companyName
     * @return int
     */
    protected function createCustomerWithCompanyName(string $companyName): int
    {
        $db = \Config\Database::connect();

        $db->table('people')->insert([
            'first_name'   => 'Mallory',
            'last_name'    => 'Attacker',
            'phone_number' => '555-0100',
            'email'        => 'mallory-' . uniqid() . '@test.com',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ]);
        $personId = (int) $db->insertID();

        $db->table('customers')->insert([
            'person_id'    => $personId,
            'employee_id'  => 1,
            'company_name' => $companyName,
            'points'       => 0,
        ]);

        return $personId;
    }

    /**
     * A customer's company_name may hold an attacker-controlled XSS payload.
     * The Specific Customer report input page must render it as escaped text in
     * the dropdown label, never as live HTML (form_dropdown() does not escape).
     *
     * @return void
     */
    public function testSpecificCustomerReportEscapesCustomerCompanyName(): void
    {
        $payload = '<img src=x onerror=alert(1)>';
        $this->createCustomerWithCompanyName($payload);

        $employeeId = $this->createNonAdminEmployee([
            'username' => 'specificreportviewer',
            'email'    => 'specificreportviewer@test.com',
            'grants'   => [
                ['permission_id' => 'reports', 'menu_group' => 'home'],
                ['permission_id' => 'reports_customers', 'menu_group' => 'home']
            ]
        ]);
        $this->loginAs($employeeId);

        $response = $this->get('/reports/specific_customers');

        $response->assertStatus(200);
        $body = (string) $response->getBody();

        $this->assertStringContainsString('Mallory', $body, 'Seeded customer missing from the Specific Customer report dropdown.');
        $this->assertStringNotContainsString($payload, $body, 'Raw XSS payload leaked into the Specific Customer report dropdown.');
    }
}
