<?php

namespace App\Jobs;

use App\Jobs\Support\ItemCsvRowProcessor;

/**
 * Queued handler for one row of an Items CSV import (issue #3833 Phase 3).
 * Pushed to the 'imports' queue, one job per CSV row, by
 * Items::postImportCsvFile(). Row-level failures do not affect other rows'
 * jobs — each row commits independently.
 */
class ItemImportJob extends BaseOsposJob
{
    public function process()
    {
        $batchId = $this->data['batch_id'];
        $row = $this->data['row'];
        $employeeId = $this->data['employee_id'];
        $definitionNames = $this->data['definition_names'];
        $attributeData = $this->data['attribute_data'];

        $processor = new ItemCsvRowProcessor();
        $success = $processor->process($row, $employeeId, $definitionNames, $attributeData);

        service('importBatch')->increment($batchId, !$success);
    }
}
