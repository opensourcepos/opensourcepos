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
}