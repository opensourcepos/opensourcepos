<?php

namespace Tests\Jobs;

use App\Libraries\ImportBatchService;
use App\Models\ImportBatch;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use RuntimeException;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\FailingCommitConnection;
use Tests\Support\Jobs\BatchProgressTestJob;

class ImportRowJobTest extends CIUnitTestCase
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

        BatchProgressTestJob::$rowSucceeds = true;
        BatchProgressTestJob::$onRow = null;
        $this->db->table('import_batches')->truncate();
    }

    protected function tearDown(): void
    {
        Services::reset();

        parent::tearDown();
    }

    public function testProcessBatchRow_CountsSuccessfulRowOnce(): void
    {
        $batchId = service('importBatch')->create('items', 1);

        $job = new BatchProgressTestJob(['batch_id' => $batchId]);

        $this->assertTrue($job->process());
        $this->seeInDatabase('import_batches', ['id' => $batchId, 'completed' => 1, 'status' => 'completed']);
    }

    public function testProcessBatchRow_DoesNotCountRejectedRow(): void
    {
        $batchId = service('importBatch')->create('items', 1);
        BatchProgressTestJob::$rowSucceeds = false;

        $job = new BatchProgressTestJob(['batch_id' => $batchId]);

        $this->assertFalse($job->process());
        $this->seeInDatabase('import_batches', ['id' => $batchId, 'completed' => 0, 'failed' => 0]);
    }

    public function testProcessBatchRow_SkipsAccountingWithoutBatchId(): void
    {
        $job = new BatchProgressTestJob(['row' => []]);

        $this->assertTrue($job->process());
    }

    public function testProcessBatchRow_PropagatesProgressFailure(): void
    {
        Services::injectMock('importBatch', new class () extends ImportBatchService {
            public function increment(string $batchId, bool $rowFailed): bool
            {
                throw new RuntimeException('progress update failed');
            }
        });

        $job = new BatchProgressTestJob(['batch_id' => 'any']);

        $this->expectException(RuntimeException::class);
        $job->process();
    }

    public function testProcessBatchRow_RollsBackRowAndProgressWhenCommitFails(): void
    {
        $this->db->table('import_batches')->insert([
            'id'         => 'row-commit-fail',
            'type'       => 'items',
            'total'      => 1,
            'completed'  => 0,
            'failed'     => 0,
            'status'     => 'processing',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $connection = FailingCommitConnection::create();
        Factories::injectMock('models', ImportBatch::class, new ImportBatch($connection));
        Services::reset();

        BatchProgressTestJob::$onRow = static function () use ($connection): void {
            $connection->table('import_batch_contexts')->insert([
                'batch_id'   => 'row-write',
                'context'    => '{}',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        };

        $job = new BatchProgressTestJob(['batch_id' => 'row-commit-fail']);

        try {
            $job->process();
            $this->fail('Expected the failed commit to be reported');
        } catch (RuntimeException) {
        } finally {
            Factories::reset('models');
        }

        $this->assertSame(0, $connection->transDepth);
        $this->dontSeeInDatabase('import_batch_contexts', ['batch_id' => 'row-write']);
        $this->seeInDatabase('import_batches', ['id' => 'row-commit-fail', 'completed' => 0]);

        $connection->failCommit = false;
        BatchProgressTestJob::$onRow = null;
        Factories::injectMock('models', ImportBatch::class, new ImportBatch($connection));
        Services::reset();

        $this->assertTrue((new BatchProgressTestJob(['batch_id' => 'row-commit-fail']))->process());
        $this->seeInDatabase('import_batches', ['id' => 'row-commit-fail', 'completed' => 1, 'status' => 'completed']);
        Factories::reset('models');
    }
}
