<?php

namespace App\Jobs;

use App\Jobs\Support\CustomerCsvRowProcessor;

/**
 * Queued handler for one row of a Customers CSV import (issue #3833 Phase 3).
 * Pushed to the 'imports' queue, one job per CSV row, by
 * Customers::postImportCsvFile(). Row-level failures do not affect other
 * rows' jobs — each row commits independently.
 */
class CustomerImportJob extends BaseOsposJob
{
    public function process()
    {
        $batchId = $this->data['batch_id'];
        $row = $this->data['row'];
        $employeeId = $this->data['employee_id'];

        $processor = new CustomerCsvRowProcessor();
        $success = $processor->process($row, $employeeId);

        service('importBatch')->increment($batchId, !$success);
    }
}
