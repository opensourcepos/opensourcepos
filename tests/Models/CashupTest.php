<?php

namespace Tests\Models;

use App\Models\Cashup;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CashupFixtureTrait;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;

class CashupTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use EmployeeFixtureTrait;
    use CashupFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private Cashup $cashup;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();

        $this->cashup = model(Cashup::class);
    }

    public function testSaveValue_InsertsAndUpdatesCashupAmounts(): void
    {
        $employeeId = $this->createEmployee();
        $cashupId = $this->createCashup($employeeId, $employeeId, [
            'open_amount_cash'     => 100.00,
            'transfer_amount_cash' => 25.00,
        ]);

        $this->assertGreaterThan(0, $cashupId);

        $updateData = [
            'open_amount_cash'     => 150.00,
            'transfer_amount_cash' => 30.00,
            'closed_amount_cash'   => 200.00,
        ];
        $this->assertTrue($this->cashup->save_value($updateData, $cashupId));

        $info = $this->cashup->get_info($cashupId);
        $this->assertEqualsWithDelta(150.00, (float) $info->open_amount_cash, 0.01);
        $this->assertEqualsWithDelta(30.00, (float) $info->transfer_amount_cash, 0.01);
        $this->assertEqualsWithDelta(200.00, (float) $info->closed_amount_cash, 0.01);
    }

    public function testGetMultipleInfo_ReturnsMatchingCashupsForIdArray(): void
    {
        $employeeId = $this->createEmployee();
        $cashupId1 = $this->createCashup($employeeId, $employeeId);
        $cashupId2 = $this->createCashup($employeeId, $employeeId);

        $result = $this->cashup->get_multiple_info([$cashupId1, $cashupId2]);

        $this->assertSame(2, $result->getNumRows());
    }
}
