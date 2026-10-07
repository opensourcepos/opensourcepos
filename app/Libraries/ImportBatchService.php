<?php

namespace App\Libraries;

use App\Models\ImportBatch;
use App\Models\ImportBatchContext;

/**
 * Thin wrapper around the ImportBatch/ImportBatchContext models (issue #3833
 * Phase 3), exposed as service('importBatch') so controllers pushing import
 * jobs and the jobs themselves share one call surface instead of reaching
 * into the models directly.
 */
class ImportBatchService
{
    private ImportBatch $importBatch;
    private ImportBatchContext $importBatchContext;

    public function __construct()
    {
        $this->importBatch = model(ImportBatch::class);
        $this->importBatchContext = model(ImportBatchContext::class);
    }

    /**
     * Creates a new batch and returns its generated id. $context, when given,
     * is stored once for the whole batch (e.g. attribute definitions for an
     * Items CSV import) instead of being duplicated into every row's job.
     */
    public function create(string $type, int $total, ?array $context = null): string
    {
        $batchId = uniqid('import_', true);

        $this->importBatch->create($batchId, $type, $total);

        if ($context !== null) {
            $this->importBatchContext->create($batchId, $context);
        }

        return $batchId;
    }

    /**
     * Fetches the batch-wide context snapshot stored at create(), decoded.
     */
    public function getContext(string $batchId): ?array
    {
        return $this->importBatchContext->getByBatchId($batchId);
    }

    /**
     * Pushes one row's job onto the queue and stamps the job with its
     * batch_id and context_id as real columns (the vendor handler only knows
     * about the JSON payload), so ImportBatch::purgeFinished() can cheaply
     * check for still-referenced jobs instead of scanning payload JSON, and
     * so the context_id foreign key protects import_batch_contexts rows.
     */
    public function pushRow(string $queue, string $job, array $data, string $batchId, string $priority): void
    {
        $result = service('queue')->setPriority($priority)->push($queue, $job, $data);

        db_connect()->table('queue_jobs')->where('id', $result->getJobId())->update([
            'batch_id'   => $batchId,
            'context_id' => $this->importBatchContext->getIdForBatch($batchId),
        ]);
    }

    /**
     * Atomically increments completed or failed for the batch.
     *
     * @return bool True if this call completed the batch.
     */
    public function increment(string $batchId, bool $rowFailed): bool
    {
        return $this->importBatch->increment($batchId, $rowFailed);
    }

    public function purgeFinished(int $retentionDays): void
    {
        $this->importBatch->purgeFinished($retentionDays);
    }
}
