<?php

namespace App\Models\Reports;

use CodeIgniter\HTTP\Response;
use CodeIgniter\Model;

/**
 * @property Response response
 */
abstract class Report extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the column names used for the report
     */
    abstract public function getDataColumns(): array;

    /**
     * Returns all the data to be populated into the report
     */
    abstract public function getData(array $inputs): array;

    /**
     * Returns key=>value pairing of summary data for the report
     */
    abstract public function getSummaryData(array $inputs): array;
}
