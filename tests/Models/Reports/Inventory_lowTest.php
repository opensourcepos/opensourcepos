<?php

namespace Tests\Models\Reports;

use App\Models\Reports\Inventory_low;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\FailedQueryConnectionTrait;

/**
 * Regression test for Issue #3634: Inventory_low::getData() must degrade to an
 * empty result (not throw "Call to member function ... on false") when the
 * query fails.
 */
class Inventory_lowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FailedQueryConnectionTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    /**
     * Inventory_low::getData() must return an empty array, not throw, when the query fails.
     */
    public function testInventoryLowGetData_ReturnsEmptyArrayWhenQueryFails(): void
    {
        $report = $this->modelWithFailingQuery(Inventory_low::class);

        $this->assertSame([], $report->getData([]));
    }
}
