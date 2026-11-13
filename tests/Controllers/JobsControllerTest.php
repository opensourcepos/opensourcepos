<?php

namespace Tests\Controllers;

use App\Models\JobThrottle;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class JobsControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $refresh = false;
    protected $namespace = 'App';

    private static bool $doneBootstrap = false;

    protected JobThrottle $jobThrottle;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

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
            'menu_group' => 'office'
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

        // Throttle 2 is omitted from the post payload, so it should be soft-deleted.
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

        // Throttle 2 is invalid and throttle 1 is omitted; neither should be touched.
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

        $this->db->table('queue_jobs')->insert([
            'queue'        => 'default',
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'low',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => time(),
            'created_at'   => time(),
        ]);

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

        $this->db->table('queue_jobs')->insert([
            'queue'        => 'default',
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'low',
            'status'       => 1,
            'attempts'     => 0,
            'available_at' => time(),
            'created_at'   => time(),
        ]);

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

    public function testGetSearch_NoStatusFilter_ReturnsAllSources(): void
    {
        $this->loginAsAdmin();

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'default',
            'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'     => 'normal',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => $now,
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

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'default',
            'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'     => 'normal',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => $now,
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

        $now = time();
        for ($i = 0; $i < 5; $i++) {
            $this->db->table('queue_jobs')->insert([
                'queue'        => 'default',
                'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
                'priority'     => 'normal',
                'status'       => 0,
                'attempts'     => 0,
                'available_at' => $now,
                'created_at'   => $now,
            ]);
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

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'default',
            'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'     => 'normal',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => $now,
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

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'default',
            'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'     => 'normal',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'default',
            'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'     => 'normal',
            'status'       => 1,
            'attempts'     => 1,
            'available_at' => $now,
            'created_at'   => $now,
        ]);
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test',
            'failed_at'  => $now,
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

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'default',
            'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'     => 'normal',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);

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

    private function insertBatch(string $id, string $status = 'processing', int $total = 5, int $completed = 0, int $failed = 0): void
    {
        $this->db->table('import_batches')->insert([
            'id'         => $id,
            'type'       => 'items',
            'total'      => $total,
            'completed'  => $completed,
            'failed'     => $failed,
            'status'     => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function testGetSearchBatches_ReturnsBatchRows(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-aaa', 'processing', 10, 3);
        $this->insertBatch('batch-bbb', 'completed', 5, 5);

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
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-proc', 'processing');
        $this->insertBatch('batch-done', 'completed');

        $response = $this->get('/jobs/searchBatches?limit=25&offset=0&statuses[]=completed');

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);

        $this->assertSame(1, $result['total']);
        $this->assertSame('batch-done', $result['rows'][0]['batch_id']);
    }

    public function testGetSearchBatches_Pagination_TotalExceedsPageRows(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        for ($i = 1; $i <= 4; $i++) {
            $this->insertBatch("batch-$i");
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
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-prog', 'processing', 10, 4);

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

        $response = $this->post('/jobs/deleteBatch', ['id' => '']);

        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostDeleteBatch_RejectsUnknownBatch(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/deleteBatch', ['id' => 'no-such-batch']);

        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostDeleteBatch_DeletesBatchAndPendingJobs(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-del');

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'imports',
            'batch_id'     => 'batch-del',
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'low',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);

        $response = $this->post('/jobs/deleteBatch', ['id' => 'batch-del']);

        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('import_batches', ['id' => 'batch-del']);
        $this->dontSeeInDatabase('queue_jobs', ['batch_id' => 'batch-del']);
    }

    public function testPostDeleteBatch_LeavesReservedJobsInQueue(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-res');

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'imports',
            'batch_id'     => 'batch-res',
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'low',
            'status'       => 1,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);

        $response = $this->post('/jobs/deleteBatch', ['id' => 'batch-res']);

        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('queue_jobs', ['batch_id' => 'batch-res', 'status' => 1]);
    }

    // -------------------------------------------------------------------------
    // postPauseBatch
    // -------------------------------------------------------------------------

    public function testPostPauseBatch_RejectsUnknownBatch(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/pauseBatch', ['id' => 'no-such-batch']);

        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostPauseBatch_MovesPendingJobsToPausedTable(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-pause');

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'imports',
            'batch_id'     => 'batch-pause',
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'low',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);

        $response = $this->post('/jobs/pauseBatch', ['id' => 'batch-pause']);

        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('queue_jobs', ['batch_id' => 'batch-pause', 'status' => 0]);
        $this->seeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-pause', 'paused_by' => 'manual']);
    }

    public function testPostPauseBatch_LeavesReservedJobsInQueue(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-pauseres');

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'imports',
            'batch_id'     => 'batch-pauseres',
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'low',
            'status'       => 1,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);

        $response = $this->post('/jobs/pauseBatch', ['id' => 'batch-pauseres']);

        $result = json_decode($response->getJSON(), true);
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

        $response = $this->post('/jobs/resumeBatch', ['id' => 'no-such-batch']);

        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostResumeBatch_MovesPausedJobsBackToQueue(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-resume');

        $now = time();
        $this->db->table('queue_jobs_paused')->insert([
            'id'           => 999,
            'queue'        => 'imports',
            'batch_id'     => 'batch-resume',
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'low',
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
            'paused_at'    => $now,
            'paused_by'    => 'manual',
        ]);

        $response = $this->post('/jobs/resumeBatch', ['id' => 'batch-resume']);

        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-resume']);
        $this->seeInDatabase('queue_jobs', ['batch_id' => 'batch-resume', 'status' => 0]);
    }

    // -------------------------------------------------------------------------
    // postPurgeBatch
    // -------------------------------------------------------------------------

    public function testPostPurgeBatch_RejectsUnknownBatch(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/purgeBatch', ['id' => 'no-such-batch']);

        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostPurgeBatch_RejectsProcessingBatch(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-proc2', 'processing');

        $response = $this->post('/jobs/purgeBatch', ['id' => 'batch-proc2']);

        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);

        $this->seeInDatabase('import_batches', ['id' => 'batch-proc2']);
    }

    public function testPostPurgeBatch_DeletesCompletedBatchAndFailedJobs(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-purge', 'completed', 5, 5);

        $now = time();
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'imports',
            'batch_id'   => 'batch-purge',
            'payload'    => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'   => 'low',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: failed',
            'failed_at'  => $now,
        ]);

        $response = $this->post('/jobs/purgeBatch', ['id' => 'batch-purge']);

        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('import_batches', ['id' => 'batch-purge']);
        $this->dontSeeInDatabase('queue_jobs_failed', ['batch_id' => 'batch-purge']);
    }

    public function testPostPurgeBatch_AcceptsPartialBatch(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-partial', 'partial', 5, 4, 1);

        $response = $this->post('/jobs/purgeBatch', ['id' => 'batch-partial']);

        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->dontSeeInDatabase('import_batches', ['id' => 'batch-partial']);
    }

    // -------------------------------------------------------------------------
    // postProcessJob — pause/process concurrency guard
    // -------------------------------------------------------------------------

    private function insertQueueJob(string $queue = 'default', int $status = 0): int
    {
        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => $queue,
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'normal',
            'status'       => $status,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);

        return (int)$this->db->insertID();
    }

    public function testPostProcessJob_RejectsJobGoneFromQueue(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->insertQueueJob();

        $this->db->table('queue_jobs')->where('id', $jobId)->delete();

        $response = $this->post('/jobs/processJob', ['id' => "pending:$jobId"]);

        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostProcessJob_RejectsJobClaimedByWorkerBeforeReservation(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->insertQueueJob();

        $this->db->table('queue_jobs')
            ->where('id', $jobId)
            ->update(['status' => 1]);

        $response = $this->post('/jobs/processJob', ['id' => "pending:$jobId"]);

        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostProcessJob_RejectsAlreadyReservedJob(): void
    {
        $this->loginAsAdmin();

        $jobId = $this->insertQueueJob(status: 1);

        $response = $this->post('/jobs/processJob', ['id' => "reserved:$jobId"]);

        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostPauseJobs_SkipsJobReservedByWorker(): void
    {
        $this->loginAsAdmin();

        $this->insertQueueJob(status: 1);

        $response = $this->post('/jobs/pauseJobs', ['scope' => 'all']);

        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('queue_jobs', ['queue' => 'default', 'status' => 1]);
        $this->dontSeeInDatabase('queue_jobs_paused', ['queue' => 'default']);
    }

    public function testPostPauseBatch_SkipsJobReservedByWorker(): void
    {
        $this->loginAsAdmin();
        $this->db->table('import_batches')->truncate();

        $this->insertBatch('batch-concurrent');

        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'imports',
            'batch_id'     => 'batch-concurrent',
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'normal',
            'status'       => 1,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);

        $response = $this->post('/jobs/pauseBatch', ['id' => 'batch-concurrent']);

        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('queue_jobs', ['batch_id' => 'batch-concurrent', 'status' => 1]);
        $this->dontSeeInDatabase('queue_jobs_paused', ['batch_id' => 'batch-concurrent']);
    }
}
