<?php

namespace Tests\Models\Reports;

use App\Models\Reports\Detailed_sales;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class Detailed_salesTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    public function testGetDataBySaleId_ReturnsEmptyArrayWhenNotFound(): void
    {
        $detailedSales = model(Detailed_sales::class);
        $detailedSales->create([
            'start_date'  => '2020-01-01',
            'end_date'    => '2020-01-02',
            'sale_type'   => 'complete',
            'location_id' => 'all',
        ]);

        $this->assertSame([], $detailedSales->getDataBySaleId(999999));
    }
}
