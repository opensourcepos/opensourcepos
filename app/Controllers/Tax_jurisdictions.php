<?php

namespace App\Controllers;

use App\Models\Tax_jurisdiction;

/**
 * @property tax_jurisdiction tax_jurisdiction
 */
class Tax_jurisdictions extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('tax_jurisdictions');

		$this->tax_jurisdiction = model('Tax_jurisdiction');

		helper('tax_helper');
	}


	/**
	 * @return void
	 */
	public function getIndex(): void
	{
		 $data['table_headers'] = get_tax_jurisdictions_table_headers();

		 echo view('taxes/tax_jurisdictions', $data);
	}

	/**
	 * Returns tax_category table data rows. This will be called with AJAX.
	 *
	 * @return void
	 */
	public function getSearch(): void
	{
		$search = $this->request->getGet('search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order  = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$tax_jurisdictions = $this->tax_jurisdiction->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->tax_jurisdiction->get_found_rows($search);

		$data_rows = [];
		foreach($tax_jurisdictions->getResult() as $tax_jurisdiction)
		{
			$data_rows[] = get_tax_jurisdictions_data_row($tax_jurisdiction);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	/**
	 * @param int $row_id
	 * @return void
	 */
	public function getRow(int $row_id): void
	{
		$data_row = get_tax_jurisdictions_data_row($this->tax_jurisdiction->get_info($row_id));

		echo json_encode($data_row);
	}

	/**
	 * @param int $tax_jurisdiction_id
	 * @return void
	 */
	public function getView(int $tax_jurisdiction_id = NEW_ENTRY): void
	{
		$data['tax_jurisdiction_info'] = $this->tax_jurisdiction->get_info($tax_jurisdiction_id);

		echo view("taxes/tax_jurisdiction_form", $data);
	}


	/**
	 * @param int $jurisdiction_id
	 * @return void
	 */
	public function postSave(int $jurisdiction_id = NEW_ENTRY): void
	{
		$tax_jurisdiction_data = [
			'jurisdiction_name' => $this->request->getPost('jurisdiction_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'reporting_authority' => $this->request->getPost('reporting_authority', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
		];

		if($this->tax_jurisdiction->save_value($tax_jurisdiction_data))
		{
			if($jurisdiction_id == NEW_ENTRY)
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Tax_jurisdictions.successful_adding'),
					'id' => $tax_jurisdiction_data['jurisdiction_id']
				]);
			}
			else
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Tax_jurisdictions.successful_updating'),
					'id' => $jurisdiction_id
				]);
			}
		}
		else
		{
			echo json_encode ([
				'success' => false,
				'message' => lang('Tax_jurisdictions.error_adding_updating') . ' ' . $tax_jurisdiction_data['jurisdiction_name'],
				'id' => NEW_ENTRY
			]);
		}
	}

	/**
	 * @return void
	 */
	public function postDelete(): void
	{
		$tax_jurisdictions_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_NUMBER_INT);

		if($this->tax_jurisdiction->delete_list($tax_jurisdictions_to_delete))
		{
			echo json_encode ([
				'success' => true,
				'message' => lang('Tax_jurisdictions.successful_deleted') . ' ' . count($tax_jurisdictions_to_delete) . ' ' . lang('Tax_jurisdictions.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => false, 'message' => lang('Tax_jurisdictions.cannot_be_deleted')]);
		}
	}
}
