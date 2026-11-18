<?php

namespace App\Jobs;

use App\Libraries\Transaction;
use App\Models\ImportBatch;
use RuntimeException;
use Throwable;

/**
 * Base for queued CSV import row jobs. Saves the row and records its batch
 * progress in one transaction, so a failed progress update rolls the row back
 * and the retry cannot import it twice. A job whose payload carries no
 * batch_id (one divorced from its batch when it was paused) is processed
 * without any batch accounting.
 */
abstract class ImportRowJob extends BaseOsposJob
{
    /**
     * @param callable():bool $processRow Saves the row; returns false if the row was rejected.
     */
    protected function processBatchRow(callable $processRow): bool
    {
        $batchId = $this->data['batch_id'] ?? null;
        $db = model(ImportBatch::class)->db;

        $db->transBegin();

        try {
            $success = $processRow();

            if ($success && $batchId !== null) {
                service('importBatch')->increment($batchId, false);
            }
        } catch (Throwable $e) {
            $db->transRollback();
            $db->resetTransStatus();

            throw $e;
        }

        if (!$success) {
            $db->transRollback();
            $db->resetTransStatus();

            return false;
        }

        if (!Transaction::commit($db)) {
            throw new RuntimeException('Failed to commit import row.');
        }

        return true;
    }
}
