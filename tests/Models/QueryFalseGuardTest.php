<?php

namespace Tests\Models;

use App\Models\Sale;
use App\Models\Tax;
use CodeIgniter\BaseModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Regression tests for Issue #3634: as of CI 4.1.2, Connection::query()
 * returns false when a query fails (previously it returned an empty result
 * object). Model methods that consume the result must guard against false
 * instead of calling getResultArray()/getRowArray() on it, which throws
 * "Call to member function ... on false".
 *
 * These tests force query() to return false and assert the methods degrade
 * gracefully to an empty result rather than crashing.
 */
class QueryFalseGuardTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    /**
     * Build a model instance whose db connection always returns false from
     * query(), simulating a failed query.
     *
     * Mocks the concrete driver class (not the abstract BaseConnection, which
     * has many abstract methods that onlyMethods() does not stub).
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function modelWithFailingQuery(string $class)
    {
        $driverClass = get_class(\Config\Database::connect($this->DBGroup));

        $db = $this->getMockBuilder($driverClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['query'])
            ->getMock();
        $db->method('query')->willReturn(false);

        $model = model($class);

        $prop = new \ReflectionProperty(BaseModel::class, 'db');
        $prop->setAccessible(true);
        $prop->setValue($model, $db);

        return $model;
    }

    public function testGetTaxesReturnsEmptyArrayWhenQueryFails(): void
    {
        $model = $this->modelWithFailingQuery(Tax::class);

        $this->assertSame([], $model->get_taxes(1, 1));
    }

    public function testGetAllSuspendedReturnsEmptyArrayWhenQueryFails(): void
    {
        $model = $this->modelWithFailingQuery(Sale::class);

        // Exercises both the NEW_ENTRY branch and the customer-id branch.
        $this->assertSame([], $model->get_all_suspended());
        $this->assertSame([], $model->get_all_suspended(NEW_ENTRY));
        $this->assertSame([], $model->get_all_suspended(123));
    }
}
