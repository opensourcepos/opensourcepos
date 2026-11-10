<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Services;
use Config\OSPOS;
use App\Models\Stock_location;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;

class ReceivingsControllerTest extends CIUnitTestCase
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

    private Stock_location $stock_location;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();
        parent::setUp();
        config(OSPOS::class)->update_settings();
        $this->stock_location = model(Stock_location::class);
    }

    protected function loginAsReceivingsEmployee(int $personId): void
    {
        $this->withSession([
            'person_id'  => $personId,
            'menu_group' => 'home',
        ]);
    }

    public function testPostChangeMode_RejectsUnauthorizedDestination(): void
    {
        $suffixA = bin2hex(random_bytes(8));
        $suffixB = bin2hex(random_bytes(8));

        $locationA = ['location_name' => "RecvAuth_{$suffixA}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($locationA, NEW_ENTRY));

        $locationB = ['location_name' => "RecvUnauth_{$suffixB}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($locationB, NEW_ENTRY));

        $locAId = (int) $locationA['location_id'];
        $locBId = (int) $locationB['location_id'];

        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'receivings', 'menu_group' => 'home'],
                ['permission_id' => 'receivings_' . $locationA['location_name'], 'menu_group' => 'home'],
            ]
        );
        $this->loginAsReceivingsEmployee($personId);

        $response = $this->post('/receivings/changeMode', [
            'stock_source'      => $locAId,
            'stock_destination' => $locBId,
            'mode'              => 'requisition',
        ]);

        $response->assertStatus(200);
        $this->assertNotEquals((string) $locBId, Services::session()->get('recv_stock_destination'));
    }

    public function testPostChangeMode_AcceptsAuthorizedDestination(): void
    {
        $suffix = bin2hex(random_bytes(8));

        $locationA = ['location_name' => "RecvAllowed_{$suffix}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($locationA, NEW_ENTRY));

        $locAId = (int) $locationA['location_id'];

        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'receivings', 'menu_group' => 'home'],
                ['permission_id' => 'receivings_' . $locationA['location_name'], 'menu_group' => 'home'],
            ]
        );
        $this->loginAsReceivingsEmployee($personId);

        $response = $this->post('/receivings/changeMode', [
            'stock_source'      => $locAId,
            'stock_destination' => $locAId,
            'mode'              => 'normal',
        ]);

        $response->assertStatus(200);
        $this->assertEquals((string) $locAId, Services::session()->get('recv_stock_destination'));
    }
}
