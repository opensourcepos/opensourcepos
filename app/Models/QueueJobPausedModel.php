<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use Config\Database;

class QueueJobPausedModel extends Model
{
    protected $table            = 'queue_jobs_paused';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id', 'queue', 'batch_id', 'context_id', 'payload', 'priority',
        'attempts', 'available_at', 'created_at', 'paused_at', 'paused_by',
    ];

    protected $useTimestamps = false;
    protected $skipValidation  = true;
    protected $allowCallbacks  = false;

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        $this->DBGroup = config('Queue')->database['dbGroup'];

        $db ??= Database::connect($this->DBGroup);

        parent::__construct($db, $validation);
    }

    public function deleteByBatchId(string $batchId): bool
    {
        return $this->where('batch_id', $batchId)->delete();
    }

    public function getByBatchId(string $batchId): array
    {
        return $this->where('batch_id', $batchId)->asArray()->findAll();
    }

    /**
     * @param string[] $batchIds
     * @return string[] Subset of $batchIds that still have at least one paused job.
     */
    public function getBatchIdsWithPausedJobs(array $batchIds): array
    {
        if ($batchIds === []) {
            return [];
        }

        $rows = $this->select('batch_id')->distinct()->whereIn('batch_id', $batchIds)->asArray()->findAll();

        return array_column($rows, 'batch_id');
    }

    public function getByIdOrQueues(?int $id, ?array $queues): array
    {
        $query = $id !== null ? $this->where('id', $id) : $this->whereIn('queue', $queues);

        return $query->asArray()->findAll();
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
