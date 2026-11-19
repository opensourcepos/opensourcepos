<?php

namespace Tests\Models;

use App\Models\QueueJobModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use RuntimeException;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\FailingCommitConnection;

class QueueJobModelTest extends CIUnitTestCase
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

        $this->db->table('queue_jobs')->truncate();
    }

    private function insertJob(int $status): int
    {
        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => 'default',
            'payload'      => json_encode(['job' => 'success', 'data' => []]),
            'priority'     => 'default',
            'status'       => $status,
            'attempts'     => 2,
            'available_at' => $now,
            'created_at'   => $now,
        ]);

        return (int)$this->db->insertID();
    }

    public function testReleaseReservedByIds_ReleasesOnlyReservedRowsAndKeepsAttempts(): void
    {
        $reservedId = $this->insertJob(1);
        $pendingId = $this->insertJob(0);

        $released = model(QueueJobModel::class)->releaseReservedByIds([$reservedId, $pendingId]);

        $this->assertSame(1, $released);
        $this->seeInDatabase('queue_jobs', ['id' => $reservedId, 'status' => 0, 'attempts' => 2]);
    }

    public function testReleaseReservedByIds_TreatsZeroAffectedRowsAsSuccess(): void
    {
        $pendingId = $this->insertJob(0);

        $this->assertSame(0, model(QueueJobModel::class)->releaseReservedByIds([$pendingId]));
    }

    public function testReleaseReservedByIds_RejectsAFalseUpdateResult(): void
    {
        $connection = FailingCommitConnection::create()->silenceQueryErrors();

        $brokenModel = new class ($connection) extends QueueJobModel {
            protected $table = 'table_that_does_not_exist';
        };

        $this->expectException(RuntimeException::class);
        $brokenModel->releaseReservedByIds([1]);
    }
}
