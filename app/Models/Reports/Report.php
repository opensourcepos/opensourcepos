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
	function __construct()
	{
		parent::__construct();

		//Make sure the report is not cached by the browser
		$this->response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
		$this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
		$this->response->setHeader('Cache-Control', 'post-check=0, pre-check=0');
		$this->response->setHeader('Pragma', 'no-cache');
	}

	// Returns the column names used for the report
	public abstract function getDataColumns(): array;

	// Returns all the data to be populated into the report
	public abstract function getData(array $inputs): array;

	// Returns key=>value pairing of summary data for the report
	public abstract function getSummaryData(array $inputs): array;
}
