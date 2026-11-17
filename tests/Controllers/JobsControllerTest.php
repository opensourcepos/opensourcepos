<?php

namespace Tests\Controllers;

use App\Models\ImportBatch;
use App\Models\JobThrottle;
use App\Models\QueueJobModel;
use Closure;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\QueueJobFixtureTrait;
use Throwable;

class JobsControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use FeatureTestTrait;
    use QueueJobFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = 'App';

    private const MYSQL_ER_LOCK_WAIT_TIMEOUT = 1205;

    protected JobThrottle $jobThrottle;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();

        $this->jobThrottle = model(JobThrottle::class);
        $this->db->table('job_throttles')->truncate();
        $this->db->table('queue_jobs_paused')->truncate();
        $this->db->table('queue_jobs')->truncate();
        $this->db->table('queue_jobs_failed')->truncate();
        $this->db->table('import_batches')->truncate();
    }

    protected function loginAsAdmin(): void
    {
        $this->withSession([
            'person_id'  => 1,
            'menu_group' => 'office',
        ]);
    }

    public function testGetIndexRendersManageView(): void
    {
        $this->loginAsAdmin();

        $response = $this->get('/jobs');

        $response->assertStatus(200);
        $response->assertSee(lang('Jobs.settings'));
    }

    public function testPostSaveSettingsRejectsInvalidMode(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveSettings', [
            'mode'             => 'bogus',
            'web_max_seconds'  => 5,
            'task_max_seconds' => 30,
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostSaveSettingsRejectsNonNaturalMaxSeconds(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveSettings', [
            'mode'             => 'web',
            'web_max_seconds'  => -5,
            'task_max_seconds' => 30,
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostSaveSettingsSavesValidSettings(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveSettings', [
            'mode'                   => 'manual',
            'web_max_seconds'        => 10,
            'task_max_seconds'       => 20,
            'retry_limit'            => 5,
            'auto_purge'             => '1',
            'retention_days'         => 14,
            'failed_retention_days'  => 60,
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('app_config', ['key' => 'jobs_mode', 'value' => 'manual']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_web_max_seconds', 'value' => '10']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_task_max_seconds', 'value' => '20']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_retry_limit', 'value' => '5']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_auto_purge', 'value' => '1']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_retention_days', 'value' => '14']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_failed_retention_days', 'value' => '60']);
    }

    public function testPostSaveThrottlesSavesAndDeletesMissingThrottles(): void
    {
        $this->loginAsAdmin();
        $this->jobThrottle->saveValue(['max_count' => 5, 'period' => 'minute'], 1);
        $this->jobThrottle->saveValue(['max_count' => 20, 'period' => 'day'], 2);

        $response = $this->post('/jobs/saveThrottles', [
            'throttle_count_1'  => 15,
            'throttle_period_1' => 'hour',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('job_throttles', ['throttle_id' => 1, 'max_count' => 15, 'period' => 'hour', 'deleted' => 0]);
        $this->seeInDatabase('job_throttles', ['throttle_id' => 2, 'deleted' => 1]);
    }

    public function testPostSaveThrottlesAcceptsSecondPeriod(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveThrottles', [
            'throttle_count_1'  => 10,
            'throttle_period_1' => 'second',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('job_throttles', ['throttle_id' => 1, 'max_count' => 10, 'period' => 'second', 'deleted' => 0]);
    }

    public function testPostSaveThrottlesRejectsInvalidPeriod(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveThrottles', [
            'throttle_count_1'  => 15,
            'throttle_period_1' => 'fortnight',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostSaveThrottlesLeavesExistingThrottlesUnchangedWhenPayloadInvalid(): void
    {
        $this->loginAsAdmin();
        $this->jobThrottle->saveValue(['max_count' => 5, 'period' => 'minute'], 1);
        $this->jobThrottle->saveValue(['max_count' => 20, 'period' => 'day'], 2);

        $response = $this->post('/jobs/saveThrottles', [
            'throttle_count_2'  => 15,
            'throttle_period_2' => 'fortnight',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);

        $this->seeInDatabase('job_throttles', ['throttle_id' => 1, 'max_count' => 5, 'period' => 'minute', 'deleted' => 0]);
        $this->seeInDatabase('job_throttles', ['throttle_id' => 2, 'max_count' => 20, 'period' => 'day', 'deleted' => 0]);
    }

    public function testGetThrottlesRendersPartial(): void
    {
        $this->loginAsAdmin();
        $this->jobThrottle->saveValue(['max_count' => 5, 'period' => 'minute'], 1);

        $response = $this->get('/jobs/throttles');

        $response->assertStatus(200);
        $response->assertSeeElement('#throttle_count_1');
    }

    public function testPostProcessJobsDrainsEmptyQueuesSuccessfully(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/processJobs', ['scope' => 'all']);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testPostProcessJobsRejectsEmptySelection(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/processJobs', ['scope' => 'selected']);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostProcessJobsIgnoresUnknownQueueNames(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/processJobs', [
            'scope'         => 'selected',
            'selected_jobs' => ['plugin.some_plugin', 'default'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testPostPauseJobsMovesPendingJobToPausedTable(): void
    {
        $this->loginAsAdmin();
        $this->createPendingJob('default');

        $response = $this->post('/jobs/pauseJobs', ['scope' => 'all']);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('queue_jobs', ['queue' => 'default']);
        $this->seeInDatabase('queue_jobs_paused', ['queue' => 'default', 'paused_by' => 'manual']);
    }

    public function testPostPauseJobsLeavesReservedJobInQueue(): void
    {
        $this->loginAsAdmin();
        $this->createReservedJob('default');

        $response = $this->post('/jobs/pauseJobs', ['scope' => 'all']);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('queue_jobs', ['queue' => 'default', 'status' => 1]);
        $this->dontSeeInDatabase('queue_jobs_paused', ['queue' => 'default']);
    }

    public function testPostPauseJobsRejectsEmptySelection(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/pauseJobs', ['scope' => 'selected']);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    // -------------------------------------------------------------------------
    // getSearch
    // -------------------------------------------------------------------------

    public function testGetSearch_NoStatusFilter_ReturnsAllSources(): void
    {
        $this->loginAsAdmin();

        $this->createPendingJob('default');
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => time(),
        ]);

        $response = $this->get('/jobs/search?limit=25&offset=0');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $sources = array_column($result['rows'], 'source');

        $this->assertContains('pending', $sources);
        $this->assertContains('failed', $sources);
        $this->assertSame(2, $result['total']);
    }

    public function testGetSearch_StatusFilterPending_ExcludesFailedRows(): void
    {
        $this->loginAsAdmin();

        $this->createPendingJob('default');
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => time(),
        ]);

        $response = $this->get('/jobs/search?limit=25&offset=0&statuses[]=pending');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $sources = array_column($result['rows'], 'source');

        $this->assertContains('pending', $sources);
        $this->assertNotContains('failed', $sources);
        $this->assertSame(1, $result['total']);
    }

    public function testGetSearch_PaginatedResponse_TotalExceedsPageRows(): void
    {
        $this->loginAsAdmin();

        for ($i = 0; $i < 5; $i++) {
            $this->createPendingJob('default');
        }

        $response = $this->get('/jobs/search?limit=2&offset=0');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);

        $this->assertCount(2, $result['rows']);
        $this->assertSame(5, $result['total']);
    }

    public function testGetSearch_EachRowHasUid(): void
    {
        $this->loginAsAdmin();

        $this->createPendingJob('default');
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => time(),
        ]);

        $response = $this->get('/jobs/search?limit=25&offset=0');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);

        foreach ($result['rows'] as $row) {
            $this->assertArrayHasKey('uid', $row);
            $this->assertNotEmpty($row['uid']);
        }
    }

    public function testGetSearch_UidsAreUniqueAcrossSources(): void
    {
        $this->loginAsAdmin();

        $this->createPendingJob('default');
        $this->createReservedJob('default');
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => time(),
        ]);

        $response = $this->get('/jobs/search?limit=25&offset=0');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $uids = array_column($result['rows'], 'uid');

        $this->assertSame(count($uids), count(array_unique($uids)));
    }

    public function testGetSearch_UidIsStableAcrossPolls(): void
    {
        $this->loginAsAdmin();

        $this->createPendingJob('default');

        $first  = json_decode($this->get('/jobs/search?limit=25&offset=0')->getJSON(), true);
        $second = json_decode($this->get('/jobs/search?limit=25&offset=0')->getJSON(), true);

        $this->assertSame(
            array_column($first['rows'], 'uid'),
            array_column($second['rows'], 'uid')
        );
    }

    // -------------------------------------------------------------------------
    // getSearchBatches
    // -------------------------------------------------------------------------

    public function testGetSearchBatches_ReturnsBatchRows(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-aaa', 'processing', 10, 3);
        $this->createBatchAt('batch-bbb', 'completed', 5, 5);

        $response = $this->get('/jobs/searchBatches?limit=25&offset=0');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);

        $this->assertSame(2, $result['total']);
        $this->assertCount(2, $result['rows']);

        $ids = array_column($result['rows'], 'batch_id');
        $this->assertContains('batch-aaa', $ids);
        $this->assertContains('batch-bbb', $ids);
    }

    public function testGetSearchBatches_StatusFilter_ExcludesNonMatchingRows(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-proc');
        $this->createBatchAt('batch-done', 'completed', 5, 5);

        $response = $this->get('/jobs/searchBatches?limit=25&offset=0&statuses[]=completed');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);

        $this->assertSame(1, $result['total']);
        $this->assertSame('batch-done', $result['rows'][0]['batch_id']);
    }

    public function testGetSearchBatches_Pagination_TotalExceedsPageRows(): void
    {
        $this->loginAsAdmin();

        for ($i = 1; $i <= 4; $i++) {
            $this->createBatch("batch-$i");
        }

        $response = $this->get('/jobs/searchBatches?limit=2&offset=0');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);

        $this->assertCount(2, $result['rows']);
        $this->assertSame(4, $result['total']);
    }

    public function testGetSearchBatches_EachRowHasProgressBar(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-prog', 'processing', 10, 4);

        $response = $this->get('/jobs/searchBatches?limit=25&offset=0');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);

        $this->assertArrayHasKey('progress', $result['rows'][0]);
        $this->assertStringContainsString('progress-bar', $result['rows'][0]['progress']);
    }

    // -------------------------------------------------------------------------
    // postDeleteBatch
    // -------------------------------------------------------------------------

    public function testPostDeleteBatch_RejectsEmptyId(): void
    {
        $this->loginAsAdmin();

        $result = json_decode($this->post('/jobs/deleteBatch', ['id' => ''])->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostDeleteBatch_RejectsUnknownBatch(): void
    {
        $this->loginAsAdmin();

        $result = json_decode($this->post('/jobs/deleteBatch', ['id' => 'no-such-batch'])->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostDeleteBatch_DeletesBatchAndPendingJobs(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-del');
        $this->createPendingJob('imports', 'batch-del');

        $result = json_decode($this->post('/jobs/deleteBatch', ['id' => 'batch-del'])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('import_batches', ['id' => 'batch-del']);
        $this->dontSeeInDatabase('queue_jobs', ['batch_id' => 'batch-del']);
    }

    public function testPostDeleteBatch_LeavesReservedJobsInQueue(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-res');
        $this->createReservedJob('imports', 'batch-res');

        $result = json_decode($this->post('/jobs/deleteBatch', ['id' => 'batch-res'])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('queue_jobs', ['batch_id' => 'batch-res', 'status' => 1]);
    }

    // -------------------------------------------------------------------------
    // postPauseBatch
    // -------------------------------------------------------------------------

    public function testPostPauseBatch_RejectsUnknownBatch(): void
    {
        $this->loginAsAdmin();

        $result = json_decode($this->post('/jobs/pauseBatch', ['id' => 'no-such-batch'])->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostPauseBatch_MovesPendingJobsToPausedTable(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-pause');
        $this->createPendingJob('imports', 'batch-pause');

        $result = json_decode($this->post('/jobs/pauseBatch', ['id' => 'batch-pause'])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('queue_jobs', ['batch_id' => 'batch-pause', 'status' => 0]);
        $this->seeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-pause', 'paused_by' => 'manual']);
    }

    public function testPostPauseBatch_LeavesReservedJobsInQueue(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-pauseres');
        $this->createReservedJob('imports', 'batch-pauseres');

        $result = json_decode($this->post('/jobs/pauseBatch', ['id' => 'batch-pauseres'])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('queue_jobs', ['batch_id' => 'batch-pauseres', 'status' => 1]);
        $this->dontSeeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-pauseres']);
    }

    // -------------------------------------------------------------------------
    // postResumeBatch
    // -------------------------------------------------------------------------

    public function testPostResumeBatch_RejectsUnknownBatch(): void
    {
        $this->loginAsAdmin();

        $result = json_decode($this->post('/jobs/resumeBatch', ['id' => 'no-such-batch'])->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostResumeBatch_MovesPausedJobsBackToQueue(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-resume');
        $this->createPausedJob('imports', 'batch-resume');

        $result = json_decode($this->post('/jobs/resumeBatch', ['id' => 'batch-resume'])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-resume']);
        $resumed = $this->db->table('queue_jobs')->where('queue', 'imports')->where('status', 0)->get()->getRowArray();
        $this->assertNotNull($resumed);
        $this->assertNull($resumed['batch_id']);
    }

    // -------------------------------------------------------------------------
    // postPurgeBatch
    // -------------------------------------------------------------------------

    public function testPostPurgeBatch_RejectsUnknownBatch(): void
    {
        $this->loginAsAdmin();

        $result = json_decode($this->post('/jobs/purgeBatch', ['id' => 'no-such-batch'])->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostPurgeBatch_RejectsProcessingBatch(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-proc2');

        $result = json_decode($this->post('/jobs/purgeBatch', ['id' => 'batch-proc2'])->getJSON(), true);
        $this->assertFalse($result['success']);

        $this->seeInDatabase('import_batches', ['id' => 'batch-proc2']);
    }

    public function testPostPurgeBatch_DeletesCompletedBatchAndFailedJobs(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-purge', 'completed', 5, 5);
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'imports',
            'batch_id'   => 'batch-purge',
            'payload'    => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'   => 'low',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: failed',
            'failed_at'  => time(),
        ]);

        $result = json_decode($this->post('/jobs/purgeBatch', ['id' => 'batch-purge'])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('import_batches', ['id' => 'batch-purge']);
        $this->dontSeeInDatabase('queue_jobs_failed', ['batch_id' => 'batch-purge']);
    }

    public function testPostPurgeBatch_AcceptsPartialBatch(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-partial', 'partial', 5, 4, 1);

        $result = json_decode($this->post('/jobs/purgeBatch', ['id' => 'batch-partial'])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('import_batches', ['id' => 'batch-partial']);
    }

    // -------------------------------------------------------------------------
    // postProcessJob — pause/process concurrency guard
    // -------------------------------------------------------------------------

    public function testPostProcessJob_RejectsJobGoneFromQueue(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->createPendingJob();
        $this->db->table('queue_jobs')->where('id', $jobId)->delete();

        $result = json_decode($this->post('/jobs/processJob', ['id' => "pending:$jobId"])->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostProcessJob_RejectsJobClaimedByWorkerBeforeReservation(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->createPendingJob();
        $this->db->table('queue_jobs')->where('id', $jobId)->update(['status' => 1]);

        $result = json_decode($this->post('/jobs/processJob', ['id' => "pending:$jobId"])->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostProcessJob_RejectsAlreadyReservedJob(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->createReservedJob();

        $result = json_decode($this->post('/jobs/processJob', ['id' => "reserved:$jobId"])->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostProcessJob_RequeuesFailedJobPreservingBatchAndContext(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-requeue');
        $this->db->table('import_batch_contexts')->where('batch_id', 'batch-requeue')->delete();
        $this->db->table('import_batch_contexts')->insert([
            'batch_id'   => 'batch-requeue',
            'context'    => '{}',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $contextId = (int)$this->db->insertID();

        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'imports',
            'batch_id'   => 'batch-requeue',
            'context_id' => $contextId,
            'payload'    => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => time(),
        ]);
        $failedId = (int)$this->db->insertID();

        $this->post('/jobs/processJob', ['id' => "failed:$failedId"]);

        $this->dontSeeInDatabase('queue_jobs_failed', ['id' => $failedId, 'attempts' => 3]);

        $requeued = $this->db->table('queue_jobs')->where('batch_id', 'batch-requeue')->get()->getRowArray();
        $this->assertNotNull($requeued);
        $this->assertSame($contextId, (int)$requeued['context_id']);
        $this->assertGreaterThan(0, (int)$requeued['created_at']);
    }

    public function testPostProcessJob_RequeueReleasesPriorFailedCountSoRetryIsNotDoubleCounted(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-requeue-count', 'partial', 5, 4, 1);

        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'imports',
            'batch_id'   => 'batch-requeue-count',
            'payload'    => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => time(),
        ]);
        $failedId = (int)$this->db->insertID();

        $this->post('/jobs/processJob', ['id' => "failed:$failedId"]);

        $batch = $this->getBatchRow('batch-requeue-count');
        $this->assertSame(5, (int)$batch['completed'] + (int)$batch['failed']);
        $this->assertContains($batch['status'], ['completed', 'partial']);
    }

    public function testPostPauseJobs_SkipsJobReservedByWorker(): void
    {
        $this->loginAsAdmin();

        $this->createReservedJob('default');

        $result = json_decode($this->post('/jobs/pauseJobs', ['scope' => 'all'])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('queue_jobs', ['queue' => 'default', 'status' => 1]);
        $this->dontSeeInDatabase('queue_jobs_paused', ['queue' => 'default']);
    }

    public function testPostPauseBatch_SkipsJobReservedByWorker(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-concurrent');
        $this->createReservedJob('imports', 'batch-concurrent');

        $result = json_decode($this->post('/jobs/pauseBatch', ['id' => 'batch-concurrent'])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('queue_jobs', ['batch_id' => 'batch-concurrent', 'status' => 1]);
        $this->dontSeeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-concurrent']);
    }

    // -------------------------------------------------------------------------
    // pause → resume → processJob interleaving
    // -------------------------------------------------------------------------

    public function testPauseResumeProcessJob_JobExecutedExactlyOnce(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->createPendingJob();

        $this->post('/jobs/pauseJobs', ['scope' => 'all']);

        $this->dontSeeInDatabase('queue_jobs', ['id' => $jobId]);
        $this->seeInDatabase('queue_jobs_paused', ['queue' => 'default']);

        $this->post('/jobs/resumeJobs', ['scope' => 'all']);

        $this->seeInDatabase('queue_jobs', ['id' => $jobId, 'status' => 0]);
        $this->dontSeeInDatabase('queue_jobs_paused', ['queue' => 'default']);

        $queueJobModel = model(QueueJobModel::class);

        $this->assertTrue($queueJobModel->claimJob($jobId));
        $this->seeInDatabase('queue_jobs', ['id' => $jobId, 'status' => 1]);
        $this->assertFalse($queueJobModel->claimJob($jobId));
    }

    public function testPauseResume_SecondProcessJobClaimRejected(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->createPendingJob();

        $this->post('/jobs/pauseJobs', ['scope' => 'all']);
        $this->post('/jobs/resumeJobs', ['scope' => 'all']);

        $this->seeInDatabase('queue_jobs', ['id' => $jobId, 'status' => 0]);

        model(QueueJobModel::class)->claimJob($jobId);

        $result = json_decode($this->post('/jobs/processJob', ['id' => "pending:$jobId"])->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPauseBatchResumeProcessJob_JobNotDuplicated(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-prp');
        $this->createPendingJob('imports', 'batch-prp');

        $this->post('/jobs/pauseBatch', ['id' => 'batch-prp']);

        $this->dontSeeInDatabase('queue_jobs', ['batch_id' => 'batch-prp', 'status' => 0]);
        $this->seeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-prp']);

        $this->post('/jobs/resumeBatch', ['id' => 'batch-prp']);

        $this->dontSeeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-prp']);

        $resumedJob = $this->db->table('queue_jobs')->where('queue', 'imports')->where('status', 0)->get()->getRowArray();
        $this->assertNotNull($resumedJob);
        $this->assertNull($resumedJob['batch_id']);

        $queueJobModel = model(QueueJobModel::class);
        $this->assertTrue($queueJobModel->claimJob((int)$resumedJob['id']));
        $this->assertFalse($queueJobModel->claimJob((int)$resumedJob['id']));

        $this->dontSeeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-prp']);
    }

    // -------------------------------------------------------------------------
    // postDelete — batch accounting
    // -------------------------------------------------------------------------

    public function testPostDelete_PendingJobWithBatch_DecrementsBatchTotal(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-del-pending', 'processing', 3, 1);
        $jobId = $this->createPendingJob('imports', 'batch-del-pending');

        $result = json_decode($this->post('/jobs/delete', ['ids' => ["pending:$jobId"]])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('queue_jobs', ['id' => $jobId]);
        $this->assertSame(2, (int)$this->getBatchRow('batch-del-pending')['total']);
    }

    public function testPostDelete_PendingJobWithBatch_CompletesWhenTotalMet(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-del-complete', 'processing', 2, 1);
        $jobId = $this->createPendingJob('imports', 'batch-del-complete');

        $this->post('/jobs/delete', ['ids' => ["pending:$jobId"]]);

        $this->assertSame('completed', $this->getBatchRow('batch-del-complete')['status']);
    }

    public function testPostDelete_OnlyPendingJobInBatch_CompletesBatch(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-del-only', 'processing', 1, 0);
        $jobId = $this->createPendingJob('imports', 'batch-del-only');

        $this->post('/jobs/delete', ['ids' => ["pending:$jobId"]]);

        $batch = $this->getBatchRow('batch-del-only');
        $this->assertSame(0, (int)$batch['total']);
        $this->assertSame('completed', $batch['status']);
    }

    public function testPostDelete_AccountingFailure_RollsBackJobDeletion(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-del-rollback', 'processing', 2, 0);
        $jobId = $this->createPendingJob('imports', 'batch-del-rollback');

        $failingBatch = new class () extends ImportBatch {
            public function adjustTotal(string $batchId, int $delta): bool
            {
                try {
                    $this->db->query('SELECT * FROM table_that_does_not_exist');
                } catch (Throwable $e) {
                }

                return false;
            }
        };
        Factories::injectMock('models', ImportBatch::class, $failingBatch);

        try {
            $this->post('/jobs/delete', ['ids' => ["pending:$jobId"]]);
        } finally {
            Factories::reset('models');
        }

        $this->seeInDatabase('queue_jobs', ['id' => $jobId]);
        $this->assertSame(2, (int)$this->getBatchRow('batch-del-rollback')['total']);
    }

    public function testPostDelete_PausedJobWithBatch_DoesNotDoubleDecrementTotal(): void
    {
        $this->loginAsAdmin();

        $this->createBatch('batch-del-paused', total: 2);
        $pausedId = $this->createPausedJob('imports', 'batch-del-paused');

        $totalBefore = (int)$this->getBatchRow('batch-del-paused')['total'];

        $result = json_decode($this->post('/jobs/delete', ['ids' => ["paused:$pausedId"]])->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('queue_jobs_paused', ['id' => $pausedId]);
        $this->assertSame($totalBefore, (int)$this->getBatchRow('batch-del-paused')['total']);
    }

    public function testPostDelete_JobReservedAfterListing_BlocksDeletionAndLeavesBatchTotal(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-del-race', 'processing', 2, 0);
        $jobId = $this->createReservedJob('imports', 'batch-del-race');

        $result = json_decode($this->post('/jobs/delete', ['ids' => ["pending:$jobId"]])->getJSON(), true);

        $this->assertFalse($result['success']);
        $this->seeInDatabase('queue_jobs', ['id' => $jobId]);
        $batch = $this->getBatchRow('batch-del-race');
        $this->assertSame(2, (int)$batch['total']);
        $this->assertSame('processing', $batch['status']);
    }

    public function testPostDelete_WorkerCannotReserveJobWhileDeletionHoldsLock(): void
    {
        $this->loginAsAdmin();

        $this->createBatchAt('batch-del-lock', 'processing', 2, 0);
        $jobId = $this->createPendingJob('imports', 'batch-del-lock');

        $workerConnection = Database::connect($this->DBGroup, false);
        $workerErrorCode = null;

        $lockingModel = new class () extends QueueJobModel {
            public ?Closure $afterLock = null;

            public function getPendingForUpdateByIdOrQueues(?int $id, ?array $queues): array
            {
                $rows = parent::getPendingForUpdateByIdOrQueues($id, $queues);

                if ($this->afterLock !== null) {
                    ($this->afterLock)();
                }

                return $rows;
            }
        };
        $lockingModel->afterLock = static function () use ($workerConnection, $jobId, &$workerErrorCode): void {
            $workerConnection->query('SET SESSION innodb_lock_wait_timeout = 1');

            try {
                $workerConnection->table('queue_jobs')
                    ->where('id', $jobId)
                    ->where('status', 0)
                    ->update(['status' => 1]);
            } catch (DatabaseException $e) {
                $workerErrorCode = $e->getCode();
            }
        };
        Factories::injectMock('models', QueueJobModel::class, $lockingModel);

        try {
            $result = json_decode($this->post('/jobs/delete', ['ids' => ["pending:$jobId"]])->getJSON(), true);
        } finally {
            Factories::reset('models');
            $workerConnection->close();
        }

        $this->assertSame(self::MYSQL_ER_LOCK_WAIT_TIMEOUT, $workerErrorCode);
        $this->assertTrue($result['success']);
        $this->dontSeeInDatabase('queue_jobs', ['id' => $jobId]);
        $this->assertSame(1, (int)$this->getBatchRow('batch-del-lock')['total']);
    }

    public function testPostDelete_ReservedJob_BlocksDeletion(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->createReservedJob('imports');

        $result = json_decode($this->post('/jobs/delete', ['ids' => ["reserved:$jobId"]])->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->seeInDatabase('queue_jobs', ['id' => $jobId]);
    }

    public function testPostDelete_PendingJobWithoutBatch_DeletesWithoutError(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->createPendingJob();

        $result = json_decode($this->post('/jobs/delete', ['ids' => ["pending:$jobId"]])->getJSON(), true);
        $this->assertTrue($result['success']);
        $this->dontSeeInDatabase('queue_jobs', ['id' => $jobId]);
    }
}
