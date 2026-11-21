<?php

namespace Tests\Models;

use App\Models\Rewards;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;
use Tests\Support\SaleFixtureTrait;

class RewardsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use EmployeeFixtureTrait;
    use SaleFixtureTrait;

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

    public function testSaveValue_InsertsAndUpdatesRewardsRow(): void
    {
        $employeeId = $this->createEmployee();
        $saleId     = $this->createSale($employeeId);

        $rewardsModel = model(Rewards::class);
        $rewardsData  = [
            'sale_id' => $saleId,
            'earned'  => 10.0,
            'used'    => 0.0,
        ];

        $this->assertTrue($rewardsModel->save_value($rewardsData));
        $this->assertArrayHasKey('id', $rewardsData);
        $rewardsId = (int) $rewardsData['id'];
        $this->assertTrue($rewardsModel->exists($rewardsId));

        $rewardsData['used'] = 5.0;
        $this->assertTrue($rewardsModel->save_value($rewardsData, $rewardsId));
    }
}
