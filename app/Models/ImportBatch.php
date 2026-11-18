<?php

namespace App\Models;

use App\Libraries\Transaction;
use CodeIgniter\Model;
use RuntimeException;
use Throwable;

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
        $result = $this->insert([
            'id'     => $batchId,
            'type'   => $type,
            'total'  => $total,
            'status' => $total > 0 ? 'processing' : 'completed',
        ]);

        if ($result === false) {
            throw new RuntimeException('Failed to create import batch: ' . $batchId);
        }
    }

    /**
     * Atomically increments completed or failed for the batch and reports
     * whether this call finished it. Locks the row for the duration of the
     * transaction so concurrent workers incrementing the same batch can't
     * both observe completion.
     *
     * @return bool True only if this call moved the batch from unfinished to finished (completed + failed = total); false if it was already finished, is still open, or no longer exists.
     * @throws RuntimeException If the progress update could not be saved.
     */
    public function increment(string $batchId, bool $rowFailed): bool
    {
        $this->db->transBegin();

        try {
            $isComplete = $this->applyIncrement($batchId, $rowFailed);
        } catch (Throwable $e) {
            $this->rollbackProgress();

            throw $e;
        }

        if (!Transaction::commit($this->db)) {
            throw new RuntimeException('Failed to commit progress for import batch: ' . $batchId);
        }

        return $isComplete;
    }

    private function rollbackProgress(): void
    {
        $this->db->transRollback();

        if ($this->db->transDepth === 0) {
            $this->db->resetTransStatus();
        }
    }

    private function applyIncrement(string $batchId, bool $rowFailed): bool
    {
        $sql = $this->db->table($this->table)
            ->where('id', $batchId)
            ->getCompiledSelect();

        $result = $this->db->query($sql . ' FOR UPDATE');

        if ($result === false) {
            throw new RuntimeException('Failed to lock import batch: ' . $batchId);
        }

        $batch = $result->getRowArray();

        if ($batch === null) {
            return false;
        }

        $column = $rowFailed ? 'failed' : 'completed';

        $this->db->table($this->table)
            ->where('id', $batchId)
            ->set($column, $column . ' + 1', false)
            ->update();

        $completed = (int)$batch['completed'] + ($rowFailed ? 0 : 1);
        $failed = (int)$batch['failed'] + ($rowFailed ? 1 : 0);
        $total = (int)$batch['total'];
        $wasComplete = ((int)$batch['completed'] + (int)$batch['failed']) >= $total;
        $isComplete = !$wasComplete && ($completed + $failed) >= $total;

        if ($isComplete) {
            $this->db->table($this->table)
                ->where('id', $batchId)
                ->update([
                    'status'     => $failed > 0 ? 'partial' : 'completed',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        return $isComplete;
    }

    /**
     * Reverses one prior failure when a failed row is requeued, and reopens
     * the batch so the retry's outcome is counted exactly once.
     *
     * @return bool False if the batch does not exist.
     */
    public function releaseFailed(string $batchId): bool
    {
        $this->db->transStart();

        $sql = $this->db->table($this->table)
            ->where('id', $batchId)
            ->getCompiledSelect();

        $batch = $this->db->query($sql . ' FOR UPDATE')->getRowArray();

        if ($batch !== null) {
            $failed = max(0, (int)$batch['failed'] - 1);
            $isOpen = ((int)$batch['completed'] + $failed) < (int)$batch['total'];

            $update = ['failed' => $failed];

            if ($isOpen) {
                $update['status'] = 'processing';
            }

            $this->db->table($this->table)
                ->where('id', $batchId)
                ->update($update);
        }

        $this->db->transComplete();

        return $batch !== null;
    }

    /**
     * @return bool False if the batch does not exist.
     */
    public function adjustTotal(string $batchId, int $delta): bool
    {
        $this->db->transStart();

        $sql = $this->db->table($this->table)
            ->where('id', $batchId)
            ->getCompiledSelect();

        $batch = $this->db->query($sql . ' FOR UPDATE')->getRowArray();

        if ($batch !== null) {
            $newTotal = max(0, ($batch['total'] ?? 0) + $delta);

            $this->db->table($this->table)
                ->where('id', $batchId)
                ->update(['total' => $newTotal]);

            $completed = $batch['completed'] ?? 0;
            $failed    = $batch['failed'] ?? 0;

            if (($completed + $failed) >= $newTotal && $batch['status'] === 'processing') {
                $this->db->table($this->table)
                    ->where('id', $batchId)
                    ->update([
                        'status'     => $failed > 0 ? 'partial' : 'completed',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }
        }

        $this->db->transComplete();

        return $batch !== null;
    }

    /**
     * Deletes batches finished more than $retentionDays ago, but only once no
     * row referencing their batch_id remains in queue_jobs,
     * queue_jobs_failed, or queue_jobs_paused (e.g. a failed row kept alive by a longer failed-job
     * retention window). 0 means keep until manual purge.
     */
    public function purgeFinished(int $retentionDays): void
    {
        if ($retentionDays <= 0) {
            return;
        }

        $cutoff = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));

        $candidates = $this->db->table($this->table)
            ->select('id')
            ->whereIn('status', ['completed', 'partial'])
            ->where('updated_at <=', $cutoff)
            ->get()
            ->getResultArray();

        foreach ($candidates as $batch) {
            $batchId = $batch['id'];

            $stillReferenced = $this->db->table('queue_jobs')->where('batch_id', $batchId)->countAllResults() > 0
                || $this->db->table('queue_jobs_failed')->where('batch_id', $batchId)->countAllResults() > 0
                || $this->db->table('queue_jobs_paused')->where('batch_id', $batchId)->countAllResults() > 0;

            if (!$stillReferenced) {
                $this->db->table($this->table)->where('id', $batchId)->delete();
                model(ImportBatchContext::class)->deleteForBatch($batchId);
            }
        }
    }
}
