<?php

namespace Tests\Jobs;

use App\Jobs\JobThrottleGate;
use App\Models\JobThrottle;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use Tests\Support\DatabaseBootstrapTrait;

class JobThrottleGateTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $refresh = false;
    protected $namespace = null;

    protected JobThrottle $jobThrottle;
    protected JobThrottleGate $gate;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

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

    public function testGetSleepSeconds_ReturnsOneWhenNoThrottlesConfigured(): void
    {
        $this->assertSame(1, $this->gate->getSleepSeconds());
    }

    public function testGetSleepSeconds_ReturnsPositiveSecondsAfterThrottleExhausted(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 1, 'period' => 'minute'], 1);

        $this->gate->allows();
        $this->gate->allows();

        $this->assertGreaterThanOrEqual(1, $this->gate->getSleepSeconds());
    }

    // Queue-scoped throttle tests

    public function testAllows_GlobalThrottleAppliesRegardlessOfQueueName(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 1, 'period' => 'minute', 'queue' => null], 1);

        $this->assertTrue($this->gate->allows('api'));
        $this->assertFalse($this->gate->allows('api'));
    }

    public function testAllows_QueueSpecificThrottleOnlyAppliesToMatchingQueue(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 1, 'period' => 'minute', 'queue' => 'api'], 1);

        // 'imports' queue is not subject to the 'api' throttle — must allow freely.
        $this->assertTrue($this->gate->allows('imports'));
        $this->assertTrue($this->gate->allows('imports'));

        // 'api' queue is subject to the throttle — second call must fail.
        $this->assertTrue($this->gate->allows('api'));
        $this->assertFalse($this->gate->allows('api'));
    }

    public function testAllows_NullQueueNameOnlyMatchesGlobalThrottles(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 1, 'period' => 'minute', 'queue' => 'api'], 1);

        // No queue name passed — the api-scoped throttle must NOT apply.
        $this->assertTrue($this->gate->allows(null));
        $this->assertTrue($this->gate->allows(null));
    }

    public function testAllows_GlobalAndQueueSpecificThrottlesBothApply(): void
    {
        // Global cap: 2/minute. Queue-specific cap for 'api': 1/minute.
        $this->jobThrottle->saveValue(['max_count' => 2, 'period' => 'minute', 'queue' => null], 1);
        $this->jobThrottle->saveValue(['max_count' => 1, 'period' => 'minute', 'queue' => 'api'], 2);

        // First 'api' call: both global and api-scoped allow.
        $this->assertTrue($this->gate->allows('api'));
        // Second 'api' call: global still has capacity, but api-scoped is exhausted.
        $this->assertFalse($this->gate->allows('api'));
    }

    public function testGetSleepSeconds_QueueScopedThrottleOnlyCountsMatchingRows(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 1, 'period' => 'minute', 'queue' => 'api'], 1);

        $this->gate->allows('api');
        $this->gate->allows('api');

        // getSleepSeconds for 'imports' must return the minimum (1) because
        // the 'api' throttle does not apply to it.
        $this->assertSame(1, $this->gate->getSleepSeconds('imports'));

        // getSleepSeconds for 'api' must reflect the exhausted bucket.
        $this->assertGreaterThanOrEqual(1, $this->gate->getSleepSeconds('api'));
    }
}
