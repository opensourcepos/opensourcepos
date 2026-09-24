<?php

namespace App\Jobs;

use CodeIgniter\Queue\Config\Queue as QueueConfig;
use CodeIgniter\Queue\Entities\QueueJob;
use CodeIgniter\Queue\Interfaces\QueueInterface;
use Config\Jobs as JobsConfig;
use Config\OSPOS;
use Config\Services;
use Throwable;

/**
 * In-process, deadline-bounded queue worker for the 'web' and 'manual' Job
 * Queue trigger modes, where a long-running CLI-style `queue:work` daemon
 * (CodeIgniter\Queue\Commands\QueueWork) cannot run — that command relies on
 * CLI::write() and signal handling that don't apply inside a web request or
 * a synchronous admin action.
 *
 * Mirrors the shape of App\Filters\BoundedTaskRunner: drains jobs from the
 * given queues until they're empty, a deadline/job-count limit is hit, or
 * a configured job_throttles rate is exhausted (see JobThrottleGate), then
 * returns instead of sleeping and waiting for more (unlike the real
 * queue:work daemon, which is only appropriate for 'auto' mode under
 * cron/supervisor).
 */
class BoundedQueueWorker
{
    private int $processed = 0;
    private int $failed = 0;

    /**
     * @param string[] $queues
     */
    public function __construct(
        private readonly array $queues,
        private readonly float $deadline,
        private readonly int $maxJobs = 0,
    ) {
    }

    public function run(): void
    {
        /** @var QueueInterface $queue */
        $queue = service('queue');
        /** @var QueueConfig $config */
        $config = config('Queue');

        $throttleGate = Services::jobThrottleGate();

        while (microtime(true) < $this->deadline) {
            if ($this->maxJobs > 0 && $this->processed + $this->failed >= $this->maxJobs) {
                break;
            }

            if (!$throttleGate->allows()) {
                break;
            }

            $work = $this->popNext($queue);

            if ($work === null) {
                break;
            }

            $this->handle($queue, $config, $work);
        }

        $appConfig = config(OSPOS::class)->settings;
        $jobsConfig = config(JobsConfig::class);
        $autoPurge = (bool)($appConfig['jobs_auto_purge'] ?? $jobsConfig->autoPurge);

        if ($autoPurge) {
            $failedRetentionDays = (int)($appConfig['jobs_failed_retention_days'] ?? $jobsConfig->failedRetentionDays);
            $retentionDays = (int)($appConfig['jobs_retention_days'] ?? $jobsConfig->retentionDays);

            $queue->flush($failedRetentionDays * 24, null);
            service('importBatch')->purgeFinished($retentionDays);
        }
    }

    public function getProcessedCount(): int
    {
        return $this->processed;
    }

    public function getFailedCount(): int
    {
        return $this->failed;
    }

    private function popNext(QueueInterface $queue): ?QueueJob
    {
        foreach ($this->queues as $queueName) {
            $work = $queue->pop($queueName, ['high', 'normal', 'low']);

            if ($work !== null) {
                return $work;
            }
        }

        return null;
    }

    private function handle(QueueInterface $queue, QueueConfig $config, QueueJob $work): void
    {
        try {
            $class = $config->resolveJobClass($work->payload['job']);
            $job = new $class($work->payload['data']);
            $job->process();

            $queue->done($work);
            $this->processed++;
        } catch (Throwable $e) {
            $work->attempts++;

            if (isset($job) && $work->attempts < $job->getTries()) {
                $queue->later($work, $job->getRetryAfter());
            } else {
                $queue->failed($work, $e, $config->keepFailedJobs);
                $this->failed++;

                if (isset($work->payload['data']['batch_id'])) {
                    service('importBatch')->increment($work->payload['data']['batch_id'], true);
                }
            }

            log_message('error', 'BoundedQueueWorker: job failed: ' . $e->getMessage());
        }
    }
}
