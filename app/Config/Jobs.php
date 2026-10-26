<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Jobs extends BaseConfig
{
    public string $mode = 'web';   // auto | web | manual
    public int $webMaxSeconds = 5;
    public int $taskMaxSeconds = 30;
    public int $manualMaxSeconds = 30;
    public int $failedRetentionDays = 30;
    public int $retentionDays = 7;
    public bool $autoPurge = true;
    public int $retryLimit = 3;

    /** Core queue names processed by BoundedQueueWorker. */
    public array $coreQueues = ['default', 'imports', 'api'];
}
