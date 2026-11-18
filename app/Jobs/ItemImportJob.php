<?php

namespace App\Jobs;

use App\Jobs\Support\ItemCsvRowProcessor;

/**
 * Queued handler for one row of an Items CSV import (issue #3833 Phase 3).
 * Pushed to the 'imports' queue, one job per CSV row, by
 * Items::postImportCsvFile(). Row-level failures do not affect other rows'
 * jobs — each row commits independently.
 */
class ItemImportJob extends ImportRowJob
{
    public function process(): bool
    {
        $row = $this->data['row'];
        $employeeId = $this->data['employee_id'];
        $queueName = $this->data['queue_name'] ?? 'imports';

        $context = $this->data['context'] ?? service('importBatch')->getContext($this->data['batch_id']);
        $definitionNames = $context['definition_names'];
        $attributeData = $context['attribute_data'];

        return $this->processBatchRow(
            static fn (): bool => (new ItemCsvRowProcessor())->process($row, $employeeId, $definitionNames, $attributeData, $queueName)
        );
    }
}
