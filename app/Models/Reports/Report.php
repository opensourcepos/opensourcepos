<?php

namespace App\Models\Reports;

use CodeIgniter\HTTP\Response;
use App\Models\BaseModel;

abstract class Report extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public abstract function getDataColumns(): array;

    public abstract function getData(array $inputs): array;

    public abstract function getSummaryData(array $inputs): array;
}
