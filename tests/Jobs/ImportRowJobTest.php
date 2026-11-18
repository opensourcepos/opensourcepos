<?php

namespace Tests\Jobs;

use App\Libraries\ImportBatchService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use RuntimeException;
use Tests\Support\DatabaseBootstrapTrait;
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
}
