<?php

namespace Tests\Support;

use App\Models\ImportBatch;

trait QueueJobFixtureTrait
{
    protected function createBatch(string $id, string $type = 'items', int $total = 5): void
    {
        model(ImportBatch::class)->create($id, $type, $total);
    }

    protected function createBatchAt(string $id, string $status, int $total, int $completed = 0, int $failed = 0): void
    {
        $this->db->table('import_batches')->insert([
            'id'         => $id,
            'type'       => 'items',
            'total'      => $total,
            'completed'  => $completed,
            'failed'     => $failed,
            'status'     => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function createPendingJob(string $queue = 'default', ?string $batchId = null): int
    {
        return $this->insertQueueJobRow($queue, 0, $batchId);
    }

    protected function createReservedJob(string $queue = 'default', ?string $batchId = null): int
    {
        return $this->insertQueueJobRow($queue, 1, $batchId);
    }

    protected function createPausedJob(string $queue = 'imports', ?string $batchId = null): int
    {
        static $nextId = 1;

        $id  = $nextId++;
        $now = time();
        $this->db->table('queue_jobs_paused')->insert([
            'id'           => $id,
            'queue'        => $queue,
            'batch_id'     => $batchId,
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'normal',
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
            'paused_at'    => $now,
            'paused_by'    => 'manual',
        ]);

        return $id;
    }

    protected function getBatchRow(string $id): ?array
    {
        $row = $this->db->table('import_batches')->where('id', $id)->get()->getRowArray();

        return $row ?: null;
    }

    private function insertQueueJobRow(string $queue, int $status, ?string $batchId): int
    {
        $now = time();
        $this->db->table('queue_jobs')->insert([
            'queue'        => $queue,
            'batch_id'     => $batchId,
            'payload'      => json_encode(['job' => 'item_import', 'data' => []]),
            'priority'     => 'normal',
            'status'       => $status,
            'attempts'     => 0,
            'available_at' => $now,
            'created_at'   => $now,
        ]);

        return (int)$this->db->insertID();
    }
}
