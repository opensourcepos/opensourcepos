<?php

namespace Tests\Support\Jobs;

use CodeIgniter\Queue\BaseJob;

class ReportingFailureTestJob extends BaseJob
{
    protected int $retryAfter = 60;
    protected int $tries = 3;

    public function process(): bool
    {
        return false;
    }
}
