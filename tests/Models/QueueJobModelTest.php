<?php

namespace Tests\Models;

use App\Jobs\UnreleasedJobRegistry;
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

    private function insertJob(int $status, int $releaseEpoch = 0): int
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
            'release_epoch' => $releaseEpoch,
        ]);

        return (int)$this->db->insertID();
    }

    public function testReleaseStrandedJobs_ReleasesOnlyMatchingReservedRowsAndKeepsAttempts(): void
    {
        $reservedId = $this->insertJob(1);
        $pendingId = $this->insertJob(0);

        $released = model(QueueJobModel::class)->releaseStrandedJobs([$reservedId => 0, $pendingId => 0]);

        $this->assertSame(1, $released);
        $this->seeInDatabase('queue_jobs', ['id' => $reservedId, 'status' => 0, 'attempts' => 2, 'release_epoch' => 1]);
    }

    public function testReleaseStrandedJobs_TreatsZeroAffectedRowsAsSuccess(): void
    {
        $pendingId = $this->insertJob(0);

        $this->assertSame(0, model(QueueJobModel::class)->releaseStrandedJobs([$pendingId => 0]));
    }

    public function testReleaseStrandedJobs_RejectsAFalseUpdateResult(): void
    {
        $connection = FailingCommitConnection::create()->silenceQueryErrors();

        $brokenModel = new class ($connection) extends QueueJobModel {
            protected $table = 'table_that_does_not_exist';
        };

        $this->expectException(RuntimeException::class);
        $brokenModel->releaseStrandedJobs([1 => 0]);
    }

    public function testReleaseStrandedJobs_LeavesAReservationTakenAfterTheRelease(): void
    {
        $model = model(QueueJobModel::class);
        $id = $this->insertJob(1);

        $this->assertSame(1, $model->releaseStrandedJobs([$id => 0]));
        $this->assertTrue($model->claimJob($id));

        $this->assertSame(0, $model->releaseStrandedJobs([$id => 0]));
        $this->seeInDatabase('queue_jobs', ['id' => $id, 'status' => 1, 'release_epoch' => 1]);
    }

    public function testReleaseStrandedJobs_IgnoresAClaimantResumingAfterItsLeaseExpired(): void
    {
        $model = model(QueueJobModel::class);
        $registryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ospos_unreleased_' . uniqid('', true) . '.json';
        $registry = new UnreleasedJobRegistry($registryPath, 300);
        $id = $this->insertJob(1);
        $registry->record([$id => 0]);

        $stalledClaim = $registry->claim('stalled-worker', 5000);
        $takeoverClaim = $registry->claim('takeover-worker', 5300);

        $this->assertSame([$id => 0], $takeoverClaim);
        $this->assertSame(1, $model->releaseStrandedJobs($takeoverClaim));
        $registry->complete('takeover-worker');
        $this->assertTrue($model->claimJob($id));

        $this->assertSame(0, $model->releaseStrandedJobs($stalledClaim));
        $this->seeInDatabase('queue_jobs', ['id' => $id, 'status' => 1]);

        @unlink($registryPath);
    }
}
