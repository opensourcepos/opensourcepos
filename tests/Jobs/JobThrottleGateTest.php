<?php

namespace Tests\Jobs;

use App\Jobs\JobThrottleGate;
use App\Models\JobThrottle;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;

class JobThrottleGateTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = false;
    protected $namespace = null;

    private static bool $doneBootstrap = false;

    protected JobThrottle $jobThrottle;
    protected JobThrottleGate $gate;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();

        Services::reset();

        $this->jobThrottle = model(JobThrottle::class);
        $this->db->table('job_throttles')->truncate();

        $this->gate = new JobThrottleGate($this->jobThrottle);
    }

    protected function tearDown(): void
    {
        $throttler = Services::throttler();

        foreach ([1, 2] as $throttleId) {
            $throttler->remove('job_queue_core_' . $throttleId);
        }

        Services::reset();

        parent::tearDown();
    }

    public function testAllowsWhenNoThrottlesConfigured(): void
    {
        $this->assertTrue($this->gate->allows());
        $this->assertTrue($this->gate->allows());
    }

    public function testAllowsWhenMaxCountIsZero(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 0, 'period' => 'minute'], 1);

        $this->assertTrue($this->gate->allows());
        $this->assertTrue($this->gate->allows());
    }

    public function testDeniesOnceCapacityExhausted(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 2, 'period' => 'minute'], 1);

        $this->assertTrue($this->gate->allows());
        $this->assertTrue($this->gate->allows());
        $this->assertFalse($this->gate->allows());
    }

    public function testDeletedThrottleIsIgnored(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 1, 'period' => 'minute'], 1);
        $this->jobThrottle->delete(1);

        $this->assertTrue($this->gate->allows());
        $this->assertTrue($this->gate->allows());
        $this->assertTrue($this->gate->allows());
    }

    public function testMultipleThrottlesAreAndedTogether(): void
    {
        // A generous per-minute cap alongside a tight per-hour cap: the
        // tighter row must trip the gate even though the looser one still
        // has capacity left.
        $this->jobThrottle->saveValue(['max_count' => 100, 'period' => 'minute'], 1);
        $this->jobThrottle->saveValue(['max_count' => 1, 'period' => 'hour'], 2);

        $this->assertTrue($this->gate->allows());
        $this->assertFalse($this->gate->allows());
    }

    public function testSecondsPeriodIsHonored(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 1, 'period' => 'second'], 1);

        $this->assertTrue($this->gate->allows());
        $this->assertFalse($this->gate->allows());
    }
}
