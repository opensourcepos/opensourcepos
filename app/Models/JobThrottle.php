<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;

/**
 * JobThrottle class
 */
class JobThrottle extends Model
{
    protected $table = 'job_throttles';
    protected $primaryKey = 'throttle_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'max_count',
        'period',
        'deleted'
    ];

    /**
     * @param int $throttleId
     * @return bool
     */
    public function exists(int $throttleId): bool
    {
        $builder = $this->db->table('job_throttles');
        $builder->where('throttle_id', $throttleId);

        return ($builder->get()->getNumRows() >= 1);
    }

    /**
     * @param array $throttleData
     * @param int $throttleId
     * @return int Returns the throttle_id of the saved row (new id if inserted)
     */
    public function saveValue(array $throttleData, int $throttleId): int
    {
        $throttleDataToSave = [
            'max_count' => $throttleData['max_count'],
            'period'    => $throttleData['period'],
            'deleted'   => 0
        ];

        if (!$this->exists($throttleId)) {
            $builder = $this->db->table('job_throttles');
            $builder->insert($throttleDataToSave);

            return (int)$this->db->insertID();
        }

        $builder = $this->db->table('job_throttles');
        $builder->where('throttle_id', $throttleId);
        $builder->update($throttleDataToSave);

        return $throttleId;
    }

    /**
     * @return ResultInterface
     */
    public function getAll(): ResultInterface
    {
        $builder = $this->db->table('job_throttles');
        $builder->where('deleted', 0);

        return $builder->get();
    }

    /**
     * Deletes one throttle
     */
    public function delete($throttleId = null, bool $purge = false): bool
    {
        $builder = $this->db->table('job_throttles');
        $builder->where('throttle_id', $throttleId);

        return $builder->update(['deleted' => 1]);
    }
}
