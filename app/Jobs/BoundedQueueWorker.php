<?php

namespace App\Jobs;

use CodeIgniter\I18n\Time;
use CodeIgniter\Queue\Config\Queue as QueueConfig;
use CodeIgniter\Queue\Entities\QueueJob;
use CodeIgniter\Queue\Interfaces\QueueInterface;
use Config\Database;
use Config\Jobs as JobsConfig;
use Config\OSPOS;
use Config\Services;
use Throwable;

/**
 * In-process, deadline-bounded queue worker for the 'web', 'auto', and
 * 'manual' Job Queue trigger modes. Avoids depending on a long-running
 * CLI-style `queue:work` daemon (CodeIgniter\Queue\Commands\QueueWork),
 * which relies on CLI::write() and signal handling that don't apply inside
 * a web request or a synchronous admin action, and which would require a
 * second cron/Task Scheduler entry alongside `tasks:run` in 'auto' mode.
 *
 * Mirrors the shape of App\Filters\BoundedTaskRunner: drains jobs from the
 * given queues until they're empty, a deadline/job-count limit is hit, or
 * a configured job_throttles rate is exhausted (see JobThrottleGate), then
 * returns instead of sleeping and waiting for more.
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

    /**
     * Processes a single, already-fetched job (used by the Manage tab's
     * per-row play/requeue action, bypassing the normal pop-from-queue
     * loop in run()).
     */
    public function runOne(QueueJob $work): void
    {
        /** @var QueueInterface $queue */
        $queue = service('queue');
        /** @var QueueConfig $config */
        $config = config('Queue');

        $this->handle($queue, $config, $work);
    }

    /**
     * Moves an exhausted job into queue_jobs_failed and deletes it from
     * queue_jobs, same as CodeIgniter\Queue\Handlers\DatabaseHandler::failed()
     * / logFailed(), but also records $work->attempts, which the vendor
     * handler has no field for (QueueJobFailedModel's allowedFields doesn't
     * include it) and would otherwise drop.
     */
    private function moveToFailed(QueueJob $work, Throwable $err, bool $keepJob): void
    {
        if ($keepJob) {
            $exception = "Exception: {$err->getCode()} - {$err->getMessage()}" . PHP_EOL
                . "file: {$err->getFile()}:{$err->getLine()}";

            Database::connect()->table('queue_jobs_failed')->insert([
                'connection' => 'database',
                'queue'      => $work->queue,
                'payload'    => json_encode($work->payload),
                'priority'   => $work->priority,
                'attempts'   => $work->attempts,
                'exception'  => $exception,
                'failed_at'  => Time::now()->timestamp,
            ]);
        }

        Database::connect()->table('queue_jobs')->where('id', $work->id)->delete();
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
                $this->moveToFailed($work, $e, $config->keepFailedJobs);
                $this->failed++;

                if (isset($work->payload['data']['batch_id'])) {
                    service('importBatch')->increment($work->payload['data']['batch_id'], true);
                }
            }

            job_log($work->queue, 'error', $work->payload['job'] . ' job failed: ' . $e->getMessage());
        }
    }
}
