<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Stores a one-time-per-batch context snapshot (e.g. attribute definitions
 * for an Items CSV import), so row-level jobs read it once by batch_id
 * instead of carrying a duplicate copy in every row's queued job payload.
 * queue_jobs/queue_jobs_failed/queue_paused_jobs all FK their context_id
 * (ON DELETE RESTRICT) here, so a row can only be deleted once no job
 * anywhere still references it.
 */
class ImportBatchContext extends Model
{
    protected $table = 'import_batch_contexts';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'batch_id',
        'context',
        'created_at',
    ];

    public function create(string $batchId, array $context): int
    {
        $this->insert([
            'batch_id'   => $batchId,
            'context'    => json_encode($context),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->getInsertID();
    }

    public function getByBatchId(string $batchId): ?array
    {
        $row = $this->where('batch_id', $batchId)->first();

        return $row === null ? null : json_decode($row['context'], true);
    }

    public function getIdForBatch(string $batchId): ?int
    {
        $row = $this->where('batch_id', $batchId)->select('id')->first();

        return $row === null ? null : (int)$row['id'];
    }

    public function deleteForBatch(string $batchId): void
    {
        $this->where('batch_id', $batchId)->delete();
    }
}
