<?php

namespace Tests\Models;

use App\Models\Sale;
use App\Models\Reports\Inventory_low;
use App\Models\Tax;
use CodeIgniter\BaseModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Regression tests for Issue #3634: as of CI 4.1.2, Connection::query()
 * returns false when a query fails (previously it returned an empty result
 * object). Consumers must not call getResultArray()/getRowArray() on it,
 * which throws "Call to member function ... on false".
 *
 * Model read methods degrade to an empty result on failure (no DDL is
 * involved). Migration metadata helpers, by contrast, fail loudly: a failed
 * metadata query must not be mistaken for "not found", so they throw instead.
 *
 * These tests force query() to return false and assert each behavior.
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
     * Build a connection whose query() always returns false, simulating a
     * failed query.
     *
     * Mocks the concrete driver class (not the abstract BaseConnection, which
     * has many abstract methods that onlyMethods() does not stub).
     */
    private function failingConnection(): BaseConnection
    {
        $driverClass = get_class(\Config\Database::connect($this->DBGroup));

        $db = $this->getMockBuilder($driverClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['query', 'error'])
            ->getMock();
        $db->method('query')->willReturn(false);
        $db->method('error')->willReturn(['code' => 1, 'message' => 'simulated query failure']);

        return $db;
    }

    /**
     * Build a model instance whose db connection always returns false from
     * query(), simulating a failed query.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function modelWithFailingQuery(string $class)
    {
        $model = model($class);

        $prop = new \ReflectionProperty(BaseModel::class, 'db');
        $prop->setAccessible(true);
        $prop->setValue($model, $this->failingConnection());

        return $model;
    }

    /**
     * Tax::get_taxes() must return an empty array, not throw, when the query fails.
     */
    public function testGetTaxes_ReturnsEmptyArrayWhenQueryFails(): void
    {
        $model = $this->modelWithFailingQuery(Tax::class);

        $this->assertSame([], $model->get_taxes(1, 1));
    }

    /**
     * Sale::get_all_suspended() must return an empty array, not throw, when the query fails.
     */
    public function testGetAllSuspended_ReturnsEmptyArrayWhenQueryFails(): void
    {
        $model = $this->modelWithFailingQuery(Sale::class);

        // Exercises both the NEW_ENTRY branch and the customer-id branch.
        $this->assertSame([], $model->get_all_suspended());
        $this->assertSame([], $model->get_all_suspended(NEW_ENTRY));
        $this->assertSame([], $model->get_all_suspended(123));
    }

    /**
     * Inventory_low::getData() must return an empty array, not throw, when the query fails.
     */
    public function testInventoryLowGetData_ReturnsEmptyArrayWhenQueryFails(): void
    {
        $report = new Inventory_low();

        $prop = new \ReflectionProperty(BaseModel::class, 'db');
        $prop->setAccessible(true);
        $prop->setValue($report, $this->failingConnection());

        $this->assertSame([], $report->getData([]));
    }

    /**
     * indexExists() must throw, not silently report "not found", when the metadata query fails.
     */
    public function testIndexExists_ThrowsWhenQueryFails(): void
    {
        $this->requireMigrationHelper();
        $this->expectException(DatabaseException::class);

        indexExists('some_table', 'some_index', $this->failingConnection());
    }

    /**
     * primaryKeyExists() must throw, not silently report "no primary key", when the query fails.
     */
    public function testPrimaryKeyExists_ThrowsWhenQueryFails(): void
    {
        $this->requireMigrationHelper();
        $this->expectException(DatabaseException::class);

        primaryKeyExists('some_table', $this->failingConnection());
    }

    /**
     * dropAllForeignKeyConstraints() must throw, not silently report "none found", when the query fails.
     */
    public function testDropAllForeignKeyConstraints_ThrowsWhenQueryFails(): void
    {
        $this->requireMigrationHelper();
        $this->expectException(DatabaseException::class);

        dropAllForeignKeyConstraints('some_table', 'some_column', $this->failingConnection());
    }

    /**
     * Ensure the migration helper functions are loaded (they are global).
     */
    private function requireMigrationHelper(): void
    {
        if (! function_exists('indexExists')) {
            require APPPATH . 'Helpers/migration_helper.php';
        }
    }
}
