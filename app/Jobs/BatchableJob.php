<?php

namespace App\Jobs;

/**
 * Opt-in interface for job classes that can process multiple pending rows in a
 * single execution. The worker will peek up to maxBatchSize()-1 additional
 * pending jobs of the same class and queue, merge their data arrays, and pass
 * the full set to process() as $this->data['_batch'] (array of data arrays).
 *
 * Jobs that do not override maxBatchSize() (or return 1) are executed one at
 * a time, the existing default.
 */
interface BatchableJob
{
    /**
     * Maximum number of pending jobs of this class the worker may merge into
     * a single process() call. Return 1 to disable batching (the default).
     */
    public function maxBatchSize(): int;
}
