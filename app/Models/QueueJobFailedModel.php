<?php

namespace App\Models;

use CodeIgniter\Queue\Models\QueueJobFailedModel as BaseQueueJobFailedModel;

class QueueJobFailedModel extends BaseQueueJobFailedModel
{
    public function getForUpdateById(int $id): ?array
    {
        $sql = $this->builder()->where('id', $id)->getCompiledSelect();

        return $this->db->query($sql . ' FOR UPDATE')->getRowArray();
    }

    public function deleteByBatchId(string $batchId): bool
    {
        return $this->where('batch_id', $batchId)->delete();
    }
}
