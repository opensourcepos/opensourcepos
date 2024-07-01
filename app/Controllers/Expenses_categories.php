<?php

namespace App\Controllers;

use App\Models\Expense_category;
use Config\Services;

class Expenses_categories extends Secure_Controller	//TODO: Is this class ever used?
{
	private Expense_category $expense_category;

	public function __construct()
	{
		parent::__construct('expenses_categories');

		$this->expense_category = model(Expense_category::class);
	}

	/**
	 * @return void
	 */
	public function getIndex(): void
	{
		$data['table_headers'] = get_expense_category_manage_table_headers();

		 echo view('expenses_categories/manage', $data);
	}

	/**
	 * Returns expense_category_manage table data rows. This will be called with AJAX.
	 **/
	public function getSearch(): void
	{
		$search = $this->request->getGet('search');
		$limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order  = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$expense_categories = $this->expense_category->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->expense_category->get_found_rows($search);

		$data_rows = [];
		foreach($expense_categories->getResult() as $expense_category)
		{
			$data_rows[] = get_expense_category_data_row($expense_category);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	/**
	 * @param int $row_id
	 * @return void
	 */
	public function getRow(int $row_id): void
	{
		$data_row = get_expense_category_data_row($this->expense_category->get_info($row_id));

		echo json_encode($data_row);
	}

	/**
	 * @param int $expense_category_id
	 * @return void
	 */
	public function getView(int $expense_category_id = NEW_ENTRY): void
	{
		$data['category_info'] = $this->expense_category->get_info($expense_category_id);

		echo view("expenses_categories/form", $data);
	}

	/**
	 * @param int $expense_category_id
	 * @return void
	 */
	public function postSave(int $expense_category_id = NEW_ENTRY): void
	{
		$expense_category_data = [
			'category_name' => $this->request->getPost('category_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'category_description' => $this->request->getPost('category_description', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
		];

		if($this->expense_category->save_value($expense_category_data, $expense_category_id))
		{
			// New expense_category
			if($expense_category_id == NEW_ENTRY)
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Expenses_categories.successful_adding'),
					'id' => $expense_category_data['expense_category_id']
				]);
			}
			else // Existing Expense Category
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Expenses_categories.successful_updating'),
					'id' => $expense_category_id
				]);
			}
		}
		else//failure
		{
			echo json_encode ([
				'success' => true,
				'message' => lang('Expenses_categories.error_adding_updating') . ' ' . $expense_category_data['category_name'],
				'id' => NEW_ENTRY
			]);
		}
	}

	/**
	 * @return void
	 */
	public function postDelete(): void
	{
		$expense_category_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if($this->expense_category->delete_list($expense_category_to_delete))	//TODO: Convert to ternary notation.
		{
			echo json_encode([
				'success' => true,
				'message' => lang('Expenses_categories.successful_deleted') . ' ' . count($expense_category_to_delete) . ' ' . lang('Expenses_categories.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => false, 'message' => lang('Expenses_categories.cannot_be_deleted')]);
		}
	}
}
