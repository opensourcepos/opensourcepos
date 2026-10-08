<?php

namespace Tests\helpers;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\FailedQueryConnectionTrait;

class migration_helperTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FailedQueryConnectionTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    public function testIndexExists_ThrowsWhenQueryFails(): void
    {
        $this->requireMigrationHelper();
        $this->expectException(DatabaseException::class);

        indexExists('some_table', 'some_index', $this->failingConnection());
    }

    public function testPrimaryKeyExists_ThrowsWhenQueryFails(): void
    {
        $this->requireMigrationHelper();
        $this->expectException(DatabaseException::class);

        primaryKeyExists('some_table', $this->failingConnection());
    }

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
