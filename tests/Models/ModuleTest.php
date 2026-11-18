<?php

namespace Tests\Models;

use App\Models\Module;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;

class ModuleTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use EmployeeFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace  = null;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();
    }

    public function testGetNavigationModules_AddsOfficeForOfficeOnlyEmployee(): void
    {
        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'home', 'menu_group' => 'office'],
                ['permission_id' => 'office', 'menu_group' => 'office'],
                ['permission_id' => 'config', 'menu_group' => 'office'],
            ]
        );

        $modules = model(Module::class)->getNavigationModules($personId, 'home', true);

        $this->assertSame(['office'], array_column($modules, 'module_id'));
    }

    public function testGetNavigationModules_OmitsOfficeWithoutOfficeGrant(): void
    {
        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'home', 'menu_group' => 'office'],
                ['permission_id' => 'config', 'menu_group' => 'office'],
            ]
        );

        $modules = model(Module::class)->getNavigationModules($personId, 'home', false);

        $this->assertSame([], $modules);
    }

    public function testGetNavigationModules_OmitsOfficeWhenOfficeGroupHidden(): void
    {
        $module = model(Module::class);
        $originalSort = $module->get_show_office_group();

        try {
            $module->set_show_office_group(false);

            $personId = $this->createEmployee(
                grants: [
                    ['permission_id' => 'home', 'menu_group' => 'office'],
                    ['permission_id' => 'office', 'menu_group' => 'office'],
                    ['permission_id' => 'config', 'menu_group' => 'office'],
                ]
            );

            $modules = $module->getNavigationModules($personId, 'home', true);

            $this->assertSame([], $modules);
        } finally {
            $module->set_show_office_group($originalSort > 0);
        }
    }

    public function testGetNavigationModules_ReturnsHomeModulesWithoutOfficeFallback(): void
    {
        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'home', 'menu_group' => 'home'],
                ['permission_id' => 'sales', 'menu_group' => 'home'],
            ]
        );

        $modules = model(Module::class)->getNavigationModules($personId, 'home', true);

        $moduleIds = array_column($modules, 'module_id');

        $this->assertContains('sales', $moduleIds);
        $this->assertNotContains('office', $moduleIds);
    }

    public function testGetNavigationModules_ReturnsEmptyArrayForEmptyOfficeMenu(): void
    {
        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'home', 'menu_group' => 'home'],
            ]
        );

        $modules = model(Module::class)->getNavigationModules($personId, 'office', true);

        $this->assertSame([], $modules);
    }
}
