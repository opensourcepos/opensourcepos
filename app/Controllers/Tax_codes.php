<?php

namespace App\Controllers;

use app\Models\Tax_code;

/**
 * @property tax_code tax_code
 */
class Tax_codes extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('tax_codes');
		
		$this->tax_code = model('Tax_code');
		helper('tax_helper');
	}


	public function index(): void
	{
		 echo view('taxes/tax_codes', $this->get_data());
	}

	public function get_data(): array
	{

		$data['table_headers'] = get_tax_code_table_headers();
		return $data;
	}

	/*
	 * Returns tax_category table data rows. This will be called with AJAX.
	 */
	public function search(): void
	{
		$search = $this->request->getGet('search', FILTER_SANITIZE_STRING);
		$limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getGet('sort', FILTER_SANITIZE_STRING);
		$order  = $this->request->getGet('order', FILTER_SANITIZE_STRING);

		$tax_codes = $this->tax_code->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->tax_code->get_found_rows($search);

		$data_rows = [];

		foreach($tax_codes->getResult() as $tax_code)
		{
			$data_rows[] = get_tax_code_data_row($tax_code);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	public function get_row(int $row_id): void
	{
		$data_row = get_tax_code_data_row($this->tax_code->get_info($row_id));

		echo json_encode($data_row);
	}

	public function view(int $tax_code_id = -1): void	//TODO: Need to replace -1 with constant
	{
		$data['tax_code_info'] = $this->tax_code->get_info($tax_code_id);

		echo view("taxes/tax_code_form", $data);
	}


	public function save(int $tax_code_id = -1): void		//TODO: Need to replace -1 with constant
	{
		$tax_code_data = [
			'tax_code' => $this->request->getPost('tax_code', FILTER_SANITIZE_STRING),
			'tax_code_name' => $this->request->getPost('tax_code_name', FILTER_SANITIZE_STRING),
			'city' => $this->request->getPost('city', FILTER_SANITIZE_STRING),
			'state' => $this->request->getPost('state', FILTER_SANITIZE_STRING)
		];

		if($this->tax_code->save($tax_code_data))
		{
			if($tax_code_id == -1)	//TODO: Need to replace -1 with constant
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Tax_codes.successful_adding'),
					'id' => $tax_code_data['tax_code_id']
				]);
			}
			else
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Tax_codes.successful_updating'),
					'id' => $tax_code_id
				]);
			}
		}
		else
		{
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Tax_codes.error_adding_updating') . ' ' . $tax_code_data['tax_code_id'],
				'id' => -1
			]);
		}
	}

	public function delete(): void
	{
		$tax_codes_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_NUMBER_INT);

		if($this->tax_code->delete_list($tax_codes_to_delete))
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Tax_codes.successful_deleted') . ' ' . count($tax_codes_to_delete) . ' ' . lang('Tax_codes.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Tax_codes.cannot_be_deleted')]);
		}
	}
}