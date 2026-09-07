<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\Stock_location;
use Config\Database;

class Stock_locationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seed = '';
    protected $seedOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    protected $stockLocation;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $seeder = Database::seeder('tests');
        $seeder->call('TestDatabaseBootstrapSeeder');
    }

    protected function setUp(): void
    {
        parent::setUp();

        session()->set('person_id', 1);

        $this->stockLocation = model(Stock_location::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function nextSortOrder(): int
    {
        return $this->db->table('stock_locations')->where('deleted', 0)->countAllResults();
    }

    private function insertLocation(string $name, bool $isDefault = false, bool $withPermissions = true): int
    {
        $sortOrder = $this->nextSortOrder();

        $builder = $this->db->table('stock_locations');
        $builder->insert([
            'location_name' => $name,
            'deleted'       => 0,
            'is_default'    => $isDefault ? 1 : 0,
            'sort_order'    => $sortOrder,
        ]);
        $locationId = $this->db->insertID();

        if ($withPermissions) {
            foreach (['items', 'sales', 'receivings'] as $module) {
                $permissionId = $module . '_' . str_replace(' ', '_', $name);

                $this->db->table('permissions')->insert([
                    'permission_id' => $permissionId,
                    'module_id'     => $module,
                    'location_id'   => $locationId,
                ]);

                $this->db->table('grants')->insert([
                    'permission_id' => $permissionId,
                    'person_id'     => 1,
                ]);
            }
        }

        return $locationId;
    }

    public function testGetDefaultLocationIdReturnsRowMarkedDefaultRegardlessOfSortOrder(): void
    {
        $this->db->table('stock_locations')->update(['is_default' => 0]);

        $this->insertLocation('Alpha');
        $defaultId = $this->insertLocation('Beta', true);

        $result = $this->stockLocation->getDefaultLocationId('items');

        $this->assertSame($defaultId, $result);
    }

    public function testGetDefaultLocationIdFallsBackToLowestSortOrderWhenNoneMarkedDefault(): void
    {
        // Remove the seeded base location so the inserted rows below are the only
        // candidates and are unambiguously ordered by sort_order.
        $this->db->table('permissions')->emptyTable();
        $this->db->table('grants')->emptyTable();
        $this->db->table('stock_locations')->emptyTable();

        $lowestId = $this->insertLocation('Delta');
        $this->insertLocation('Epsilon');
        $this->insertLocation('Gamma');

        $result = $this->stockLocation->getDefaultLocationId('items');

        $this->assertSame($lowestId, $result);
    }

    public function testGetDefaultLocationIdReturnsNullWhenNoAllowedLocationForModule(): void
    {
        $this->db->table('grants')->emptyTable();
        $this->db->table('permissions')->emptyTable();

        $result = $this->stockLocation->getDefaultLocationId('items');

        $this->assertNull($result);
    }

    public function testSetDefaultLocationSetsTargetAndClearsOthers(): void
    {
        $firstId = $this->insertLocation('Zeta', true);
        $secondId = $this->insertLocation('Eta');

        $result = $this->stockLocation->setDefaultLocation($secondId);

        $this->assertTrue($result);

        $rows = $this->db->table('stock_locations')->orderBy('location_id')->get()->getResultArray();
        $defaults = array_column($rows, 'is_default', 'location_id');

        $this->assertEquals(0, $defaults[$firstId]);
        $this->assertEquals(1, $defaults[$secondId]);
    }

    public function testSaveSortOrderAppliesNewOrderExactly(): void
    {
        // saveSortOrder() only reassigns sort_order for the ids passed in, starting
        // the new range at 0 — any pre-existing row left out of the batch would
        // collide with that range, so the batch here is every row in the table.
        $idA = $this->insertLocation('Theta', false, false);
        $idB = $this->insertLocation('Iota', false, false);
        $idC = $this->insertLocation('Kappa', false, false);

        $allIds = array_column($this->db->table('stock_locations')->get()->getResultArray(), 'location_id');
        $ordered = array_values(array_diff($allIds, [$idA, $idB, $idC]));
        array_unshift($ordered, $idC, $idA, $idB);

        $result = $this->stockLocation->saveSortOrder($ordered);

        $this->assertTrue($result);

        $rows = $this->db->table('stock_locations')
            ->whereIn('location_id', [$idA, $idB, $idC])
            ->get()
            ->getResultArray();
        $sortOrders = array_column($rows, 'sort_order', 'location_id');

        $this->assertLessThan($sortOrders[$idA], $sortOrders[$idC]);
        $this->assertLessThan($sortOrders[$idB], $sortOrders[$idA]);
    }

    public function testSaveSortOrderHandlesCollidingRotationWithoutError(): void
    {
        // A reverse rotation is exactly the case naive sequential UPDATEs would
        // collide on against the unique sort_order index.
        $idA = $this->insertLocation('Lambda', false, false);
        $idB = $this->insertLocation('Mu', false, false);
        $idC = $this->insertLocation('Nu', false, false);

        $allIds = array_column($this->db->table('stock_locations')->get()->getResultArray(), 'location_id');
        $ordered = array_values(array_diff($allIds, [$idA, $idB, $idC]));
        array_unshift($ordered, $idC, $idB, $idA);

        $result = $this->stockLocation->saveSortOrder($ordered);

        $this->assertTrue($result);

        $rows = $this->db->table('stock_locations')
            ->whereIn('location_id', [$idA, $idB, $idC])
            ->get()
            ->getResultArray();
        $sortOrders = array_column($rows, 'sort_order', 'location_id');

        $this->assertLessThan($sortOrders[$idB], $sortOrders[$idC]);
        $this->assertLessThan($sortOrders[$idA], $sortOrders[$idB]);

        $distinctCount = $this->db->table('stock_locations')
            ->distinct()
            ->select('sort_order')
            ->where('deleted', 0)
            ->get()
            ->getNumRows();
        $totalCount = $this->db->table('stock_locations')->where('deleted', 0)->countAllResults();
        $this->assertSame($totalCount, $distinctCount, 'sort_order values must remain unique after rotation');
    }

    public function testDeleteClearsSortOrder(): void
    {
        $idA = $this->insertLocation('Xi', false, false);
        $idB = $this->insertLocation('Omicron', false, false);

        $result = $this->stockLocation->delete($idB);

        $this->assertTrue($result);

        $deletedRow = $this->db->table('stock_locations')->where('location_id', $idB)->get()->getRow();

        $this->assertEquals(1, $deletedRow->deleted);
        $this->assertNull($deletedRow->sort_order);
    }

    public function testDeleteExcludesLocationFromGetAll(): void
    {
        $idA = $this->insertLocation('Pi', false, false);
        $idB = $this->insertLocation('Rho', false, false);

        $this->stockLocation->delete($idB);

        $remainingIds = array_map('intval', array_column($this->stockLocation->getAll()->getResultArray(), 'location_id'));

        $this->assertContains($idA, $remainingIds);
        $this->assertNotContains($idB, $remainingIds);
    }

    public function testDeleteCompactsRemainingSortOrderAndAllowsSubsequentInsert(): void
    {
        $idA = $this->insertLocation('Sigma', false, false);
        $idB = $this->insertLocation('Tau', false, false);
        $idC = $this->insertLocation('Upsilon', false, false);

        $this->stockLocation->delete($idB);

        $remainingSortOrders = array_column(
            $this->db->table('stock_locations')->where('deleted', 0)->orderBy('sort_order', 'ASC')->get()->getResultArray(),
            'sort_order'
        );
        $this->assertSame(range(0, count($remainingSortOrders) - 1), array_map('intval', $remainingSortOrders));

        $locationData = ['location_name' => 'Phi New Location'];

        $this->assertTrue($this->stockLocation->saveValue($locationData, NEW_ENTRY));
        $this->assertNotEmpty($locationData['location_id']);
    }

    public function testSaveValueNewInsertAssignsNextSortOrder(): void
    {
        $countBefore = $this->db->table('stock_locations')->where('deleted', 0)->countAllResults();

        $locationData = ['location_name' => 'Sigma New Location'];

        $this->assertTrue($this->stockLocation->saveValue($locationData, NEW_ENTRY));
        $this->assertNotEmpty($locationData['location_id']);

        $savedRow = $this->db->table('stock_locations')
            ->where('location_id', $locationData['location_id'])
            ->get()
            ->getRow();

        $this->assertSame($countBefore, (int) $savedRow->sort_order);
    }
}
