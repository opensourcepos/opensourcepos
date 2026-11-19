<?php

namespace App\Models;

use CodeIgniter\Queue\Enums\Status;
use CodeIgniter\Queue\Models\QueueJobModel as BaseQueueJobModel;
use RuntimeException;

class QueueJobModel extends BaseQueueJobModel
{
    public function deletePendingByBatchId(string $batchId): bool
    {
        return $this->where('status', Status::PENDING->value)
            ->where('batch_id', $batchId)
            ->delete();
    }

    /**
     * SELECT FOR UPDATE on pending jobs — must be called inside an open transaction.
     */
    public function getPendingForUpdateByBatchId(string $batchId): array
    {
        $sql = $this->builder()
            ->where('status', Status::PENDING->value)
            ->where('batch_id', $batchId)
            ->getCompiledSelect();

        return $this->db->query($sql . ' FOR UPDATE')->getResultArray();
    }

    /**
     * SELECT FOR UPDATE on pending jobs — must be called inside an open transaction.
     */
    public function getPendingForUpdateByIdOrQueues(?int $id, ?array $queues): array
    {
        $builder = $this->builder()->where('status', Status::PENDING->value);
        $builder = $id !== null ? $builder->where('id', $id) : $builder->whereIn('queue', $queues);

        return $this->db->query($builder->getCompiledSelect() . ' FOR UPDATE')->getResultArray();
    }

    public function claimJob(int $id): bool
    {
        $this->builder()
            ->where('id', $id)
            ->where('status', Status::PENDING->value)
            ->update(['status' => Status::RESERVED->value]);

        return $this->db->affectedRows() > 0;
    }

    /**
     * Puts stranded reservations back to pending without touching their
     * attempts or availability. A row is released only while it is still
     * reserved with the release_epoch it had when it was stranded, and
     * releasing increments that epoch. Nothing else writes it, so a later
     * reservation of the same row, however it came about, never matches a
     * stale fence. Zero affected rows is a success: the row was already
     * released, finished or reserved again by another worker.
     *
     * @param array<int, int> $fences Job id => release_epoch when stranded.
     * @throws RuntimeException If an update itself fails.
     */
    public function releaseStrandedJobs(array $fences): int
    {
        $released = 0;

        foreach ($fences as $id => $fence) {
            $updated = $this->builder()
                ->where('id', (int)$id)
                ->where('status', Status::RESERVED->value)
                ->where('release_epoch', (int)$fence)
                ->set('status', Status::PENDING->value)
                ->set('release_epoch', 'release_epoch + 1', false)
                ->update();

            if ($updated === false) {
                throw new RuntimeException('Failed to release reserved jobs');
            }

            $released += $this->db->affectedRows();
        }

        return $released;
    }

    public function insertRows(array $rows): bool
    {
        return $this->db->table($this->table)->insertBatch($rows);
    }

    public function deleteWhereInIds(array $ids): bool
    {
        return $this->db->table($this->table)->whereIn('id', $ids)->delete();
    }
}
