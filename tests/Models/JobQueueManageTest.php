<?php

namespace Tests\Models;

use App\Models\JobQueueManage;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class JobQueueManageTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = false;
    protected $namespace = 'App';

    private static bool $doneBootstrap = false;

    protected JobQueueManage $model;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();

        $this->model = new JobQueueManage();

        $this->db->table('queue_jobs')->truncate();
        $this->db->table('queue_jobs_failed')->truncate();
        $this->db->table('queue_jobs_paused')->truncate();
    }

    private function insertPending(string $queue = 'default'): void
    {
        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => $queue,
            'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'     => 'normal',
            'status'       => 0,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);
    }

    private function insertReserved(string $queue = 'default'): void
    {
        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => $queue,
            'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'     => 'normal',
            'status'       => 1,
            'attempts'     => 1,
            'available_at' => $now,
            'created_at'   => $now,
        ]);
    }

    private function insertFailed(string $queue = 'default'): void
    {
        $now = time();
        $this->db->table('queue_jobs_failed')->insert([
            'connection' => 'database',
            'queue'      => $queue,
            'payload'    => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'   => 'normal',
            'attempts'   => 3,
            'exception'  => 'RuntimeException: test error',
            'failed_at'  => $now,
        ]);
    }

    private static int $pausedId = 1;

    private function insertPaused(string $queue = 'default'): void
    {
        $now = time();
        $this->db->table('queue_jobs_paused')->insert([
            'id'           => self::$pausedId++,
            'queue'        => $queue,
            'payload'      => json_encode(['job' => 'TestJob', 'data' => []]),
            'priority'     => 'normal',
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
            'paused_at'    => $now,
            'paused_by'    => 'manual',
        ]);
    }

    private function uids(array $rows): array
    {
        return array_map(fn($row) => $row->uid, $rows);
    }

    public function testSearch_NoStatusFilter_ReturnsAllSources(): void
    {
        $this->insertPending();
        $this->insertReserved();
        $this->insertFailed();
        $this->insertPaused();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc');

        $sources = array_map(fn($row) => $row->source, $rows);
        sort($sources);

        $this->assertContains('pending', $sources);
        $this->assertContains('reserved', $sources);
        $this->assertContains('failed', $sources);
        $this->assertContains('paused', $sources);
    }

    public function testSearch_FilterPending_ReturnsOnlyPendingRows(): void
    {
        $this->insertPending();
        $this->insertReserved();
        $this->insertFailed();
        $this->insertPaused();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc', false, ['pending']);

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertSame('pending', $row->source);
        }
    }

    public function testSearch_FilterReserved_ReturnsOnlyReservedRows(): void
    {
        $this->insertPending();
        $this->insertReserved();
        $this->insertFailed();
        $this->insertPaused();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc', false, ['reserved']);

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertSame('reserved', $row->source);
        }
    }

    public function testSearch_FilterPendingAndReserved_ExcludesPausedAndFailed(): void
    {
        $this->insertPending();
        $this->insertReserved();
        $this->insertFailed();
        $this->insertPaused();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc', false, ['pending', 'reserved']);

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertContains($row->source, ['pending', 'reserved']);
        }
    }

    public function testSearch_FilterFailed_ReturnsOnlyFailedRows(): void
    {
        $this->insertPending();
        $this->insertReserved();
        $this->insertFailed();
        $this->insertPaused();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc', false, ['failed']);

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertSame('failed', $row->source);
        }
    }

    public function testSearch_FilterPaused_ReturnsOnlyPausedRows(): void
    {
        $this->insertPending();
        $this->insertReserved();
        $this->insertFailed();
        $this->insertPaused();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc', false, ['paused']);

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertSame('paused', $row->source);
        }
    }

    public function testSearch_FilterFailedAndPaused_ExcludesPendingAndReserved(): void
    {
        $this->insertPending();
        $this->insertReserved();
        $this->insertFailed();
        $this->insertPaused();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc', false, ['failed', 'paused']);

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertContains($row->source, ['failed', 'paused']);
        }
    }

    public function testSearch_UnknownStatusValue_IsIgnoredAndAllSourcesReturned(): void
    {
        $this->insertPending();
        $this->insertFailed();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc', false, ['bogus', 'injection; DROP TABLE queue_jobs;']);

        $sources = array_map(fn($row) => $row->source, $rows);
        $this->assertContains('pending', $sources);
        $this->assertContains('failed', $sources);
    }

    public function testSearch_UnknownStatusMixedWithValid_OnlyValidApplied(): void
    {
        $this->insertPending();
        $this->insertReserved();
        $this->insertFailed();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc', false, ['bogus', 'pending']);

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertSame('pending', $row->source);
        }
    }

    public function testGetFoundRows_RespectsStatusFilter(): void
    {
        $this->insertPending();
        $this->insertPending();
        $this->insertFailed();

        $allCount = $this->model->getFoundRows('', []);
        $pendingCount = $this->model->getFoundRows('', [], ['pending']);
        $failedCount = $this->model->getFoundRows('', [], ['failed']);

        $this->assertSame(3, $allCount);
        $this->assertSame(2, $pendingCount);
        $this->assertSame(1, $failedCount);
    }

    public function testSearch_UidPrefix_MatchesSource(): void
    {
        $this->insertPending();
        $this->insertReserved();
        $this->insertFailed();

        $rows = $this->model->search('', [], 100, 0, 'date', 'desc');

        foreach ($rows as $row) {
            $this->assertStringStartsWith($row->source . ':', $row->uid);
        }
    }
}
