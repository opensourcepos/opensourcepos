<?php

namespace App\Controllers;

use app\Models\Tax_category;

/**
 * @property tax_category tax_category
 */
class Tax_categories extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('tax_categories');
		
		$this->tax_category = model('Tax_category');
	}

	public function index(): void
	{
		 $data['tax_categories_table_headers'] = get_tax_categories_table_headers();

		 echo view('taxes/tax_categories', $data);
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

		$tax_categories = $this->tax_category->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->tax_category->get_found_rows($search);

		$data_rows = [];
		foreach($tax_categories->getResult() as $tax_category)
		{
			$data_rows[] = get_tax_categories_data_row($tax_category);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	public function get_row($row_id): void
	{
		$data_row = get_tax_categories_data_row($this->tax_category->get_info($row_id));

		echo json_encode($data_row);
	}

	public function view(int $tax_category_id = -1): void	//TODO: Need to replace -1 with constant
	{
		$data['tax_category_info'] = $this->tax_category->get_info($tax_category_id);

		echo view("taxes/tax_category_form", $data);
	}


	public function save(int $tax_category_id = -1): void	//TODO: Need to replace -1 with constant
	{
		$tax_category_data = [
			'tax_category' => $this->request->getPost('tax_category', FILTER_SANITIZE_STRING),
			'tax_category_code' => $this->request->getPost('tax_category_code', FILTER_SANITIZE_STRING),
			'tax_group_sequence' => $this->request->getPost('tax_group_sequence', FILTER_SANITIZE_NUMBER_INT)
		];

		if($this->tax_category->save_value($tax_category_data, $tax_category_id))
		{
			// New tax_category_id
			if($tax_category_id == -1)	//TODO: Need to replace -1 with constant
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Tax_categories.successful_adding'),
					'id' => $tax_category_data['tax_category_id']
				]);
			}
			else
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Tax_categories.successful_updating'),
					'id' => $tax_category_id
				]);
			}
		}
		else
		{
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Tax_categories.error_adding_updating') . ' ' . $tax_category_data['tax_category'],
				'id' => -1	//TODO: Need to replace -1 with constant
			]);
		}
	}

	public function delete(): void
	{
		$tax_categories_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_NUMBER_INT);

		if($this->tax_category->delete_list($tax_categories_to_delete))
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Tax_categories.successful_deleted') . ' ' . count($tax_categories_to_delete) . ' ' . lang('Tax_categories.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Tax_categories.cannot_be_deleted')]);
		}
	}
}