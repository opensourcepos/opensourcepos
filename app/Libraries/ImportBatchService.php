<?php

namespace App\Libraries;

use App\Models\ImportBatch;

/**
 * Thin wrapper around the ImportBatch model (issue #3833 Phase 3), exposed
 * as service('importBatch') so controllers pushing import jobs and the jobs
 * themselves share one call surface instead of reaching into the model
 * directly.
 */
class ImportBatchService
{
    private ImportBatch $importBatch;

    public function __construct()
    {
        $this->importBatch = model(ImportBatch::class);
    }

    /**
     * Creates a new batch and returns its generated id.
     */
    public function create(string $type, int $total): string
    {
        $batchId = uniqid('import_', true);

        $this->importBatch->create($batchId, $type, $total);

        return $batchId;
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
