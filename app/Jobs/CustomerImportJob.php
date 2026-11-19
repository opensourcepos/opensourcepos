<?php

namespace App\Jobs;

use App\Jobs\Support\CustomerCsvRowProcessor;

/**
 * Queued handler for one row of a Customers CSV import (issue #3833 Phase 3).
 * Pushed to the 'imports' queue, one job per CSV row, by
 * Customers::postImportCsvFile(). Row-level failures do not affect other
 * rows' jobs — each row commits independently.
 */
class CustomerImportJob extends ImportRowJob
{
    public function process(): bool
    {
        $row = $this->data['row'];
        $employeeId = $this->data['employee_id'];
        $queueName = $this->data['queue_name'] ?? 'imports';

        return $this->processBatchRow(
            static fn (): bool => (new CustomerCsvRowProcessor())->process($row, $employeeId, $queueName)
        );
    }
}
