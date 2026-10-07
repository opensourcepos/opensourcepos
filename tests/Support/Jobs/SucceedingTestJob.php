<?php

namespace Tests\Support\Jobs;

use CodeIgniter\Queue\BaseJob;

class SucceedingTestJob extends BaseJob
{
    public function process(): bool
    {
        return true;
    }
}
