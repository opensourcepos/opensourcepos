<?php

namespace Tests\Models;

use App\Models\Inventory;
use App\Models\Stock_location;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;
use Tests\Support\ItemFixtureTrait;

class InventoryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use EmployeeFixtureTrait;
    use ItemFixtureTrait;

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

    public function testResetQuantity_ResetsAllLocationsAndNegativeBalances(): void
    {
        $employeeId = $this->createEmployee();
        Services::session()->set('person_id', $employeeId);

        $stockLocation = model(Stock_location::class);
        $suffix = bin2hex(random_bytes(8));
        $secondLocation = ['location_name' => "InvLoc2_{$suffix}", 'deleted' => 0];
        $this->assertTrue($stockLocation->saveValue($secondLocation, NEW_ENTRY));
        $secondLocId = (int) $secondLocation['location_id'];

        $itemId = $this->createTestItem(HAS_STOCK);
        $inventoryModel = model(Inventory::class);

        $inventoryModel->insert([
            'trans_items'     => $itemId,
            'trans_user'      => $employeeId,
            'trans_date'      => date('Y-m-d H:i:s'),
            'trans_comment'   => 'Initial positive stock',
            'trans_inventory' => 10,
            'trans_location'  => 1,
        ]);

        $inventoryModel->insert([
            'trans_items'     => $itemId,
            'trans_user'      => $employeeId,
            'trans_date'      => date('Y-m-d H:i:s'),
            'trans_comment'   => 'Initial negative stock',
            'trans_inventory' => -4,
            'trans_location'  => $secondLocId,
        ]);

        $result = $inventoryModel->reset_quantity($itemId);

        $this->assertTrue($result);

        $sums = $inventoryModel->get_inventory_sum($itemId);
        $this->assertCount(2, $sums);
        foreach ($sums as $row) {
            $this->assertEqualsWithDelta(0.0, (float) $row['sum'], 0.001);
        }
    }
}
