<?php

namespace Tests\Support;

use App\Database\Seeds\TestDatabaseBootstrapSeeder;
use CodeIgniter\Database\Config;

/**
 * Standard one-time-per-class database reset for DatabaseTestTrait tests.
 *
 * Call {@see self::resetDatabaseOnce()} as the first line of setUp(), before
 * parent::setUp(). It drops and re-migrates the `tests` schema exactly once
 * per test class (guarded by a per-class static, since PHP trait statics are
 * independent per using-class) and closes the shared connection afterward so
 * the migration run that follows reconnects against the freshly reset schema
 * instead of reusing a connection whose state predates the reset.
 *
 * Usage:
 *
 *   use Tests\Support\DatabaseBootstrapTrait;
 *
 *   class FooTest extends CIUnitTestCase
 *   {
 *       use DatabaseTestTrait;
 *       use DatabaseBootstrapTrait;
 *
 *       protected function setUp(): void
 *       {
 *           $this->resetDatabaseOnce();
 *
 *           parent::setUp();
 *       }
 *   }
 */
trait DatabaseBootstrapTrait
{
    private static bool $doneBootstrap = false;

    protected function resetDatabaseOnce(): void
    {
        if (self::$doneBootstrap === true) {
            return;
        }

        TestDatabaseBootstrapSeeder::reset();
        Config::connect($this->DBGroup)->close();

        self::$doneBootstrap = true;
    }
}
