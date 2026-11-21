<?php

namespace Tests\Models\Reports;

use App\Models\Reports\Detailed_receivings;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class Detailed_receivingsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    public function testGetDataByReceivingId_ReturnsEmptyArrayWhenNotFound(): void
    {
        $detailedReceivings = model(Detailed_receivings::class);
        $detailedReceivings->create([
            'start_date'     => '2020-01-01',
            'end_date'       => '2020-01-02',
            'receiving_type' => 'receiving',
            'location_id'    => 'all',
        ]);

        $this->assertSame([], $detailedReceivings->getDataByReceivingId('999999'));
    }
}
