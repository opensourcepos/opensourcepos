<?php

namespace Tests\Database\Migrations;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class AddJobsModuleTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App';

    private const JOB_TABLES = ['queue_jobs', 'queue_jobs_failed', 'queue_jobs_paused', 'import_batches', 'import_batch_contexts', 'job_throttles'];

    private function assertJobsSchemaApplied(bool $sameBatch = true): void
    {
        foreach (self::JOB_TABLES as $table) {
            $this->assertTrue($this->db->tableExists($table), "$table should exist");
        }

        $history = $this->db->table('migrations')
            ->groupStart()
            ->like('class', 'AddJobsModule')
            ->orLike('class', 'CodeIgniter\\Queue\\Database', 'after', null, true)
            ->groupEnd()
            ->get()
            ->getResultArray();

        $this->assertCount(4, $history);
        if ($sameBatch) {
            $this->assertCount(1, array_unique(array_column($history, 'batch')));
        }
    }

    public function testDown_OneBatchRollbackThenMigrateAgainRestoresQueueSchemaAndHistory(): void
    {
        $this->assertJobsSchemaApplied();

        $this->regressDatabase();

        foreach (self::JOB_TABLES as $table) {
            $this->assertFalse($this->db->tableExists($table), "$table should be dropped");
        }
        $this->assertSame(0, $this->db->table('migrations')->like('class', 'AddJobsModule')->countAllResults());
        $this->assertSame(0, $this->db->table('migrations')->like('namespace', 'CodeIgniter\\Queue', 'after', null, true)->countAllResults());

        $this->migrateDatabase();

        $this->assertJobsSchemaApplied();
    }

    public function testDown_RollbackOfLaterBatchKeepsEarlierBatchesAndReapplies(): void
    {
        $this->db->table('migrations')
            ->whereIn('class', [
                'App\Database\Migrations\AddJobsModule',
                'CodeIgniter\Queue\Database\Migrations\AddQueueTables',
                'CodeIgniter\Queue\Database\Migrations\AddPriorityField',
                'CodeIgniter\Queue\Database\Migrations\ChangePayloadFieldTypeInSqlsrv',
            ])
            ->update(['batch' => 2]);


        $earlierRows = $this->db->table('migrations')->where('batch', 1)->countAllResults();
        $this->assertGreaterThan(0, $earlierRows);

        $this->migrations->setNamespace('App');
        $this->migrations->regress(-1, 'tests');

        foreach (self::JOB_TABLES as $table) {
            $this->assertFalse($this->db->tableExists($table), "$table should be dropped");
        }
        $this->assertSame($earlierRows, $this->db->table('migrations')->countAllResults());
        $this->assertTrue($this->db->tableExists('items'));

        $this->migrateDatabase();

        $this->assertJobsSchemaApplied(false);
    }

    public function testDown_VendorMigrationsInEarlierBatchStayAppliedAfterRollback(): void
    {
        $vendorClasses = [
            'CodeIgniter\\Queue\\Database\\Migrations\\AddQueueTables',
            'CodeIgniter\\Queue\\Database\\Migrations\\AddPriorityField',
            'CodeIgniter\\Queue\\Database\\Migrations\\ChangePayloadFieldTypeInSqlsrv',
        ];

        $this->db->table('migrations')->whereIn('class', ['App\\Database\\Migrations\\AddJobsModule'])->update(['batch' => 2]);

        $this->migrations->setNamespace('App');
        $this->migrations->regress(-1, 'tests');

        foreach (['queue_jobs_paused', 'import_batches', 'import_batch_contexts', 'job_throttles'] as $table) {
            $this->assertFalse($this->db->tableExists($table), "$table should be dropped");
        }

        $this->db->resetDataCache();

        $this->assertTrue($this->db->tableExists('queue_jobs'));
        $this->assertTrue($this->db->tableExists('queue_jobs_failed'));
        $this->assertFalse($this->db->fieldExists('batch_id', 'queue_jobs'));
        $this->assertFalse($this->db->fieldExists('context_id', 'queue_jobs_failed'));
        $this->assertSame(3, $this->db->table('migrations')->whereIn('class', $vendorClasses)->where('batch', 1)->countAllResults());

        $this->migrateDatabase();

        $this->assertJobsSchemaApplied(false);
        $this->assertSame(3, $this->db->table('migrations')->whereIn('class', $vendorClasses)->countAllResults());
    }
}
