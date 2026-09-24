<?php

namespace Tests\Support\Jobs;

use CodeIgniter\Queue\BaseJob;
use Exception;

class FailingTestJob extends BaseJob
{
    protected int $retryAfter = 60;
    protected int $tries = 2;

    /**
     * @throws Exception
     */
    public function process(): never
    {
        throw new Exception('Intentional test failure');
    }
}
