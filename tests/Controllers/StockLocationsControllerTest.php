<?php

namespace Tests\Controllers;

use App\Models\Stock_location;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\AdminAuthTrait;

class StockLocationsControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AdminAuthTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private static $doneBootstrap = false;

    protected Stock_location $stock_location;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();

        $this->stock_location = model(Stock_location::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function createLocation(string $name): int
    {
        $locationData = ['location_name' => $name];
        $this->stock_location->saveValue($locationData, NEW_ENTRY);

        return (int) $locationData['location_id'];
    }

    public function testPostSaveLocations_RejectsNonArrayPayload(): void
    {
        $this->loginAsAdminWithGrants(['config']);

        $response = $this->post('/config/saveLocations', [
            'stock_location' => 'not-an-array',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostSaveLocations_AddsNewLocation(): void
    {
        $this->loginAsAdminWithGrants(['config']);

        $response = $this->post('/config/saveLocations', [
            'stock_location'     => [],
            'stock_location_new' => ['Warehouse'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $row = $this->db->table('stock_locations')
            ->where('location_name', 'Warehouse')
            ->where('deleted', 0)
            ->get()
            ->getRow();

        $this->assertNotNull($row);

        $permissionCount = $this->db->table('permissions')
            ->where('location_id', $row->location_id)
            ->countAllResults();
        $this->assertSame(3, $permissionCount); // items, sales, receivings
    }

    public function testPostSaveLocations_RenamesExistingLocation(): void
    {
        $locationId = $this->createLocation('Original Name');
        $this->loginAsAdminWithGrants(['config']);

        $response = $this->post('/config/saveLocations', [
            'stock_location' => [(string) $locationId => 'Renamed'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->assertSame('Renamed', $this->stock_location->getLocationName($locationId));
    }

    public function testPostSaveLocations_RemovesLocationNotInSubmittedSet(): void
    {
        $keptId    = $this->createLocation('Keep Me');
        $removedId = $this->createLocation('Remove Me');
        $this->loginAsAdminWithGrants(['config']);

        $response = $this->post('/config/saveLocations', [
            'stock_location' => [(string) $keptId => 'Keep Me'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->assertTrue($this->stock_location->exists($keptId));

        $removedRow = $this->db->table('stock_locations')
            ->where('location_id', $removedId)
            ->get()
            ->getRow();
        $this->assertSame(1, (int) $removedRow->deleted);
    }

    public function testPostSaveLocations_ReordersWithNewLocationToken(): void
    {
        $existingId = $this->createLocation('Existing');
        $this->loginAsAdminWithGrants(['config']);

        $response = $this->post('/config/saveLocations', [
            'stock_location'       => [
                (string) $existingId => 'Existing',
            ],
            'stock_location_new'   => ['Brand New'],
            'stock_location_order' => 'new-0,' . $existingId,
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $newRow = $this->db->table('stock_locations')
            ->where('location_name', 'Brand New')
            ->get()
            ->getRow();

        $this->assertNotNull($newRow);
        $this->assertLessThan(
            (int) $this->db->table('stock_locations')->where('location_id', $existingId)->get()->getRow()->sort_order,
            (int) $newRow->sort_order
        );
    }

    public function testPostSaveLocations_SetsValidDefaultLocation(): void
    {
        $locationId = $this->createLocation('Default Candidate');
        $this->loginAsAdminWithGrants(['config']);

        $response = $this->post('/config/saveLocations', [
            'stock_location'         => [(string) $locationId => 'Default Candidate'],
            'stock_location_default' => (string) $locationId,
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $row = $this->db->table('stock_locations')
            ->where('location_id', $locationId)
            ->get()
            ->getRow();
        $this->assertSame(1, (int) $row->is_default);
    }

    public function testPostSaveLocations_IgnoresDefaultForDeletedLocation(): void
    {
        $keptId    = $this->createLocation('Keep Me');
        $removedId = $this->createLocation('Remove Me');
        $this->loginAsAdminWithGrants(['config']);

        $response = $this->post('/config/saveLocations', [
            'stock_location'         => [(string) $keptId => 'Keep Me'],
            'stock_location_default' => (string) $removedId,
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $removedRow = $this->db->table('stock_locations')
            ->where('location_id', $removedId)
            ->get()
            ->getRow();
        $this->assertSame(0, (int) $removedRow->is_default);
    }
}
