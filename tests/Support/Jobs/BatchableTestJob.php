<?php

namespace Tests\Support\Jobs;

use App\Jobs\BatchableJob;
use CodeIgniter\Queue\BaseJob;

/**
 * Test job that implements BatchableJob. Records the data it was called with
 * so assertions can verify batch accumulation happened.
 */
class BatchableTestJob extends BaseJob implements BatchableJob
{
    public static array $receivedData = [];

    public function maxBatchSize(): int
    {
        return 3;
    }

    public function process(): bool
    {
        self::$receivedData[] = $this->data;

        return true;
    }
}
