<?php

namespace App\Models\Reports;

use CodeIgniter\HTTP\Response;
use CodeIgniter\Model;

/**
 *
 *
 * @property response response
 *
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
	public abstract function getDataColumns(): array;

	/**
	 * Returns all the data to be populated into the report
	 */
	public abstract function getData(array $inputs): array;

	/**
	 * Returns key=>value pairing of summary data for the report
	 */
	public abstract function getSummaryData(array $inputs): array;
}
