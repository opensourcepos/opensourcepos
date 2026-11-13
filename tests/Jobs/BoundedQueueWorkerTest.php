<?php

namespace Tests\Jobs;

use App\Jobs\BoundedQueueWorker;
use App\Models\JobThrottle;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Jobs as JobsConfig;
use Config\Queue as QueueConfig;
use Config\Services;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\Jobs\BatchableTestJob;
use Tests\Support\Jobs\FailingTestJob;
use Tests\Support\Jobs\SucceedingTestJob;

class BoundedQueueWorkerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $refresh = false;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();

        Services::reset();

        $queueConfig = new QueueConfig();
        $queueConfig->jobHandlers = [
            'success'   => SucceedingTestJob::class,
            'failure'   => FailingTestJob::class,
            'batchable' => BatchableTestJob::class,
        ];
        Factories::injectMock('config', 'Queue', $queueConfig);

        $this->db->table('queue_jobs')->truncate();
        $this->db->table('queue_jobs_failed')->truncate();
        $this->db->table('job_throttles')->truncate();

        BatchableTestJob::$receivedData = [];
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

    public function testRun_UsesPerQueuePrioritiesFromConfig(): void
    {
        $queueConfig = new QueueConfig();
        $queueConfig->jobHandlers = [
            'success' => SucceedingTestJob::class,
        ];
        $queueConfig->queuePriorities = [
            'urgent_queue' => ['critical', 'normal'],
        ];
        $queueConfig->queueDefaultPriority = [
            'urgent_queue' => 'critical',
        ];
        Factories::injectMock('config', 'Queue', $queueConfig);

        service('queue')->setPriority('critical')->push('urgent_queue', 'success', []);

        $worker = new BoundedQueueWorker(['urgent_queue'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(1, $worker->getProcessedCount());
        $this->assertSame(0, $this->db->table('queue_jobs')->countAllResults());
    }

    public function testRun_UsesDefaultPriorityForUnconfiguredQueue(): void
    {
        // Config\Queue pre-configures 'default' with ['high','normal','low'].
        // Unset it so the worker hits the ['default'] fallback branch.
        $queueConfig = new QueueConfig();
        $queueConfig->jobHandlers = ['success' => SucceedingTestJob::class];
        $queueConfig->queuePriorities = [];
        $queueConfig->queueDefaultPriority = [];
        Factories::injectMock('config', 'Queue', $queueConfig);

        // The queue package assigns priority 'default' when none is set via
        // setPriority(), so the fallback ['default'] must pop this job.
        service('queue')->push('default', 'success', []);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(1, $worker->getProcessedCount());
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

    public function testRun_SleepsAndContinuesWhenThrottledWithinDeadline(): void
    {
        model(JobThrottle::class)->saveValue(['max_count' => 1, 'period' => 'second'], 1);

        service('queue')->push('default', 'success', []);
        service('queue')->push('default', 'success', []);

        // 5s deadline: worker should process job 1, sleep 1s for refill, then process job 2.
        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(2, $worker->getProcessedCount());
        $this->assertSame(0, $this->db->table('queue_jobs')->countAllResults());
    }

    public function testRun_ExitsRatherThanSleepingIfDeadlineWouldExpire(): void
    {
        model(JobThrottle::class)->saveValue(['max_count' => 1, 'period' => 'minute'], 1);

        service('queue')->push('default', 'success', []);
        service('queue')->push('default', 'success', []);

        // 2s deadline: sleeping 60s would blow past it, so worker must exit after 1 job.
        $worker = new BoundedQueueWorker(['default'], microtime(true) + 2);
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

    // Batch accumulation tests

    public function testBatchableJob_MergesUpToMaxBatchSizeJobsIntoOneCall(): void
    {
        service('queue')->push('default', 'batchable', ['row' => 'A']);
        service('queue')->push('default', 'batchable', ['row' => 'B']);
        service('queue')->push('default', 'batchable', ['row' => 'C']);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        // BatchableTestJob::maxBatchSize() = 3, so all 3 jobs merge into 1 call.
        $this->assertSame(3, $worker->getProcessedCount());
        $this->assertCount(1, BatchableTestJob::$receivedData);

        $batchData = BatchableTestJob::$receivedData[0];
        $this->assertArrayHasKey('_batch', $batchData);
        $this->assertCount(3, $batchData['_batch']);
    }

    public function testBatchableJob_DoesNotMergeDifferentJobClasses(): void
    {
        service('queue')->push('default', 'batchable', ['row' => 'A']);
        service('queue')->push('default', 'success', []);
        service('queue')->push('default', 'batchable', ['row' => 'C']);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        // First batchable pops, then finds 'success' next — different class,
        // puts it back and closes batch with 1 item. Then success runs. Then
        // the third batchable runs as single.
        $this->assertSame(3, $worker->getProcessedCount());

        // Each batchable ran as a single (no _batch key) since they were separated.
        foreach (BatchableTestJob::$receivedData as $callData) {
            $this->assertArrayNotHasKey('_batch', $callData);
        }
    }

    public function testBatchableJob_SingleJobRunsWithoutBatchWrapper(): void
    {
        service('queue')->push('default', 'batchable', ['row' => 'only']);

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(1, $worker->getProcessedCount());
        $this->assertCount(1, BatchableTestJob::$receivedData);

        // Only one item — no _batch wrapping.
        $this->assertArrayNotHasKey('_batch', BatchableTestJob::$receivedData[0]);
    }

    public function testBatchableJob_RespectsBatchSizeCap(): void
    {
        // Push 5 jobs, but maxBatchSize() = 3 — expect 2 process() calls.
        for ($i = 1; $i <= 5; $i++) {
            service('queue')->push('default', 'batchable', ['row' => $i]);
        }

        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(5, $worker->getProcessedCount());

        $callCount = count(BatchableTestJob::$receivedData);
        // First call: batch of 3 (has _batch). Second call: batch of 2 (has _batch).
        $this->assertSame(2, $callCount);
        $this->assertArrayHasKey('_batch', BatchableTestJob::$receivedData[0]);
        $this->assertCount(3, BatchableTestJob::$receivedData[0]['_batch']);
        $this->assertArrayHasKey('_batch', BatchableTestJob::$receivedData[1]);
        $this->assertCount(2, BatchableTestJob::$receivedData[1]['_batch']);
    }

    // Queue-scoped throttle tests

    public function testQueueScopedThrottle_OnlyThrottlesMatchingQueue(): void
    {
        model(JobThrottle::class)->saveValue(['max_count' => 1, 'period' => 'minute', 'queue' => 'api'], 1);

        service('queue')->push('default', 'success', []);
        service('queue')->push('default', 'success', []);

        // 'default' queue is not subject to the 'api' throttle — both jobs must run.
        $worker = new BoundedQueueWorker(['default'], microtime(true) + 5);
        $worker->run();

        $this->assertSame(2, $worker->getProcessedCount());
        $this->assertSame(0, $this->db->table('queue_jobs')->countAllResults());
    }

    public function testQueueScopedThrottle_ThrottlesJobsOnMatchingQueue(): void
    {
        model(JobThrottle::class)->saveValue(['max_count' => 1, 'period' => 'minute', 'queue' => 'api'], 1);

        service('queue')->push('api', 'success', []);
        service('queue')->push('api', 'success', []);

        // After 1 job on 'api', the api-scoped throttle trips — 2nd job stays queued.
        $worker = new BoundedQueueWorker(['api'], microtime(true) + 2);
        $worker->run();

        $this->assertSame(1, $worker->getProcessedCount());
        $this->assertSame(1, $this->db->table('queue_jobs')->countAllResults());
    }
}
