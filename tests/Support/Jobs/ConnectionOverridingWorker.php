<?php

namespace Tests\Support\Jobs;

use App\Jobs\BoundedQueueWorker;
use CodeIgniter\Database\BaseConnection;

class ConnectionOverridingWorker extends BoundedQueueWorker
{
    public function __construct(array $queues, float $deadline, int $maxJobs, private readonly BaseConnection $overrideConnection)
    {
        parent::__construct($queues, $deadline, $maxJobs);
    }

    protected function connection(): BaseConnection
    {
        return $this->overrideConnection;
    }
}
