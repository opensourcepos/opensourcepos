<?php

namespace App\Jobs;

use CodeIgniter\Queue\BaseJob;
use Config\Jobs as JobsConfig;
use Config\OSPOS;

/**
 * Base class for core (non-plugin) queue jobs. Applies the app-wide
 * jobs_retry_limit setting (issue #3833 Phase 2) instead of CI4 Queue's
 * BaseJob default of a single try with no retry.
 *
 * Implements BatchableJob with a default maxBatchSize() of 1 (no batching).
 * Subclasses override maxBatchSize() to allow the worker to merge multiple
 * pending jobs of the same class into a single process() call.
 */
abstract class BaseOsposJob extends BaseJob implements BatchableJob
{
    public function getTries(): int
    {
        $appConfig = config(OSPOS::class)->settings;

        return (int)($appConfig['jobs_retry_limit'] ?? config(JobsConfig::class)->retryLimit);
    }

    public function maxBatchSize(): int
    {
        return 1;
    }
}
