<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Tracks progress of a queued CSV import (issue #3833 Phase 3). Each row of
 * an import is pushed as an independent job, so no single job knows it is
 * the last one — increment() resolves this with an atomic counter, and only
 * the job that pushes the batch to totals returns true from isComplete() so
 * 'import_completed' fires exactly once.
 */
class ImportBatch extends Model
{
    protected $table = 'import_batches';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id',
        'type',
        'total',
        'completed',
        'failed',
        'status',
    ];

    public function create(string $batchId, string $type, int $total): void
    {
        $this->insert([
            'id'     => $batchId,
            'type'   => $type,
            'total'  => $total,
            'status' => $total > 0 ? 'processing' : 'completed',
        ]);
    }

    /**
     * Atomically increments completed or failed for the batch and reports
     * whether this call finished it. Locks the row for the duration of the
     * transaction so concurrent workers incrementing the same batch can't
     * both observe completion.
     *
     * @return bool True if this call completed the batch (completed + failed = total).
     */
    public function increment(string $batchId, bool $rowFailed): bool
    {
        $this->db->transStart();

        $batch = $this->db->query(
            'SELECT * FROM ' . $this->db->protectIdentifiers($this->table) . ' WHERE id = ? FOR UPDATE',
            [$batchId]
        )->getRowArray();

        $column = $rowFailed ? 'failed' : 'completed';

        $this->db->table($this->table)
            ->where('id', $batchId)
            ->set($column, $column . ' + 1', false)
            ->update();

        $completed = ($batch['completed'] ?? 0) + ($rowFailed ? 0 : 1);
        $failed = ($batch['failed'] ?? 0) + ($rowFailed ? 1 : 0);
        $total = $batch['total'] ?? 0;
        $isComplete = ($completed + $failed) >= $total;

        if ($isComplete) {
            $this->db->table($this->table)
                ->where('id', $batchId)
                ->update(['status' => $failed > 0 ? 'partial' : 'completed']);
        }

        $this->db->transComplete();

        return $isComplete;
    }

    /**
     * Deletes batches finished more than $retentionDays ago. 0 means keep
     * until manual purge.
     */
    public function purgeFinished(int $retentionDays): void
    {
        if ($retentionDays <= 0) {
            return;
        }

        $this->db->table($this->table)
            ->whereIn('status', ['completed', 'partial'])
            ->where('updated_at <=', date('Y-m-d H:i:s', strtotime("-{$retentionDays} days")))
            ->delete();
    }
}
