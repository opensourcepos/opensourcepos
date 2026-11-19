<?php

namespace Tests\Models;

use App\Models\ImportBatchContext;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class ImportBatchContextTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = false;
    protected $namespace = 'App';

    private static bool $doneBootstrap = false;

    protected ImportBatchContext $importBatchContext;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();

        $this->importBatchContext = model(ImportBatchContext::class);

        $this->db->table('queue_jobs')->where('context_id >', 0)->delete();
        $this->db->table('import_batch_contexts')->where('id >', 0)->delete();
    }

    public function testCreateStoresJsonEncodedContextAndReturnsId(): void
    {
        $context = ['definition_names' => ['1' => 'Color'], 'attribute_data' => []];

        $id = $this->importBatchContext->create('batch-a', $context);

        $this->assertGreaterThan(0, $id);
        $this->seeInDatabase('import_batch_contexts', ['id' => $id, 'batch_id' => 'batch-a']);
    }

    public function testGetByBatchIdReturnsDecodedContext(): void
    {
        $context = ['definition_names' => ['1' => 'Color'], 'attribute_data' => ['Color' => ['definition_type' => 'TEXT']]];
        $this->importBatchContext->create('batch-b', $context);

        $result = $this->importBatchContext->getByBatchId('batch-b');

        $this->assertSame($context, $result);
    }

    public function testGetByBatchIdReturnsNullWhenNoContextStored(): void
    {
        $this->assertNull($this->importBatchContext->getByBatchId('nonexistent-batch'));
    }

    public function testGetIdForBatchReturnsStoredId(): void
    {
        $id = $this->importBatchContext->create('batch-c', ['definition_names' => []]);

        $this->assertSame($id, $this->importBatchContext->getIdForBatch('batch-c'));
    }

    public function testGetIdForBatchReturnsNullWhenNoContextStored(): void
    {
        $this->assertNull($this->importBatchContext->getIdForBatch('nonexistent-batch'));
    }

    public function testDeleteForBatchRemovesRow(): void
    {
        $this->importBatchContext->create('batch-d', ['definition_names' => []]);

        $this->importBatchContext->deleteForBatch('batch-d');

        $this->dontSeeInDatabase('import_batch_contexts', ['batch_id' => 'batch-d']);
    }
}
