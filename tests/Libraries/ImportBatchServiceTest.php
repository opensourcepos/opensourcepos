<?php

namespace Tests\Libraries;

use App\Libraries\ImportBatchService;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class ImportBatchServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = false;
    protected $namespace = 'App';

    private static bool $doneBootstrap = false;

    protected ImportBatchService $importBatchService;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();

        $this->importBatchService = service('importBatch');

        $this->db->table('queue_jobs')->truncate();
        $this->db->table('import_batch_contexts')->where('id >', 0)->delete();
        $this->db->table('import_batches')->truncate();
    }

    public function testCreateWithoutContextStoresNoContextRow(): void
    {
        $batchId = $this->importBatchService->create('customers', 1);

        $this->seeInDatabase('import_batches', ['id' => $batchId, 'type' => 'customers']);
        $this->assertNull($this->importBatchService->getContext($batchId));
    }

    public function testCreateWithContextStoresRetrievableContext(): void
    {
        $context = ['definition_names' => ['1' => 'Color'], 'attribute_data' => []];

        $batchId = $this->importBatchService->create('items', 1, $context);

        $this->assertSame($context, $this->importBatchService->getContext($batchId));
    }

    public function testPushRowStampsBatchIdOnQueuedJob(): void
    {
        $batchId = $this->importBatchService->create('customers', 1);

        $this->importBatchService->pushRow('imports', 'customer_import', [
            'batch_id'    => $batchId,
            'row'         => ['First Name' => 'Jane'],
            'employee_id' => 1,
        ], $batchId, 'low');

        $this->seeInDatabase('queue_jobs', ['queue' => 'imports', 'batch_id' => $batchId]);
    }

    public function testPushRowStampsContextIdWhenBatchHasContext(): void
    {
        $batchId = $this->importBatchService->create('items', 1, ['definition_names' => []]);

        $this->importBatchService->pushRow('imports', 'item_import', [
            'batch_id'    => $batchId,
            'row'         => [],
            'employee_id' => 1,
        ], $batchId, 'low');

        $contextRow = $this->db->table('import_batch_contexts')->where('batch_id', $batchId)->get()->getRowArray();
        $this->seeInDatabase('queue_jobs', ['queue' => 'imports', 'batch_id' => $batchId, 'context_id' => $contextRow['id']]);
    }

    public function testPushRowLeavesContextIdNullWhenBatchHasNoContext(): void
    {
        $batchId = $this->importBatchService->create('customers', 1);

        $this->importBatchService->pushRow('imports', 'customer_import', [
            'batch_id'    => $batchId,
            'row'         => [],
            'employee_id' => 1,
        ], $batchId, 'low');

        $this->seeInDatabase('queue_jobs', ['queue' => 'imports', 'batch_id' => $batchId, 'context_id' => null]);
    }

    public function testIncrementReportsBatchCompletion(): void
    {
        $batchId = $this->importBatchService->create('customers', 1);

        $this->assertTrue($this->importBatchService->increment($batchId, false));
    }
}
