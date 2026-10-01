<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use Config\Database;
use Config\Jobs as JobsConfig;
use stdClass;

/**
 * Reads queue_jobs (pending/reserved) and queue_jobs_failed (exhausted
 * retries) as one virtual grid for the Jobs "Manage" tab. Both tables come
 * from codeigniter4/queue (see 20260916000000_AddJobsModule.php) and have
 * no shared row identity, so rows are tagged here with a synthetic
 * 'source' ('pending'|'reserved'|'failed') and 'uid' ("{source}:{id}")
 * used as the grid's uniqueId.
 */
class JobQueueManage
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * @param string[] $queues Queue names to include; empty means all core queues.
     */
    public function search(string $search, array $queues, int $limit = 0, int $limitFrom = 0, string $sort = 'date', string $order = 'desc', bool $countOnly = false): BaseResult|int
    {
        $unionSql = $this->buildUnionSql($search, $queues);

        if ($countOnly) {
            return (int)$this->db->query(
                'SELECT COUNT(*) AS count FROM (' . $unionSql . ') AS union_jobs'
            )->getRow()->count;
        }

        $sortable = ['status', 'queue', 'priority', 'attempts', 'date'];
        $sort = in_array($sort, $sortable, true) ? $sort : 'date';
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $sql = 'SELECT * FROM (' . $unionSql . ') AS union_jobs ORDER BY ' . $sort . ' ' . $order;

        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $limitFrom;
        }

        return $this->db->query($sql)->getResult();
    }

    public function getFoundRows(string $search, array $queues): int
    {
        return $this->search($search, $queues, 0, 0, 'date', 'desc', true);
    }

    /**
     * @param string[] $queues
     */
    private function buildUnionSql(string $search, array $queues): string
    {
        $coreQueues = config(JobsConfig::class)->coreQueues ?? [];
        $queues = array_values(array_intersect($queues === [] ? $coreQueues : $queues, $coreQueues));

        $pending = $this->db->table('queue_jobs')
            ->select("CONCAT(IF(status = 1, 'reserved', 'pending'), ':', id) AS uid", false)
            ->select("IF(status = 1, 'reserved', 'pending') AS source", false)
            ->select('queue, payload, priority, attempts, NULL AS exception, created_at AS date')
            ->whereIn('queue', $queues);

        $failed = $this->db->table('queue_jobs_failed')
            ->select("CONCAT('failed:', id) AS uid", false)
            ->select("'failed' AS source", false)
            ->select('queue, payload, priority, attempts, exception, failed_at AS date')
            ->whereIn('queue', $queues);

        if ($search !== '') {
            $pending->groupStart()
                ->like('queue', $search)
                ->orLike('payload', $search)
                ->groupEnd();
            $failed->groupStart()
                ->like('queue', $search)
                ->orLike('payload', $search)
                ->orLike('exception', $search)
                ->groupEnd();
        }

        return $pending->union($failed)->getCompiledSelect();
    }

    /**
     * Decodes the payload JSON for a job row fetched from the union query
     * (payload comes back as a raw text column, not auto-decoded).
     */
    public function decodePayload(stdClass $row): array
    {
        return json_decode($row->payload, true) ?? [];
    }
}
