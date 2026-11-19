<?php

namespace App\Models;

use CodeIgniter\Queue\Models\QueueJobFailedModel;

class QueueJobFailedManageModel extends QueueJobFailedModel
{
    public function deleteByBatchId(string $batchId): bool
    {
        return $this->where('batch_id', $batchId)->delete();
    }
}
