<?php

namespace App\Controllers;

use App\Models\Tax_category;

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

	public function getIndex(): void
	{
		 $data['tax_categories_table_headers'] = get_tax_categories_table_headers();

		 echo view('taxes/tax_categories', $data);
	}

	/*
	 * Returns tax_category table data rows. This will be called with AJAX.
	*/
	public function getSearch(): void
	{
		$search = $this->request->getGet('search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order  = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$tax_categories = $this->tax_category->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->tax_category->get_found_rows($search);

		$data_rows = [];
		foreach($tax_categories->getResult() as $tax_category)
		{
			$data_rows[] = get_tax_categories_data_row($tax_category);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	public function getRow($row_id): void
	{
		$data_row = get_tax_categories_data_row($this->tax_category->get_info($row_id));

		echo json_encode($data_row);
	}

	public function getView(int $tax_category_id = NEW_ENTRY): void
	{
		$data['tax_category_info'] = $this->tax_category->get_info($tax_category_id);

		echo view("taxes/tax_category_form", $data);
	}


	public function postSave(int $tax_category_id = NEW_ENTRY): void
	{
		$tax_category_data = [
			'tax_category' => $this->request->getPost('tax_category', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'tax_category_code' => $this->request->getPost('tax_category_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'tax_group_sequence' => $this->request->getPost('tax_group_sequence', FILTER_SANITIZE_NUMBER_INT)
		];

		if($this->tax_category->save_value($tax_category_data, $tax_category_id))
		{
			// New tax_category_id
			if($tax_category_id == NEW_ENTRY)
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Tax_categories.successful_adding'),
					'id' => $tax_category_data['tax_category_id']
				]);
			}
			else
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Tax_categories.successful_updating'),
					'id' => $tax_category_id
				]);
			}
		}
		else
		{
			echo json_encode ([
				'success' => false,
				'message' => lang('Tax_categories.error_adding_updating') . ' ' . $tax_category_data['tax_category'],
				'id' => NEW_ENTRY
			]);
		}
	}

	public function postDelete(): void
	{
		$tax_categories_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_NUMBER_INT);

		if($this->tax_category->delete_list($tax_categories_to_delete))
		{
			echo json_encode ([
				'success' => true,
				'message' => lang('Tax_categories.successful_deleted') . ' ' . count($tax_categories_to_delete) . ' ' . lang('Tax_categories.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => false, 'message' => lang('Tax_categories.cannot_be_deleted')]);
		}
	}
}
