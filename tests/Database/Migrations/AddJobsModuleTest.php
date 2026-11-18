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

    private function assertJobsSchemaApplied(): void
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
        $this->assertCount(1, array_unique(array_column($history, 'batch')));
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
}
