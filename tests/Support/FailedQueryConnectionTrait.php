<?php

namespace Tests\Support;

use CodeIgniter\BaseModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Shared helpers for regression tests that force a database query to fail.
 *
 * Issue #3634: as of CI 4.1.2, Connection::query() returns false when a query
 * fails (previously it returned an empty result object). Consumers must not
 * call getResultArray()/getRowArray() on it. These helpers build a connection
 * whose query() always returns false so the consuming code paths can be
 * exercised deterministically.
 *
 * The host test class must use CodeIgniter\Test\DatabaseTestTrait so that
 * $this->DBGroup is available.
 */
trait FailedQueryConnectionTrait
{
    /**
     * Build a connection whose query() always returns false, simulating a
     * failed query.
     *
     * Mocks the concrete driver class (not the abstract BaseConnection, which
     * has many abstract methods that onlyMethods() does not stub).
     */
    protected function failingConnection(): BaseConnection
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
     * Build a fresh model instance whose db connection always returns false
     * from query(), simulating a failed query.
     *
     * Uses a fresh instance (not the shared model() singleton) so the injected
     * failing connection does not leak into other tests.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    protected function modelWithFailingQuery(string $class)
    {
        $model = new $class();

        $prop = new \ReflectionProperty(BaseModel::class, 'db');
        $prop->setAccessible(true);
        $prop->setValue($model, $this->failingConnection());

        return $model;
    }
}
