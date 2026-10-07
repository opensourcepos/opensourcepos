<?php

namespace Tests\Models;

use App\Models\ImportBatch;
use App\Models\ImportBatchContext;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class ImportBatchTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = false;
    protected $namespace = 'App';

    private static bool $doneBootstrap = false;

    protected ImportBatch $importBatch;
    protected ImportBatchContext $importBatchContext;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();

        $this->importBatch = model(ImportBatch::class);
        $this->importBatchContext = model(ImportBatchContext::class);

        $this->db->table('queue_jobs')->truncate();
        $this->db->table('queue_jobs_failed')->truncate();
        $this->db->table('import_batch_contexts')->where('id >', 0)->delete();
        $this->db->table('import_batches')->truncate();
    }

    public function testCreateInsertsProcessingBatch(): void
    {
        $this->importBatch->create('batch-1', 'items', 3);

        $this->seeInDatabase('import_batches', ['id' => 'batch-1', 'type' => 'items', 'total' => 3, 'status' => 'processing']);
    }

    public function testCreateWithZeroTotalIsImmediatelyCompleted(): void
    {
        $this->importBatch->create('batch-empty', 'items', 0);

        $this->seeInDatabase('import_batches', ['id' => 'batch-empty', 'status' => 'completed']);
    }

    public function testIncrementMarksBatchCompletedWhenAllRowsSucceed(): void
    {
        $this->importBatch->create('batch-2', 'items', 2);

        $this->assertFalse($this->importBatch->increment('batch-2', false));
        $this->assertTrue($this->importBatch->increment('batch-2', false));

        $this->seeInDatabase('import_batches', ['id' => 'batch-2', 'completed' => 2, 'status' => 'completed']);
    }

    public function testIncrementMarksBatchPartialWhenARowFails(): void
    {
        $this->importBatch->create('batch-3', 'items', 2);

        $this->assertFalse($this->importBatch->increment('batch-3', true));
        $this->assertTrue($this->importBatch->increment('batch-3', false));

        $this->seeInDatabase('import_batches', ['id' => 'batch-3', 'completed' => 1, 'failed' => 1, 'status' => 'partial']);
    }

    public function testPurgeFinishedDeletesOldCompletedBatchWithNoRemainingJobs(): void
    {
        $this->importBatch->create('batch-old', 'items', 1);
        $this->importBatch->increment('batch-old', false);
        $this->db->table('import_batches')->where('id', 'batch-old')->update(['updated_at' => date('Y-m-d H:i:s', strtotime('-10 days'))]);

        $this->importBatch->purgeFinished(7);

        $this->dontSeeInDatabase('import_batches', ['id' => 'batch-old']);
    }

    public function testPurgeFinishedKeepsRecentCompletedBatch(): void
    {
        $this->importBatch->create('batch-recent', 'items', 1);
        $this->importBatch->increment('batch-recent', false);

        $this->importBatch->purgeFinished(7);

        $this->seeInDatabase('import_batches', ['id' => 'batch-recent']);
    }

    public function testPurgeFinishedDoesNothingWhenRetentionIsZero(): void
    {
        $this->importBatch->create('batch-keep-forever', 'items', 1);
        $this->importBatch->increment('batch-keep-forever', false);
        $this->db->table('import_batches')->where('id', 'batch-keep-forever')->update(['updated_at' => date('Y-m-d H:i:s', strtotime('-100 days'))]);

        $this->importBatch->purgeFinished(0);

        $this->seeInDatabase('import_batches', ['id' => 'batch-keep-forever']);
    }

    /**
     * The anti-fragile guard: a batch past its retention window must not be
     * purged (and its import_batch_contexts row must survive) while a failed
     * job still references it in queue_jobs_failed — e.g. kept alive by a
     * longer failed-job retention window than the batch retention window.
     */
    public function testPurgeFinishedKeepsBatchAndContextWhileFailedJobStillReferencesIt(): void
    {
        $this->importBatch->create('batch-still-failed', 'items', 1);
        $this->importBatch->increment('batch-still-failed', true);
        $this->db->table('import_batches')->where('id', 'batch-still-failed')->update(['updated_at' => date('Y-m-d H:i:s', strtotime('-10 days'))]);

        $contextId = $this->importBatchContext->create('batch-still-failed', ['definition_names' => []]);

        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => 'imports',
            'batch_id'   => 'batch-still-failed',
            'context_id' => $contextId,
            'payload'    => '{}',
            'priority'   => 'low',
            'attempts'   => 3,
            'exception'  => 'boom',
            'failed_at'  => time(),
        ]);

        $this->importBatch->purgeFinished(7);

        $this->seeInDatabase('import_batches', ['id' => 'batch-still-failed']);
        $this->seeInDatabase('import_batch_contexts', ['id' => $contextId]);
    }

    public function testPurgeFinishedKeepsBatchWhilePendingJobStillReferencesIt(): void
    {
        $this->importBatch->create('batch-still-pending', 'items', 1);
        $this->importBatch->increment('batch-still-pending', false);
        $this->db->table('import_batches')->where('id', 'batch-still-pending')->update(['updated_at' => date('Y-m-d H:i:s', strtotime('-10 days'))]);

        $this->db->table('queue_jobs')->insert([
            'queue'        => 'imports',
            'batch_id'     => 'batch-still-pending',
            'payload'      => '{}',
            'priority'     => 'low',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => time(),
            'created_at'   => time(),
        ]);

        $this->importBatch->purgeFinished(7);

        $this->seeInDatabase('import_batches', ['id' => 'batch-still-pending']);
    }

    public function testPurgeFinishedDeletesContextOnceAllReferencingJobsAreGone(): void
    {
        $this->importBatch->create('batch-clean', 'items', 1);
        $this->importBatch->increment('batch-clean', false);
        $this->db->table('import_batches')->where('id', 'batch-clean')->update(['updated_at' => date('Y-m-d H:i:s', strtotime('-10 days'))]);

        $contextId = $this->importBatchContext->create('batch-clean', ['definition_names' => []]);

        $this->importBatch->purgeFinished(7);

        $this->dontSeeInDatabase('import_batches', ['id' => 'batch-clean']);
        $this->dontSeeInDatabase('import_batch_contexts', ['id' => $contextId]);
    }
}
