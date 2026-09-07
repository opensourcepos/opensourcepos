<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\Employee;

class EmployeeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace    = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIsAdminReturnsTrueForPersonId1(): void
    {
        $employeeModel = model(Employee::class);

        $result = $employeeModel->isAdmin(1);

        $this->assertTrue($result);
    }

    public function testIsAdminReturnsTrueForEmployeeWithAllPermissions(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['has_grant'])
            ->getMock();

        $employeeModel->method('has_grant')
            ->willReturn(true);

        $result = $employeeModel->isAdmin(2);

        $this->assertTrue($result);
    }

    public function testIsAdminReturnsFalseWhenMissingPermissions(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['has_grant'])
            ->getMock();

        $employeeModel->method('has_grant')
            ->willReturnCallback(function($permissionId, $personId) {
                return $permissionId !== 'config';
            });

        $result = $employeeModel->isAdmin(3);

        $this->assertFalse($result);
    }

    public function testCanModifyEmployeeReturnsTrueForOwnAccount(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['isAdmin'])
            ->getMock();

        $employeeModel->method('isAdmin')
            ->willReturn(false);

        $result = $employeeModel->canModifyEmployee(1, 1);

        $this->assertTrue($result);
    }

    public function testCanModifyEmployeeReturnsTrueForOwnAdminAccount(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['isAdmin'])
            ->getMock();

        $employeeModel->method('isAdmin')
            ->willReturn(true);

        $result = $employeeModel->canModifyEmployee(1, 1);

        $this->assertTrue($result);
    }

    public function testCanModifyEmployeeReturnsFalseWhenNonAdminModifiesAdmin(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['isAdmin'])
            ->getMock();

        $employeeModel->method('isAdmin')
            ->willReturnCallback(function($personId) {
                return $personId === 1;
            });

        $result = $employeeModel->canModifyEmployee(1, 2);

        $this->assertFalse($result);
    }

    public function testCanModifyEmployeeReturnsTrueWhenAdminModifiesNonAdmin(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['isAdmin'])
            ->getMock();

        $employeeModel->method('isAdmin')
            ->willReturnCallback(function($personId) {
                return $personId === 1;
            });

        $result = $employeeModel->canModifyEmployee(2, 1);

        $this->assertTrue($result);
    }

    public function testCanModifyEmployeeReturnsTrueWhenNonAdminModifiesNonAdmin(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['isAdmin'])
            ->getMock();

        $employeeModel->method('isAdmin')
            ->willReturn(false);

        $result = $employeeModel->canModifyEmployee(2, 3);

        $this->assertTrue($result);
    }

    public function testCanModifyEmployeeReturnsFalseForNonAdminEditingAdmin(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['isAdmin'])
            ->getMock();

        $employeeModel->method('isAdmin')
            ->willReturnCallback(function($personId) {
                return $personId === 1;
            });

        $result = $employeeModel->canModifyEmployee(1, 2);

        $this->assertFalse($result);
    }

    public function testHasGrantReturnsTrueForActualGrant(): void
    {
        $employeeModel = model(Employee::class);

        $result = $employeeModel->has_grant('employees', 1);

        $this->assertTrue($result);
    }

    public function testHasGrantReturnsFalseForMissingGrant(): void
    {
        $employeeModel = model(Employee::class);

        $result = $employeeModel->has_grant('nonexistent_permission', 1);

        $this->assertFalse($result);
    }

    protected function createEmployeeWithGrants(array $grantsData): int
    {
        $uniqueSuffix = uniqid();

        $personData = [
            'first_name'   => 'Grant',
            'last_name'    => 'Tester',
            'email'        => "granttester{$uniqueSuffix}@test.com",
            'phone_number' => '555-5678'
        ];

        $employeeData = [
            'username'      => "granttester{$uniqueSuffix}",
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        $employeeModel = model(Employee::class);
        $result = $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

        $this->assertTrue($result);
        $this->assertArrayHasKey('person_id', $personData);

        return $personData['person_id'];
    }

    public function testExistingEmployeeKeepsOriginalGrantsWhenGrantChangeDisallowed(): void
    {
        $employeeId = $this->createEmployeeWithGrants([
            ['permission_id' => 'customers', 'menu_group' => 'home']
        ]);

        $originalDisallowGrantChange = getenv('DISALLOW_GRANT_CHANGE');
        putenv('DISALLOW_GRANT_CHANGE=true');

        try {
            $employeeModel = model(Employee::class);
            $personData = ['first_name' => 'Grant', 'last_name' => 'Tester'];
            $employeeData = ['username' => 'granttester', 'language_code' => 'en', 'language' => 'english'];
            $newGrantsData = [['permission_id' => 'sales', 'menu_group' => 'home']];

            $saveEmployeeResult = $employeeModel->save_employee($personData, $employeeData, $newGrantsData, $employeeId);
            $this->assertTrue($saveEmployeeResult);

            $this->assertTrue($employeeModel->has_grant('customers', $employeeId));
            $this->assertFalse($employeeModel->has_grant('sales', $employeeId));
        } finally {
            $originalDisallowGrantChange === false
                ? putenv('DISALLOW_GRANT_CHANGE')
                : putenv("DISALLOW_GRANT_CHANGE={$originalDisallowGrantChange}");
        }
    }

    public function testNewEmployeeCreationWithGrantsRejectedWhenGrantChangeDisallowed(): void
    {
        $originalDisallowGrantChange = getenv('DISALLOW_GRANT_CHANGE');
        putenv('DISALLOW_GRANT_CHANGE=true');

        try {
            $result = $this->createEmployeeWithGrantsExpectingFailure([
                ['permission_id' => 'customers', 'menu_group' => 'home']
            ]);

            $this->assertFalse($result);
        } finally {
            $originalDisallowGrantChange === false
                ? putenv('DISALLOW_GRANT_CHANGE')
                : putenv("DISALLOW_GRANT_CHANGE={$originalDisallowGrantChange}");
        }
    }

    protected function createEmployeeWithGrantsExpectingFailure(array $grantsData): bool
    {
        $uniqueSuffix = uniqid();

        $personData = [
            'first_name'   => 'Rejected',
            'last_name'    => 'Tester',
            'email'        => "rejectedtester{$uniqueSuffix}@test.com",
            'phone_number' => '555-9999'
        ];

        $employeeData = [
            'username'      => "rejectedtester{$uniqueSuffix}",
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english'
        ];

        $employeeModel = model(Employee::class);

        return $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);
    }

    public function testExistingEmployeeGrantsUpdateWhenGrantChangeAllowed(): void
    {
        $employeeId = $this->createEmployeeWithGrants([
            ['permission_id' => 'customers', 'menu_group' => 'home']
        ]);

        $employeeModel = model(Employee::class);
        $personData = ['first_name' => 'Grant', 'last_name' => 'Tester'];
        $employeeData = ['username' => 'granttester', 'language_code' => 'en', 'language' => 'english'];
        $newGrantsData = [['permission_id' => 'sales', 'menu_group' => 'home']];

        $employeeModel->save_employee($personData, $employeeData, $newGrantsData, $employeeId);

        $this->assertFalse($employeeModel->has_grant('customers', $employeeId));
        $this->assertTrue($employeeModel->has_grant('sales', $employeeId));
    }

    public function testNewEmployeeCreationWithGrantsSucceedsWhenGrantChangeAllowed(): void
    {
        $result = $this->createEmployeeWithGrantsExpectingFailure([
            ['permission_id' => 'customers', 'menu_group' => 'home']
        ]);

        $this->assertTrue((bool) $result);
    }
}
