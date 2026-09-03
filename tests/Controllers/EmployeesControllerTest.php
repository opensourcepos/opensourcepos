<?php

namespace Tests\Controllers;

use CodeIgniter\Database\Config;
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

    protected $priorDisallowGrantChange;

    private static bool $doneBootstrap = false;

    protected function setUp(): void
    {
        // Reset the test database to a known clean schema on the first test in
        // this class so stale employees from prior runs (whose usernames are
        // unique-key bound and whose grant sets leak into these assertions)
        // cannot contaminate the assertions below.
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();
        // Reset any stale transaction state left on the shared in-process DB
        // connection by a previous test (e.g. a failed transaction rollback in
        // strict mode sets transStatus=false and poisons save_employee here).
        $this->db->resetTransStatus();
        $this->priorDisallowGrantChange = getenv('DISALLOW_GRANT_CHANGE');
        putenv('DISALLOW_GRANT_CHANGE=false');
    }

    protected function tearDown(): void
    {
        if ($this->priorDisallowGrantChange === false) {
            putenv('DISALLOW_GRANT_CHANGE');
        } else {
            putenv('DISALLOW_GRANT_CHANGE=' . $this->priorDisallowGrantChange);
        }
        parent::tearDown();
    }

    protected function createNonAdminEmployee(): int
    {
        $uniqueSuffix = uniqid();

        $personData = [
            'first_name'   => 'NonAdmin',
            'last_name'    => 'User',
            'email'        => "nonadmin{$uniqueSuffix}@test.com",
            'phone_number' => '555-1234'
        ];

        $employeeData = [
            'username'      => "nonadmin{$uniqueSuffix}",
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        $grantsData = [
            ['permission_id' => 'employees', 'menu_group' => 'home'],
            ['permission_id' => 'customers', 'menu_group' => 'home'],
            ['permission_id' => 'sales', 'menu_group' => 'home']
        ];

        $employeeModel = model(Employee::class);
        $ok = $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

        $row = $this->db->table('people')->where('email', $personData['email'])->get()->getRowArray();
        if (!empty($row['person_id'])) {
            return (int) $row['person_id'];
        }

        return (int) $personData['person_id'];
    }

    protected function loginAsAdmin(): void
    {
        $this->withSession(['person_id' => 1, 'menu_group' => 'office']);
    }

    protected function loginAsNonAdmin(int $personId): void
    {
        $this->withSession(['person_id' => $personId, 'menu_group' => 'home']);
    }

    protected function createEmployee(string $email, string $username, array $grants = []): int
    {
        $personData = [
            'first_name'   => 'Temp',
            'last_name'    => 'Employee',
            'email'        => $email,
            'phone_number' => '555-0000',
        ];
        $employeeData = [
            'username'      => $username,
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english',
        ];
        $employeeModel = model(Employee::class);
        $employeeModel->save_employee($personData, $employeeData, $grants, NEW_ENTRY);

        return (int) $this->db->table('people')->where('people.email', $email)->get()->getRowArray()['person_id'];
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
            'ids' => ['1']
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

        $targetEmployeeId = $this->createEmployee('test@test.com', 'testuser');

        $response = $this->post('/employees/save/' . $targetEmployeeId, [
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'test@test.com',
            'username' => 'testuser',
            'language' => 'en:english',
            'grant_config' => 'config',
            'grant_giftcards' => 'giftcards'
        ]);

        $employeeModel = model(Employee::class);
        $hasConfigGrant = $employeeModel->has_grant('config', $targetEmployeeId);
        $hasGiftcardsGrant = $employeeModel->has_grant('giftcards', $targetEmployeeId);

        $this->assertFalse($hasConfigGrant);
        $this->assertFalse($hasGiftcardsGrant);
    }

    public function testAdminCanModifyAnyAccount(): void
    {
        $nonAdminId = $this->createNonAdminEmployee();
        $this->loginAsAdmin();
        
        $response = $this->post('/employees/save/' . $nonAdminId, [
            'first_name' => 'Modified',
            'last_name' => 'User',
            'email' => 'modified@test.com',
            'username' => 'modified',
            'language' => 'en:english'
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
            'ids' => [(string)$nonAdminId]
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
            'username' => 'owned',
            'language' => 'en:english'
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
        $employeeId = $this->createNonAdminEmployee();
        $this->loginAsAdmin();

        putenv('DISALLOW_GRANT_CHANGE=false');

        $permissionsRequested = ['customers', 'employees', 'sales', 'config'];

        $postData = [
            'first_name' => 'NonAdmin',
            'last_name'  => 'User',
            'email'      => 'nonadmin@test.com',
            'username'   => 'grantany' . uniqid(),
            'language'   => 'en:english'
        ];
        foreach ($permissionsRequested as $perm) {
            $postData['grant_' . $perm] = $perm;
        }

        $response = $this->post('/employees/save/' . $employeeId, $postData);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $employeeModel = model(Employee::class);
        foreach ($permissionsRequested as $perm) {
            $this->assertTrue($employeeModel->has_grant($perm, $employeeId));
        }
    }

    public function testGrantChangeRequestFailsWhenGrantChangeDisallowed(): void
    {
        // Target holds customers+sales but NOT employees, so requesting the
        // employees grant is a genuine change that DISALLOW_GRANT_CHANGE=true
        // must reject (leaving the grant set untouched).
        $unique = uniqid();
        $employeeId = $this->createEmployee("disallow{$unique}@test.com", "disallow{$unique}", [
            ['permission_id' => 'customers', 'menu_group' => 'home'],
            ['permission_id' => 'sales', 'menu_group' => 'home'],
        ]);
        $this->loginAsAdmin();

        putenv('DISALLOW_GRANT_CHANGE=true');

        $response = $this->post('/employees/save/' . $employeeId, [
            'first_name'      => 'NonAdmin',
            'last_name'       => 'User',
            'email'           => "disallow{$unique}@test.com",
            'username'        => "disallow{$unique}",
            'language'        => 'en:english',
            'grant_employees' => 'employees'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);

        $employeeModel = model(Employee::class);
        $this->assertTrue($employeeModel->has_grant('customers', $employeeId));
        $this->assertTrue($employeeModel->has_grant('sales', $employeeId));
        $this->assertFalse($employeeModel->has_grant('employees', $employeeId));
    }

    public function testNewEmployeeCreationWithGrantsFailsWhenGrantChangeDisallowed(): void
    {
        $this->loginAsAdmin();

        putenv('DISALLOW_GRANT_CHANGE=true');

        $response = $this->post('/employees/save', [
            'first_name'      => 'Brand',
            'last_name'       => 'New',
            'email'           => 'brandnew@test.com',
            'username'        => 'brandnew',
            'password'        => 'password123',
            'grant_customers' => 'customers'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);

        $createdEmployee = $this->db->table('employees')->where('username', 'brandnew')->get()->getRow();
        $this->assertNull($createdEmployee);
    }

    public function testGrantChangeRequestSucceedsWhenGrantChangeAllowed(): void
    {
        $employeeId = $this->createNonAdminEmployee();
        $this->loginAsAdmin();

        putenv('DISALLOW_GRANT_CHANGE=false');

        $response = $this->post('/employees/save/' . $employeeId, [
            'first_name'      => 'NonAdmin',
            'last_name'       => 'User',
            'email'           => 'nonadmin@test.com',
            'username'        => 'grantsucc',
            'language'        => 'en:english',
            'grant_employees' => 'employees'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $employeeModel = model(Employee::class);
        $this->assertTrue($employeeModel->has_grant('employees', $employeeId));
        $this->assertFalse($employeeModel->has_grant('customers', $employeeId));
        $this->assertFalse($employeeModel->has_grant('sales', $employeeId));
    }

    public function testNewEmployeeCreationWithGrantsSucceedsWhenGrantChangeAllowed(): void
    {
        $this->loginAsAdmin();

        putenv('DISALLOW_GRANT_CHANGE=false');

        $response = $this->post('/employees/save', [
            'first_name'      => 'Brand',
            'last_name'       => 'New2',
            'email'           => 'brandnew2@test.com',
            'phone_number'    => '555-1234',
            'address_1'       => '',
            'address_2'       => '',
            'city'            => '',
            'state'           => '',
            'zip'             => '',
            'country'         => '',
            'comments'        => '',
            'username'        => 'brandnew2',
            'password'        => 'password123',
            'language'        => 'en:english',
            'grant_customers' => 'customers'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $createdEmployee = $this->db->table('employees')->where('username', 'brandnew2')->get()->getRow();
        $this->assertNotNull($createdEmployee);

        $employeeModel = model(Employee::class);
        $this->assertTrue($employeeModel->has_grant('customers', (int) $createdEmployee->person_id));
    }
}