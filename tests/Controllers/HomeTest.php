<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Services;
use App\Models\Employee;

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
        parent::setUp();
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
        $admin = $employee->get_info(1);
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
        $admin = $employee->get_info(1);
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
     * Test password validation rejects whitespace-only passwords
     * 
     * @return void
     */
    public function testPasswordMinLength_RejectsWhitespaceOnly(): void
    {
        $this->resetSession();
        
        // Attempt to set password as only whitespace
        $response = $this->post('/home/save', [
            'employee_id' => 1,
            'username' => 'admin',
            'current_password' => 'pointofsale',
            'password' => '        ' // 8 spaces but empty actual password
        ]);
        
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success'], 'Whitespace only password should be rejected');
        $this->assertEquals(-1, $result['id']);
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
        $admin = $employee->get_info(1);
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
        $admin = $employee->get_info(1);
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
        $session = Services::session();
        $session->destroy();
        $session->set('person_id', 1); // Admin user
    }
    
    /**
     * Create a non-admin employee for testing
     * 
     * @return int The person_id of the created employee
     */
    protected function createNonAdminEmployee(): int
    {
        $personData = [
            'first_name'   => 'NonAdmin',
            'last_name'    => 'User',
            'email'        => 'nonadmin@test.com',
            'phone_number' => '555-1234'
        ];
        
        $employeeData = [
            'username'      => 'nonadmin',
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];
        
        $grantsData = [
            ['permission_id' => 'customers', 'menu_group' => 'home'],
            ['permission_id' => 'sales', 'menu_group' => 'home']
        ];
        
        $employeeModel = model(Employee::class);
        $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);
        
        return $employeeModel->get_found_rows('');
    }
    
    /**
     * Login as a specific user
     * 
     * @param int $personId
     * @return void
     */
    protected function loginAs(int $personId): void
    {
        $session = Services::session();
        $session->destroy();
        $session->set('person_id', $personId);
        $session->set('menu_group', 'home');
    }
    
    // ========== BOLA Authorization Tests ==========
    
    /**
     * Test non-admin cannot view admin password change form
     * BOLA vulnerability fix: GHSA-q58g-gg7v-f9rf
     * 
     * @return void
     */
    public function testNonAdminCannotViewAdminPasswordForm(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAs($nonAdminId);
        
        $response = $this->get('/home/changePassword/1');
        
        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }
    
    /**
     * Test non-admin cannot change admin password
     * BOLA vulnerability fix: GHSA-q58g-gg7v-f9rf
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
        $admin = $employee->get_info(1);
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
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAs($nonAdminId);
        
        $response = $this->post('/home/save/' . $nonAdminId, [
            'username' => 'nonadmin',
            'current_password' => 'password123',
            'password' => 'newpassword123'
        ]);
        
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
        
        // Verify password was changed
        $employee = model(Employee::class);
        $user = $employee->get_info($nonAdminId);
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
        $nonAdminId = $this->createNonAdminEmployee();
        $this->resetSession(); // Login as admin
        
        $response = $this->post('/home/save/' . $nonAdminId, [
            'username' => 'nonadmin',
            'current_password' => 'password123',
            'password' => 'adminset123'
        ]);
        
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
        
        // Verify password was changed
        $employee = model(Employee::class);
        $user = $employee->get_info($nonAdminId);
        $this->assertTrue(password_verify('adminset123', $user->password));
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
}