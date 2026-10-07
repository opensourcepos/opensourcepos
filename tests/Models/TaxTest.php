<?php

namespace Tests\Models;

use App\Models\Tax;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\FailedQueryConnectionTrait;

/**
 * Regression test for Issue #3634: Tax::get_taxes() must degrade to an empty
 * result (not throw "Call to member function ... on false") when the query fails.
 */
class TaxTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FailedQueryConnectionTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    /**
     * Tax::get_taxes() must return an empty array, not throw, when the query fails.
     */
    public function testGetTaxes_ReturnsEmptyArrayWhenQueryFails(): void
    {
        $model = $this->modelWithFailingQuery(Tax::class);

        $this->assertSame([], $model->get_taxes(1, 1));
    }
}
