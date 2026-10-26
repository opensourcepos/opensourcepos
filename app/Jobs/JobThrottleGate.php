<?php

namespace App\Jobs;

use App\Models\JobThrottle;
use Config\Services;

/**
 * Gates queue draining against the throttle rows configured in
 * job_throttles (Jobs settings > Throttles). All active rows must pass
 * (AND) for a pop to be allowed, so e.g. a '10/minute' row and a
 * '1000/hour' row both act as simultaneous caps. Core throttles are
 * global across all queues (one shared bucket per row, not per-queue).
 *
 * Resolved via Config\Services::jobThrottleGate() rather than instantiated
 * directly, so a future plugin can override the service binding to layer
 * its own per-queue throttles on top of (or instead of) these core ones;
 * core throttles remain the fallback when no plugin override is bound.
 */
class JobThrottleGate
{
    private const PERIOD_SECONDS = [
        'second' => 1,
        'minute' => 60,
        'hour'   => 3600,
        'day'    => 86400,
        'month'  => 2_592_000,
    ];

    public function __construct(private readonly JobThrottle $jobThrottle)
    {
    }

    /**
     * Checks every active core throttle row. Consumes a token from each
     * row's bucket as a side effect, so this must only be called once per
     * job actually popped/attempted.
     */
    public function allows(): bool
    {
        $throttler = Services::throttler();
        $allowed = true;

        foreach ($this->jobThrottle->getAll()->getResultArray() as $throttle) {
            $maxCount = (int) $throttle['max_count'];

            if ($maxCount <= 0) {
                continue;
            }

            $seconds = self::PERIOD_SECONDS[$throttle['period']] ?? 60;
            $key = 'job_queue_core_' . $throttle['throttle_id'];

            // Check every row even after one fails, so each bucket's
            // refill timing stays accurate regardless of which row tripped.
            if (!$throttler->check($key, $maxCount, $seconds)) {
                $allowed = false;
            }
        }

        return $allowed;
    }
}
