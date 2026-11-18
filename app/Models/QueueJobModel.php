<?php

namespace App\Models;

use CodeIgniter\Queue\Enums\Status;
use CodeIgniter\Queue\Models\QueueJobModel as BaseQueueJobModel;

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
     * Puts reserved jobs back to pending without touching their attempts or
     * availability. Only rows that are still reserved are changed.
     *
     * @param int[] $ids
     */
    public function releaseReservedByIds(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        $this->builder()
            ->whereIn('id', $ids)
            ->where('status', Status::RESERVED->value)
            ->update(['status' => Status::PENDING->value]);

        return $this->db->affectedRows();
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
