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

            $work = $this->popNext($queue, $config);

            if ($work === null) {
                break;
            }

            if (!$throttleGate->allows($work->queue)) {
                $sleepSec = $throttleGate->getSleepSeconds($work->queue);
                $queue->later($work, $sleepSec);

                if (microtime(true) + $sleepSec >= $this->deadline) {
                    break;
                }

                sleep($sleepSec);
                continue;
            }

            $this->handle($queue, $config, $work);
        }

        $appConfig = config(OSPOS::class)->settings;
        $jobsConfig = config(JobsConfig::class);
        $autoPurge = (bool)($appConfig['jobs_auto_purge'] ?? $jobsConfig->autoPurge);

        if ($autoPurge) {
            $failedRetentionDays = (int)($appConfig['jobs_failed_retention_days'] ?? $jobsConfig->failedRetentionDays);
            $retentionDays = (int)($appConfig['jobs_retention_days'] ?? $jobsConfig->retentionDays);

            if ($failedRetentionDays > 0) {
                $queue->flush($failedRetentionDays * 24, null);
            }
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

            $sourceRow = Database::connect()->table('queue_jobs')
                ->select('batch_id, context_id')
                ->where('id', $work->id)
                ->get()
                ->getRowArray();

            Database::connect()->table('queue_jobs_failed')->insert([
                'connection' => 'database',
                'queue'      => $work->queue,
                'batch_id'   => $sourceRow['batch_id'] ?? null,
                'context_id' => $sourceRow['context_id'] ?? null,
                'payload'    => json_encode($work->payload),
                'priority'   => $work->priority,
                'attempts'   => $work->attempts,
                'exception'  => $exception,
                'failed_at'  => Time::now()->timestamp,
            ]);
        }

        Database::connect()->table('queue_jobs')->where('id', $work->id)->delete();
    }

    private function popNext(QueueInterface $queue, QueueConfig $config): ?QueueJob
    {
        foreach ($this->queues as $queueName) {
            $priorities = $config->queuePriorities[$queueName] ?? ['default'];
            $work = $queue->pop($queueName, $priorities);

            if ($work !== null) {
                return $work;
            }
        }

        return null;
    }

    private function handle(QueueInterface $queue, QueueConfig $config, QueueJob $work): void
    {
        $class = $config->resolveJobClass($work->payload['job']);
        $job = new $class($work->payload['data']);

        $peers = $this->popPeers($queue, $config, $work, $job);

        if ($peers !== []) {
            $batch = array_merge([$work->payload['data']], array_map(
                static fn (QueueJob $p) => $p->payload['data'],
                $peers
            ));
            $job = new $class(['_batch' => $batch]);
        }

        $allWorks = [$work, ...$peers];

        try {
            $job->process();

            foreach ($allWorks as $w) {
                $queue->done($w);
            }
            $this->processed += count($allWorks);
        } catch (Throwable $e) {
            foreach ($allWorks as $w) {
                $w->attempts++;

                if ($w->attempts < $job->getTries()) {
                    $queue->later($w, $job->getRetryAfter());
                } else {
                    $this->moveToFailed($w, $e, $config->keepFailedJobs);
                    $this->failed++;

                    if (isset($w->payload['data']['batch_id'])) {
                        service('importBatch')->increment($w->payload['data']['batch_id'], true);
                    }
                }
            }

            job_log($work->queue, 'error', $work->payload['job'] . ' job failed: ' . $e->getMessage());
        }
    }

    /**
     * If the job class implements BatchableJob with maxBatchSize() > 1, peeks
     * and pops up to maxBatchSize()-1 additional pending jobs of the same
     * class and queue to merge into a single process() call.
     *
     * @return QueueJob[]
     */
    private function popPeers(QueueInterface $queue, QueueConfig $config, QueueJob $work, object $job): array
    {
        if (!($job instanceof BatchableJob)) {
            return [];
        }

        $batchSize = $job->maxBatchSize();

        if ($batchSize <= 1) {
            return [];
        }

        $peers = [];
        $jobClass = $work->payload['job'];
        $priorities = $config->queuePriorities[$work->queue] ?? ['default'];

        for ($i = 1; $i < $batchSize; $i++) {
            $candidate = $queue->pop($work->queue, $priorities);

            if ($candidate === null) {
                break;
            }

            if ($candidate->payload['job'] !== $jobClass) {
                $queue->later($candidate, 0);
                break;
            }

            $peers[] = $candidate;
        }

        return $peers;
    }
}
