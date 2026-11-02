<?php

namespace Tests\Support;

use CodeIgniter\Config\Factories;
use Config\OSPOS;

/**
 * Shared helper for injecting a mocked OSPOS config into a test.
 *
 * Replaces the old per-file `$ospos = new OSPOS(); $ospos->settings = [...];
 * Factories::injectMock(...)` block that was duplicated with slightly
 * different settings arrays across many test files. Each test supplies the
 * exact settings it depends on - OSPOS::getDefaultSettings() is private and
 * only has a handful of keys, so it is not a usable base to merge onto.
 *
 * Usage:
 *
 *   use Tests\Support\OsposConfigFixtureTrait;
 *
 *   class FooTest extends CIUnitTestCase
 *   {
 *       use OsposConfigFixtureTrait;
 *
 *       protected function setUp(): void
 *       {
 *           parent::setUp();
 *
 *           $this->injectOsposSettings([
 *               'invoice_enable' => false,
 *               'number_locale'  => 'en_US',
 *           ]);
 *       }
 *   }
 */
trait OsposConfigFixtureTrait
{
    /**
     * @param array<string, mixed> $settings
     */
    protected function injectOsposSettings(array $settings): OSPOS
    {
        $ospos           = new OSPOS();
        $ospos->settings = $settings;

        Factories::injectMock('config', OSPOS::class, $ospos);

        return $ospos;
    }
}
