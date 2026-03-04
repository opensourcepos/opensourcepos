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
    protected $refresh     = true;
    protected $namespace    = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIsAdminReturnsTrueForPersonId1(): void
    {
        $employeeModel = model(Employee::class);
        
        $result = $employeeModel->is_admin(1);
        
        $this->assertTrue($result);
    }

    public function testIsAdminReturnsTrueForEmployeeWithAllPermissions(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['has_grant'])
            ->getMock();
        
        $employeeModel->method('has_grant')
            ->willReturn(true);
        
        $result = $employeeModel->is_admin(2);
        
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
        
        $result = $employeeModel->is_admin(3);
        
        $this->assertFalse($result);
    }

    public function testCanModifyEmployeeReturnsTrueForOwnAccount(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['is_admin'])
            ->getMock();
        
        $employeeModel->method('is_admin')
            ->willReturn(false);
        
        $result = $employeeModel->can_modify_employee(1, 1);
        
        $this->assertTrue($result);
    }

    public function testCanModifyEmployeeReturnsTrueForOwnAdminAccount(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['is_admin'])
            ->getMock();
        
        $employeeModel->method('is_admin')
            ->willReturn(true);
        
        $result = $employeeModel->can_modify_employee(1, 1);
        
        $this->assertTrue($result);
    }

    public function testCanModifyEmployeeReturnsFalseWhenNonAdminModifiesAdmin(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['is_admin'])
            ->getMock();
        
        $employeeModel->method('is_admin')
            ->willReturnCallback(function($personId) {
                return $personId === 1;
            });
        
        $result = $employeeModel->can_modify_employee(1, 2);
        
        $this->assertFalse($result);
    }

    public function testCanModifyEmployeeReturnsTrueWhenAdminModifiesNonAdmin(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['is_admin'])
            ->getMock();
        
        $employeeModel->method('is_admin')
            ->willReturnCallback(function($personId) {
                return $personId === 1;
            });
        
        $result = $employeeModel->can_modify_employee(2, 1);
        
        $this->assertTrue($result);
    }

    public function testCanModifyEmployeeReturnsTrueWhenNonAdminModifiesNonAdmin(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['is_admin'])
            ->getMock();
        
        $employeeModel->method('is_admin')
            ->willReturn(false);
        
        $result = $employeeModel->can_modify_employee(2, 3);
        
        $this->assertTrue($result);
    }

    public function testCanModifyEmployeeReturnsFalseForNonAdminEditingAdmin(): void
    {
        $employeeModel = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['is_admin'])
            ->getMock();
        
        $employeeModel->method('is_admin')
            ->willReturnCallback(function($personId) {
                return $personId === 1;
            });
        
        $result = $employeeModel->can_modify_employee(1, 2);
        
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
}