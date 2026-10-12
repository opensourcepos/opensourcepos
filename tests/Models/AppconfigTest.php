<?php

namespace Tests\Models;

use App\Models\Appconfig;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Config\OSPOS;

class AppconfigTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seed = '';
    protected $seedOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    public static function setUpBeforeClass(): void
    {
        $seeder = Database::seeder('tests');
        $seeder->call('TestDatabaseBootstrapSeeder');
    }

    private function appconfig(): Appconfig
    {
        return model(Appconfig::class);
    }

    public function testBatchSave_PersistsAllKeysAndRefreshesCachedSettings(): void
    {
        $appconfig = $this->appconfig();
        $prefix    = uniqid('ospos_batch_');
        $keyA      = $prefix . 'alpha';
        $keyB      = $prefix . 'beta';
        $keyC      = $prefix . 'gamma';

        $success = $appconfig->batch_save([
            $keyA => 'value-a',
            $keyB => 'value-b',
            $keyC => 'value-c',
        ]);

        $this->assertTrue($success);

        // Every key was written to the database.
        $this->assertSame('value-a', $appconfig->get_value($keyA));
        $this->assertSame('value-b', $appconfig->get_value($keyB));
        $this->assertSame('value-c', $appconfig->get_value($keyC));

        // The cached settings were refreshed after the transaction, so a
        // consumer reading config(OSPOS::class)->settings sees the new values.
        $settings = config(OSPOS::class)->settings;
        $this->assertSame('value-a', $settings[$keyA]);
        $this->assertSame('value-b', $settings[$keyB]);
        $this->assertSame('value-c', $settings[$keyC]);
    }

    public function testSave_RefreshesCachedSettings(): void
    {
        $appconfig = $this->appconfig();
        $key       = uniqid('ospos_single_');

        $success = $appconfig->save([$key => 'single-value']);

        $this->assertTrue($success);
        $this->assertSame('single-value', $appconfig->get_value($key));
        $this->assertSame('single-value', config(OSPOS::class)->settings[$key]);
    }
}
