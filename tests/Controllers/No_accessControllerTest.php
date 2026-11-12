<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;

class No_accessControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use FeatureTestTrait;
    use EmployeeFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();
    }

    public function testGetIndex_ShowsOfficeLinkForOfficeOnlyEmployee(): void
    {
        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'home', 'menu_group' => 'office'],
                ['permission_id' => 'office', 'menu_group' => 'office'],
                ['permission_id' => 'config', 'menu_group' => 'office'],
            ]
        );

        $this->withSession(['person_id' => $personId, 'menu_group' => 'home']);

        $response = $this->get('/no_access/sales/sales');

        $response->assertStatus(200);
        $this->assertStringContainsString('href="' . base_url('office') . '"', $response->getBody());
    }

    public function testGetIndex_OmitsOfficeLinkWithoutOfficeGrant(): void
    {
        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'home', 'menu_group' => 'office'],
                ['permission_id' => 'config', 'menu_group' => 'office'],
            ]
        );

        $this->withSession(['person_id' => $personId, 'menu_group' => 'home']);

        $response = $this->get('/no_access/sales/sales');

        $response->assertStatus(200);
        $this->assertStringNotContainsString('href="' . base_url('office') . '"', $response->getBody());
    }
}