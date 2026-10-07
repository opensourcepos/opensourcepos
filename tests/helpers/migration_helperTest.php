<?php

namespace Tests\helpers;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\FailedQueryConnectionTrait;

/**
 * Regression tests for Issue #3634: the migration metadata helpers must fail
 * loudly (throw DatabaseException) when their metadata query returns false,
 * rather than mistaking a failed query for "not found".
 */
class migration_helperTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FailedQueryConnectionTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

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
