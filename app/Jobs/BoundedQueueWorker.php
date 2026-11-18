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
use RuntimeException;
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
     * include it) and would otherwise drop. The failed-row insert, the live-row
     * delete and the batch failure count share one transaction, so a partial
     * failure leaves the live job in place to be retried.
     *
     * @return bool False if nothing was committed and the job is still in queue_jobs.
     */
    private function moveToFailed(QueueJob $work, string $exception, bool $keepJob, ?string $batchId): bool
    {
        $db = Database::connect();

        $db->transBegin();

        try {
            if ($keepJob) {
                $sourceRow = $db->table('queue_jobs')
                    ->select('batch_id, context_id')
                    ->where('id', $work->id)
                    ->get()
                    ->getRowArray();

                $inserted = $db->table('queue_jobs_failed')->insert([
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

                if ($inserted === false) {
                    throw new RuntimeException('Failed to insert queue_jobs_failed row for job ' . $work->id);
                }
            }

            if ($db->table('queue_jobs')->where('id', $work->id)->delete() === false) {
                throw new RuntimeException('Failed to delete queue_jobs row ' . $work->id);
            }

            if ($batchId !== null) {
                service('importBatch')->increment($batchId, true);
            }
        } catch (Throwable $e) {
            $db->transRollback();
            $db->resetTransStatus();
            job_log($work->queue, 'error', 'Could not move job ' . $work->id . ' to failed: ' . $e->getMessage());

            return false;
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            $db->resetTransStatus();

            return false;
        }

        return $db->transCommit();
    }

    private function describeThrowable(Throwable $err): string
    {
        return "Exception: {$err->getCode()} - {$err->getMessage()}" . PHP_EOL
            . "file: {$err->getFile()}:{$err->getLine()}";
    }

    /**
     * Counts one attempt against $work and either schedules a retry (only
     * when $retryable and attempts remain) or moves it to queue_jobs_failed.
     * A job that cannot be moved is released back to the queue instead of
     * being left reserved or deleted.
     */
    private function failWork(QueueInterface $queue, QueueConfig $config, QueueJob $work, string $exception, bool $retryable, int $tries, int $retryAfter): void
    {
        $work->attempts++;

        if ($retryable && $work->attempts < $tries) {
            $queue->later($work, $retryAfter);

            return;
        }

        $batchId = $work->payload['data']['batch_id'] ?? null;

        if (!$this->moveToFailed($work, $exception, $config->keepFailedJobs, $batchId)) {
            $queue->later($work, $retryAfter);

            return;
        }

        $this->failed++;
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
        $peers = [];
        $job = null;

        try {
            $class = $config->resolveJobClass($work->payload['job']);
            $job = new $class($work->payload['data']);

            $this->popPeers($queue, $config, $work, $job, $peers);

            if ($peers !== []) {
                $batch = array_merge([$work->payload['data']], array_map(
                    static fn (QueueJob $p) => $p->payload['data'],
                    $peers
                ));
                $job = new $class(['_batch' => $batch]);
            }

            $result = $job->process();

            if ($result === false) {
                foreach ([$work, ...$peers] as $w) {
                    $this->failWork($queue, $config, $w, 'Job reported failure; see the job log for details.', false, $job->getTries(), $job->getRetryAfter());
                }

                job_log($work->queue, 'error', $work->payload['job'] . ' job reported failure');

                return;
            }

            foreach ([$work, ...$peers] as $w) {
                $queue->done($w);
            }
            $this->processed += 1 + count($peers);
            job_log($work->queue, 'info', $work->payload['job'] . ' completed (' . (1 + count($peers)) . ' job(s))');
        } catch (Throwable $e) {
            foreach ([$work, ...$peers] as $w) {
                $this->failWork(
                    $queue,
                    $config,
                    $w,
                    $this->describeThrowable($e),
                    $job !== null,
                    $job?->getTries() ?? 1,
                    $job?->getRetryAfter() ?? 60
                );
            }

            job_log($work->queue, 'error', $work->payload['job'] . ' job failed: ' . $e->getMessage());
        }
    }

    /**
     * If the job class implements BatchableJob with maxBatchSize() > 1, peeks
     * and pops up to maxBatchSize()-1 additional pending jobs of the same
     * class and queue to merge into a single process() call. Each reserved
     * peer is appended to $peers as soon as it is popped, so the caller can
     * still release it if a later pop throws.
     *
     * @param QueueJob[] $peers
     */
    private function popPeers(QueueInterface $queue, QueueConfig $config, QueueJob $work, object $job, array &$peers): void
    {
        if (!($job instanceof BatchableJob)) {
            return;
        }

        $batchSize = $job->maxBatchSize();

        if ($batchSize <= 1) {
            return;
        }

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
    }
}
