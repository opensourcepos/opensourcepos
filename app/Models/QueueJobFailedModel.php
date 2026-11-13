<?php

namespace App\Models;

use CodeIgniter\Queue\Models\QueueJobFailedModel as BaseQueueJobFailedModel;

class QueueJobFailedModel extends BaseQueueJobFailedModel
{
    public function deleteByBatchId(string $batchId): bool
    {
        return $this->where('batch_id', $batchId)->delete();
    }
}
