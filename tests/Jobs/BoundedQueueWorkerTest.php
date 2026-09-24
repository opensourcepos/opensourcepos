<?php

namespace Tests\Jobs;

use App\Jobs\BoundedQueueWorker;
use App\Models\JobThrottle;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Jobs as JobsConfig;
use Config\Queue as QueueConfig;
use Config\Services;
use Tests\Support\Jobs\FailingTestJob;
use Tests\Support\Jobs\SucceedingTestJob;

class BoundedQueueWorkerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = false;
    protected $namespace = null;

    private static bool $doneBootstrap = false;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();

        Services::reset();

        $queueConfig = new QueueConfig();
        $queueConfig->jobHandlers = [
            'success' => SucceedingTestJob::class,
            'failure' => FailingTestJob::class,
        ];
        Factories::injectMock('config', 'Queue', $queueConfig);

        $this->db->table('queue_jobs')->truncate();
        $this->db->table('queue_jobs_failed')->truncate();
        $this->db->table('job_throttles')->truncate();
    }

    protected function tearDown(): void
    {
        Services::throttler()->remove('job_queue_core_1');

        Factories::reset('config');
        Services::reset();

        parent::tearDown();
    }

    public function testDrainsAvailableJobs(): void
    {
        service('queue')->push('default', 'success', []);
        service('queue')->push('default', 'success', []);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(2, $worker->getProcessedCount());
        $this->assertSame(0, $worker->getFailedCount());
        $this->assertSame(0, $this->db->table('queue_jobs')->countAllResults());
    }

    public function testStopsAtDeadlineWithoutDrainingEverything(): void
    {
        service('queue')->push('default', 'success', []);

        // Deadline already in the past: the worker must not process anything.
        $worker = new BoundedQueueWorker(['default'], microtime(true) - 1);
        $worker->run();

        $this->assertSame(0, $worker->getProcessedCount());
        $this->assertSame(1, $this->db->table('queue_jobs')->countAllResults());
    }

    public function testStopsAtMaxJobs(): void
    {
        service('queue')->push('default', 'success', []);
        service('queue')->push('default', 'success', []);
        service('queue')->push('default', 'success', []);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5, maxJobs: 1);
        $worker->run();

        $this->assertSame(1, $worker->getProcessedCount());
        $this->assertSame(2, $this->db->table('queue_jobs')->countAllResults());
    }

    public function testRetriesFailedJobUpToItsTriesLimit(): void
    {
        service('queue')->push('default', 'failure', []);

        // FailingTestJob::$tries = 2, so the first failure is a retry, not a final failure.
        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(0, $worker->getFailedCount());
        $this->seeInDatabase('queue_jobs', ['queue' => 'default', 'attempts' => 1]);
        $this->assertSame(0, $this->db->table('queue_jobs_failed')->countAllResults());
    }

    public function testMovesJobToFailedTableAfterExhaustingTries(): void
    {
        service('queue')->push('default', 'failure', []);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run(); // attempt 1: retried

        // Force the retried job to be immediately available again.
        $this->db->table('queue_jobs')->update(['available_at' => 0]);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run(); // attempt 2: exhausts tries, moves to failed table

        $this->assertSame(1, $worker->getFailedCount());
        $this->assertSame(0, $this->db->table('queue_jobs')->countAllResults());
        $this->seeInDatabase('queue_jobs_failed', ['queue' => 'default']);
    }

    public function testStopsWhenThrottleCapacityExhausted(): void
    {
        model(JobThrottle::class)->saveValue(['max_count' => 1, 'period' => 'minute'], 1);

        service('queue')->push('default', 'success', []);
        service('queue')->push('default', 'success', []);
        service('queue')->push('default', 'success', []);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(1, $worker->getProcessedCount());
        $this->assertSame(2, $this->db->table('queue_jobs')->countAllResults());
    }

    public function testThrottleAppliesAcrossQueues(): void
    {
        // A single core throttle row is a global cap, not per-queue: it
        // must stop the worker even though the jobs come from different
        // queues.
        model(JobThrottle::class)->saveValue(['max_count' => 1, 'period' => 'minute'], 1);

        service('queue')->push('default', 'success', []);
        service('queue')->push('imports', 'success', []);

        $worker = new BoundedQueueWorker(['default', 'imports'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(1, $worker->getProcessedCount());
        $this->assertSame(1, $this->db->table('queue_jobs')->countAllResults());
    }

    public function testAutoPurgeRemovesOldFailedJobs(): void
    {
        $jobsConfig = config(JobsConfig::class);
        $jobsConfig->autoPurge = true;
        $jobsConfig->failedRetentionDays = 30;

        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['job' => 'failure', 'data' => []]),
            'priority'   => 'default',
            'exception'  => 'old failure',
            'failed_at'  => strtotime('-40 days'),
        ]);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(0, $this->db->table('queue_jobs_failed')->countAllResults());
    }
}
