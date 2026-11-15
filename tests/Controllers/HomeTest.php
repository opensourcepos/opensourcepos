<?php

namespace Tests\Controllers;

use App\Models\Employee;
use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Filters;
use Config\OSPOS;
use Config\Session as SessionConfig;
use ReflectionClass;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;

/**
 * Test suite for Home controller password validation
 *
 * Tests the critical fix for password minimum length validation bypass
 * Issue: Code was checking hashed password length (always 60 chars) instead of actual password
 * Fix: Validate raw password length BEFORE hashing
 */
class HomeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use EmployeeFixtureTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();

        config(OSPOS::class)->update_settings();
    }

    protected function tearDown(): void
    {
        Factories::reset('config');
        Services::resetSingle('filters');
        Services::resetSingle('security');
        Services::resetSingle('session');

        parent::tearDown();
    }

    private function injectDestroyTrackingSession(): Session
    {
        $sessionConfig = config(SessionConfig::class);
        $handler = new ArrayHandler($sessionConfig, '127.0.0.1');
        $sessionSpy = new class ($handler, $sessionConfig) extends Session {
            public bool $destroyed = false;

            public function destroy(): void
            {
                $this->destroyed = true;
                $_SESSION = [];
            }
        };
        $sessionSpy->setLogger(Services::logger());
        Services::injectMock('session', $sessionSpy);

        return $sessionSpy;
    }

    /**
     * Test password validation rejects passwords shorter than 8 characters
     *
     * @return void
     */
    public function testPasswordMinLength_Rejects7Characters(): void
    {
        $this->resetSession();

        // Attempt to change password to 7 characters
        $response = $this->post('/home/save', [
            'employee_id' => 1,
            'username' => 'admin',
            'current_password' => 'pointofsale',
            'password' => '1234567' // 7 characters
        ]);

        // Assert failure response
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success'], 'Password with 7 chars should be rejected');
        $this->assertEquals(-1, $result['id']);

        // Verify password was not changed
        $employee = model(Employee::class);
        $admin = $employee->getInfo(1);
        $this->assertTrue(password_verify('pointofsale', $admin->password),
            'Password should not have been changed');
    }

    /**
     * Test password validation accepts passwords with exactly 8 characters
     *
     * @return void
     */
    public function testPasswordMinLength_Accepts8Characters(): void
    {
        $this->resetSession();

        // Change password to exactly 8 characters
        $response = $this->post('/home/save', [
            'employee_id' => 1,
            'username' => 'admin',
            'current_password' => 'pointofsale',
            'password' => 'pa$$w0rd' // Exactly 8 characters including special chars
        ]);

        // Assert success response
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success'], 'Password with 8 chars should be accepted');
        $this->assertEquals(1, $result['id']);

        // Verify password was changed
        $employee = model(Employee::class);
        $admin = $employee->getInfo(1);
        $this->assertTrue(password_verify('pa$$w0rd', $admin->password),
            'Password with 8 chars should be accepted');

        // Restore original password
        $employee->change_password([
            'username' => 'admin',
            'password' => password_hash('pointofsale', PASSWORD_DEFAULT),
            'hash_version' => 2
        ], 1);
    }

    /**
     * Test password validation rejects empty password
     *
     * @return void
     */
    public function testPasswordMinLength_RejectsEmptyString(): void
    {
        $this->resetSession();

        // Attempt to set empty password
        $response = $this->post('/home/save', [
            'employee_id' => 1,
            'username' => 'admin',
            'current_password' => 'pointofsale',
            'password' => '' // Empty string
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success'], 'Empty password should be rejected');
        $this->assertEquals(-1, $result['id']);
    }

    /**
     * Password validation is a raw strlen() check with no whitespace
     * handling, so a password consisting entirely of spaces still counts
     * toward the length minimum.
     *
     * @return void
     */
    public function testPasswordMinLength_WhitespaceOnlyPasswordCountsTowardLength(): void
    {
        $this->resetSession();

        try {
            $response = $this->post('/home/save', [
                'employee_id' => 1,
                'username' => 'admin',
                'current_password' => 'pointofsale',
                'password' => '        ' // 8 spaces: exactly meets the byte-length minimum
            ]);

            $response->assertStatus(200);
            $result = json_decode($response->getJSON(), true);
            $this->assertTrue($result['success'], 'strlen()-based validation accepts 8 spaces as meeting the minimum length');
        } finally {
            // Restore original password
            $employee = model(Employee::class);
            $employee->change_password([
                'username' => 'admin',
                'password' => password_hash('pointofsale', PASSWORD_DEFAULT),
                'hash_version' => 2
            ], 1);
        }
    }

    /**
     * Test password validation accepts passwords with special characters
     * as long as they meet minimum length
     *
     * @return void
     */
    public function testPasswordMinLength_AcceptsSpecialCharacters(): void
    {
        $this->resetSession();

        $specialPassword = 'Str0ng!@#$'; // 11 characters with special chars

        $response = $this->post('/home/save', [
            'employee_id' => 1,
            'username' => 'admin',
            'current_password' => 'pointofsale',
            'password' => $specialPassword
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success'], 'Password with special chars should be accepted');
        $this->assertEquals(1, $result['id']);

        // Verify password works
        $employee = model(Employee::class);
        $admin = $employee->getInfo(1);
        $this->assertTrue(password_verify($specialPassword, $admin->password));

        // Restore original password
        $employee->change_password([
            'username' => 'admin',
            'password' => password_hash('pointofsale', PASSWORD_DEFAULT),
            'hash_version' => 2
        ], 1);
    }

    /**
     * Regression test: Verify previous vulnerable behavior is fixed
     *
     * Before fix: 1-character passwords like "a" were accepted because
     * code checked len(hashed_password) which is always 60 for bcrypt
     * After fix: Raw password is validated before hashing
     *
     * @return void
     */
    public function testPasswordMinLength_RejectsPreviousBehavior(): void
    {
        $this->resetSession();

        // Attempt the previously vulnerable case: single character password
        $response = $this->post('/home/save', [
            'employee_id' => 1,
            'username' => 'admin',
            'current_password' => 'pointofsale',
            'password' => 'a' // Previously allowed due to bug
        ]);

        // This should now fail
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success'], 'Single character password should be rejected (CVE fix)');
        $this->assertEquals(-1, $result['id']);

        // Verify password was NOT changed
        $employee = model(Employee::class);
        $admin = $employee->getInfo(1);
        $this->assertTrue(password_verify('pointofsale', $admin->password),
            'Single character password should be rejected (CVE fix)');
    }

    /**
     * Helper method to reset session
     *
     * @return void
     */
    protected function resetSession(): void
    {
        $this->withSession(['person_id' => 1]); // Admin user
    }

    /**
     * Create a non-admin employee for testing
     *
     * @param array $overrides Optional overrides for username, email, password, etc.
     * @return int The person_id of the created employee
     */
    protected function createNonAdminEmployee(array $overrides = []): int
    {
        $uniqueSuffix = uniqid();

        $grantsData = $overrides['grants'] ?? [
            ['permission_id' => 'home', 'menu_group' => 'home'],
            ['permission_id' => 'customers', 'menu_group' => 'home'],
            ['permission_id' => 'sales', 'menu_group' => 'home'],
        ];

        $employeeOverrides = [];
        if (array_key_exists('password', $overrides)) {
            $employeeOverrides['password'] = password_hash($overrides['password'], PASSWORD_DEFAULT);
        }

        return $this->createEmployee(
            first_name: $overrides['first_name'] ?? 'NonAdmin',
            last_name: $overrides['last_name'] ?? 'User',
            email: $overrides['email'] ?? "nonadmin{$uniqueSuffix}@test.com",
            username: $overrides['username'] ?? "nonadmin{$uniqueSuffix}",
            phone_number: $overrides['phone_number'] ?? '555-1234',
            grants: $grantsData,
            employee: $employeeOverrides,
        );
    }

    /**
     * Login as a specific user
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

    // ========== BOLA Authorization Tests ==========

    /**
     * Test non-admin cannot view admin password change form
     * BOLA vulnerability fix.
     *
     * @return void
     */
    public function testNonAdminCannotViewAdminPasswordForm(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAs($nonAdminId);

        $response = $this->get('/home/changePassword/1');

        $response->assertStatus(403);
    }

    /**
     * Test non-admin cannot change admin password
     * BOLA vulnerability fix.
     *
     * @return void
     */
    public function testNonAdminCannotChangeAdminPassword(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAs($nonAdminId);

        $response = $this->post('/home/save/1', [
            'username' => 'admin',
            'current_password' => 'pointofsale',
            'password' => 'hacked123'
        ]);

        $response->assertStatus(403);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);

        // Verify admin password was NOT changed
        $employee = model(Employee::class);
        $admin = $employee->getInfo(1);
        $this->assertTrue(password_verify('pointofsale', $admin->password),
            'Admin password should not have been changed by non-admin');
    }

    /**
     * Test user can view their own password change form
     *
     * @return void
     */
    public function testUserCanViewOwnPasswordForm(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAs($nonAdminId);

        $response = $this->get('/home/changePassword/' . $nonAdminId);

        $response->assertStatus(200);
        $response->assertSee('nonadmin'); // Username should be visible
    }

    /**
     * Test user can change their own password
     *
     * @return void
     */
    public function testUserCanChangeOwnPassword(): void
    {
        $nonAdminId = $this->createNonAdminEmployee(['username' => 'nonadminchangeown']);
        $this->loginAs($nonAdminId);

        $response = $this->post('/home/save/' . $nonAdminId, [
            'username' => 'nonadminchangeown',
            'current_password' => 'password123',
            'password' => 'newpassword123'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        // Verify password was changed
        $employee = model(Employee::class);
        $user = $employee->getInfo($nonAdminId);
        $this->assertTrue(password_verify('newpassword123', $user->password));
    }

    /**
     * Test admin can view any user's password form
     *
     * @return void
     */
    public function testAdminCanViewAnyPasswordForm(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->resetSession(); // Login as admin

        $response = $this->get('/home/changePassword/' . $nonAdminId);

        $response->assertStatus(200);
        $response->assertSee('nonadmin');
    }

    /**
     * Test admin can change any user's password
     *
     * @return void
     */
    public function testAdminCanChangeAnyPassword(): void
    {
        $nonAdminId = $this->createNonAdminEmployee(['username' => 'nonadminadminchange']);
        $this->resetSession(); // Login as admin

        $response = $this->post('/home/save/' . $nonAdminId, [
            'username'         => 'nonadminadminchange',
            'current_password' => 'pointofsale',
            'password'         => 'adminset123',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        // Verify password was changed
        $employee = model(Employee::class);
        $user     = $employee->getInfo($nonAdminId);
        $this->assertTrue(password_verify('adminset123', $user->password));
    }

    public function testPostSave_VerifiesCurrentUsersOwnPassword(): void
    {
        $nonAdminId = $this->createNonAdminEmployee([
            'username' => 'boundcheckuser',
            'password' => 'ownpass123',
        ]);
        $this->loginAs($nonAdminId);

        $response = $this->post('/home/save/' . $nonAdminId, [
            'username'         => 'admin',
            'current_password' => 'pointofsale',
            'password'         => 'newpass12345',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);

        $employee = model(Employee::class);
        $user     = $employee->getInfo($nonAdminId);
        $this->assertTrue(password_verify('ownpass123', $user->password));
    }

    public function testPostSave_PreservesUsernameWhenPostUsernameTampered(): void
    {
        $nonAdminId = $this->createNonAdminEmployee([
            'username' => 'originalusername',
            'password' => 'password123',
        ]);
        $this->loginAs($nonAdminId);

        $response = $this->post('/home/save/' . $nonAdminId, [
            'username'         => 'tamperedusername',
            'current_password' => 'password123',
            'password'         => 'updatedpass123',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $employee = model(Employee::class);
        $user     = $employee->getInfo($nonAdminId);
        $this->assertSame('originalusername', $user->username);
        $this->assertTrue(password_verify('updatedpass123', $user->password));
    }

    /**
     * Test default employee_id parameter uses current user
     *
     * @return void
     */
    public function testDefaultEmployeeIdUsesCurrentUser(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAs($nonAdminId);

        // Calling without employee_id should use current user
        $response = $this->get('/home/changePassword');

        $response->assertStatus(200);
        $response->assertSee('nonadmin');
    }

    /**
     * Test non-admin cannot view another non-admin's password form
     * IDOR vulnerability fix.
     *
     * @return void
     */
    public function testNonAdminCannotViewOtherNonAdminPasswordForm(): void
    {
        $nonAdminId1 = $this->createNonAdminEmployee();
        $this->loginAs($nonAdminId1);

        $otherUserId = $this->createNonAdminEmployee([
            'username' => 'otheruser',
            'email' => 'other@test.com',
            'password' => 'password456'
        ]);

        $response = $this->get('/home/changePassword/' . $otherUserId);

        $response->assertStatus(403);
    }

    /**
     * Test non-admin cannot change another non-admin's password
     * IDOR vulnerability fix.
     *
     * @return void
     */
    public function testNonAdminCannotChangeOtherNonAdminPassword(): void
    {
        $nonAdminId1 = $this->createNonAdminEmployee();
        $this->loginAs($nonAdminId1);

        $victimId = $this->createNonAdminEmployee([
            'username' => 'victimuser',
            'email' => 'victim@test.com',
            'password' => 'victimpass123'
        ]);

        $response = $this->post('/home/save/' . $victimId, [
            'username' => 'victimuser',
            'current_password' => 'victimpass123',
            'password' => 'hacked123456'
        ]);

        $response->assertStatus(403);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);

        // Verify victim's password was NOT changed
        $employeeModel = model(Employee::class);
        $victim = $employeeModel->getInfo($victimId);
        $this->assertTrue(password_verify('victimpass123', $victim->password),
            'Non-admin should not be able to change another non-admin password');
    }

    /**
     * Regression test: Home::__construct() previously
     * read the raw (single-decoded) URI segment to decide whether to skip
     * Secure_Controller's module-grant check for 'logout'. A route whose
     * double-decoded method name resolves to 'logout' must still be treated
     * as logout consistently - using the router's fully-resolved method name
     * removes any single-vs-double-decode mismatch as an attack surface.
     *
     * @return void
     */
    public function testLogoutStillBypassesGrantCheckAfterFix(): void
    {
        $sessionSpy = $this->injectDestroyTrackingSession();
        $nonAdminId = $this->createNonAdminEmployee([
            'username' => 'logoutnograntuser',
            'email' => 'logoutnograntuser@test.com',
            'grants' => []
        ]);
        $this->loginAs($nonAdminId);

        $response = $this->post('/home/logout');

        $response->assertRedirectTo('login');
        $this->assertTrue($sessionSpy->destroyed);
        $this->assertNull(session('person_id'));
        $this->assertFalse(model(Employee::class)->is_logged_in());
    }

    public function testPostLogout_AcceptsMixedCaseSegmentWithoutHomeGrant(): void
    {
        $sessionSpy = $this->injectDestroyTrackingSession();
        $nonAdminId = $this->createNonAdminEmployee([
            'username' => 'logoutmixedcaseuser',
            'email' => 'logoutmixedcaseuser@test.com',
            'grants' => [],
        ]);
        $this->loginAs($nonAdminId);

        $response = $this->post('/home/Logout');

        $response->assertRedirectTo('login');
        $this->assertTrue($sessionSpy->destroyed);
        $this->assertNull(session('person_id'));
    }

    public function testPostLogout_AcceptsUrlEncodedSegmentWithoutHomeGrant(): void
    {
        $nonAdminId = $this->createNonAdminEmployee([
            'username' => 'logoutencodeduser',
            'email' => 'logoutencodeduser@test.com',
            'grants' => [],
        ]);

        foreach (['/home/%6Cogout', '/home/%4Cogout'] as $encodedUri) {
            $sessionSpy = $this->injectDestroyTrackingSession();
            $this->loginAs($nonAdminId);

            $response = $this->post($encodedUri);

            $response->assertRedirectTo('login');
            $this->assertTrue($sessionSpy->destroyed);
            $this->assertNull(session('person_id'));
        }
    }

    public function testPostLogout_RejectsGetRequest(): void
    {
        $nonAdminId = $this->createNonAdminEmployee([
            'username' => 'logoutgetuser',
            'email' => 'logoutgetuser@test.com',
            'grants' => [],
        ]);
        $this->loginAs($nonAdminId);

        $this->expectException(PageNotFoundException::class);
        $this->get('/home/logout');
    }

    public function testPostLogout_RejectsTokenlessRequestWhenCsrfEnabled(): void
    {
        $defaultProperties = (new ReflectionClass(Filters::class))->getDefaultProperties();
        $filtersConfig = config(Filters::class);
        $filtersConfig->globals['before']['csrf'] = $defaultProperties['globals']['before']['csrf'];
        Services::resetSingle('filters');
        Services::resetSingle('security');

        $this->loginAs(1);

        $this->expectException(SecurityException::class);
        $this->post('/home/logout');
    }

    public function testGetIndex_RendersPostLogoutFormWithCsrfTokenWhenCsrfEnabled(): void
    {
        $defaultProperties = (new ReflectionClass(Filters::class))->getDefaultProperties();
        $filtersConfig = config(Filters::class);
        $filtersConfig->globals['before']['csrf'] = $defaultProperties['globals']['before']['csrf'];
        Services::resetSingle('filters');
        Services::resetSingle('security');

        $this->loginAs(1);

        $response = $this->get('/home');
        $response->assertStatus(200);

        $html = (string) $response->response()->getBody();
        $this->assertMatchesRegularExpression('/<form\b[^>]*action="[^"]*home\/logout"[^>]*id="logout_form"[^>]*method="post"/i', $html);
        $this->assertStringContainsString('name="' . csrf_token() . '"', $html);
        $this->assertDoesNotMatchRegularExpression('/<a\b[^>]*href="[^"]*home\/logout"/i', $html);
    }

    public function testGetIndex_RendersAmpersandAndApostropheInHeaderWithoutDoubleEscaping(): void
    {
        $encodedEmployeeId = $this->createNonAdminEmployee([
            'first_name' => filter_var('Smith & Wesson', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'last_name'  => filter_var("O'Connor", FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        ]);
        $this->loginAs($encodedEmployeeId);

        $encodedResponse = $this->get('/home');
        $encodedResponse->assertStatus(200);
        $encodedHtml = (string) $encodedResponse->response()->getBody();

        $this->assertStringContainsString('Smith &amp; Wesson', $encodedHtml);
        $this->assertStringNotContainsString('&amp;amp;', $encodedHtml);
        $this->assertStringNotContainsString('&amp;#', $encodedHtml);
        $this->assertMatchesRegularExpression('/O(?:&#0?39;|&apos;)Connor/', $encodedHtml);

        $rawEmployeeId = $this->createNonAdminEmployee([
            'first_name' => 'Ben & Jerry',
            'last_name'  => "D'Angelo",
        ]);
        $this->loginAs($rawEmployeeId);

        $rawResponse = $this->get('/home');
        $rawResponse->assertStatus(200);
        $rawHtml = (string) $rawResponse->response()->getBody();

        $this->assertStringContainsString('Ben &amp; Jerry', $rawHtml);
        $this->assertStringNotContainsString('&amp;amp;', $rawHtml);
        $this->assertMatchesRegularExpression('/D(?:&#0?39;|&apos;)Angelo/', $rawHtml);
    }

    public function testGetIndex_EscapesHostileMarkupInHeaderEmployeeName(): void
    {
        $rawHostileId = $this->createNonAdminEmployee([
            'first_name' => '<script>alert(1)</script>',
            'last_name'  => '<img src=x onerror=alert(1)>',
        ]);
        $this->loginAs($rawHostileId);

        $rawResponse = $this->get('/home');
        $rawResponse->assertStatus(200);
        $rawHtml = (string) $rawResponse->response()->getBody();

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $rawHtml);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $rawHtml);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $rawHtml);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $rawHtml);

        $sanitizedHostileId = $this->createNonAdminEmployee([
            'first_name' => filter_var('<script>alert(2)</script>', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'last_name'  => 'Tester',
        ]);
        $this->loginAs($sanitizedHostileId);

        $sanitizedResponse = $this->get('/home');
        $sanitizedResponse->assertStatus(200);
        $sanitizedHtml = (string) $sanitizedResponse->response()->getBody();

        $this->assertStringContainsString('&lt;script&gt;alert(2)&lt;/script&gt;', $sanitizedHtml);
        $this->assertStringNotContainsString('<script>alert(2)</script>', $sanitizedHtml);
    }

    /**
     * A non-'logout' Home method must still enforce Secure_Controller's
     * module-grant check (i.e. parent::__construct() is not skipped) for a
     * logged-in employee with no 'home' grant.
     *
     * @return void
     */
    public function testNonLogoutMethodStillEnforcesModuleGrantCheck(): void
    {
        $nonAdminId = $this->createNonAdminEmployee([
            'username' => 'nogranthome',
            'email' => 'nogranthome@test.com',
            'grants' => []
        ]);
        $this->loginAs($nonAdminId);

        $response = $this->get('/home/changePassword/' . $nonAdminId);

        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }
}
