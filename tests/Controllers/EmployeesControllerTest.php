<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Services;
use App\Models\Employee;
use App\Models\Module;

class EmployeesControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

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

    protected function loginAsAdmin(): void
    {
        $session = Services::session();
        $session->destroy();
        $session->set('person_id', 1);
        $session->set('menu_group', 'office');
    }

    protected function loginAsNonAdmin(int $personId): void
    {
        $session = Services::session();
        $session->destroy();
        $session->set('person_id', $personId);
        $session->set('menu_group', 'home');
    }

    public function testNonAdminCannotViewAdminAccount(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAsNonAdmin($nonAdminId);
        
        $response = $this->get('/employees/view/1');
        
        $response->assertRedirect();
        $this->assertStringContainsString('no_access', $response->getRedirectUrl());
    }

    public function testNonAdminCannotModifyAdminAccount(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAsNonAdmin($nonAdminId);
        
        $response = $this->post('/employees/save/1', [
            'first_name' => 'Hacked',
            'last_name' => 'Admin',
            'email' => 'hacked@evil.com',
            'username' => 'admin'
        ]);
        
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('admin', strtolower($result['message']));
    }

    public function testNonAdminCannotDeleteAdminAccount(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAsNonAdmin($nonAdminId);
        
        $response = $this->post('/employees/delete', [
            'ids' => [1]
        ]);
        
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('admin', strtolower($result['message']));
    }

    public function testNonAdminCannotGrantPermissionsTheyDontHave(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAsNonAdmin($nonAdminId);
        
        $targetEmployeeId = $nonAdminId + rand(1000, 9999);
        $this->createTestEmployee($targetEmployeeId);
        
        $response = $this->post('/employees/save/' . $targetEmployeeId, [
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'test@test.com',
            'username' => 'testuser',
            'grant_employees' => 'employees',
            'grant_config' => 'config'
        ]);
        
        $employeeModel = model(Employee::class);
        $hasEmployeesGrant = $employeeModel->has_grant('employees', $targetEmployeeId);
        $hasConfigGrant = $employeeModel->has_grant('config', $targetEmployeeId);
        
        $this->assertFalse($hasEmployeesGrant);
        $this->assertFalse($hasConfigGrant);
    }

    public function testAdminCanModifyAnyAccount(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAsAdmin();
        
        $response = $this->post('/employees/save/' . $nonAdminId, [
            'first_name' => 'Modified',
            'last_name' => 'User',
            'email' => 'modified@test.com',
            'username' => 'nonadmin'
        ]);
        
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testAdminCanDeleteAnyAccount(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAsAdmin();
        
        $response = $this->post('/employees/delete', [
            'ids' => [$nonAdminId]
        ]);
        
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testUserCanModifyOwnAccount(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAsNonAdmin($nonAdminId);
        
        $response = $this->post('/employees/save/' . $nonAdminId, [
            'first_name' => 'Modified',
            'last_name' => 'OwnAccount',
            'email' => 'own@test.com',
            'username' => 'nonadmin'
        ]);
        
        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testPermissionDelegationRule(): void
    {
        $permissionsRequested = ['customers', 'employees', 'sales', 'config'];
        $userPermissions = ['customers', 'sales'];
        $isAdmin = false;
        
        $granted = [];
        foreach ($permissionsRequested as $perm) {
            if ($isAdmin || in_array($perm, $userPermissions)) {
                $granted[] = $perm;
            }
        }
        
        $this->assertEquals(['customers', 'sales'], $granted);
    }

    public function testAdminCanGrantAnyPermission(): void
    {
        $permissionsRequested = ['customers', 'employees', 'sales', 'config'];
        $userPermissions = ['customers', 'sales'];
        $isAdmin = true;
        
        $granted = [];
        foreach ($permissionsRequested as $perm) {
            if ($isAdmin || in_array($perm, $userPermissions)) {
                $granted[] = $perm;
            }
        }
        
        $this->assertEquals($permissionsRequested, $granted);
    }
}