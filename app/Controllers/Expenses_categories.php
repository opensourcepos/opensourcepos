<?php

namespace App\Controllers;

use App\Models\Expense_category;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Expenses_categories extends Secure_Controller    // TODO: Is this class ever used?
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
    public function getIndex(): string
    {
        $data['table_headers'] = get_expense_category_manage_table_headers();

        return view('expenses_categories/manage', $data);
    }

    /**
     * Returns expense_category_manage table data rows. This will be called with AJAX.
     **/
    public function getSearch(): ResponseInterface
    {
        $search = $this->request->getGet('search');
        $limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort   = $this->sanitizeSortColumn(expense_category_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'expense_category_id');
        $order  = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $expense_categories = $this->expense_category->search($search, $limit, $offset, $sort, $order);
        $total_rows = $this->expense_category->get_found_rows($search);

        $data_rows = [];
        foreach ($expense_categories->getResult() as $expense_category) {
            $data_rows[] = get_expense_category_data_row($expense_category);
        }

        return $this->response->setJSON(['total' => $total_rows, 'rows' => $data_rows]);
    }

    /**
     * @param int $row_id
     * @return void
     */
    public function getRow(int $row_id): ResponseInterface
    {
        $data_row = get_expense_category_data_row($this->expense_category->get_info($row_id));

        return $this->response->setJSON($data_row);
    }

    /**
     * @param int $expense_category_id
     * @return void
     */
    public function getView(int $expense_category_id = NEW_ENTRY): string
    {
        $data['category_info'] = $this->expense_category->get_info($expense_category_id);

        return view("expenses_categories/form", $data);
    }

    /**
     * @param int $expense_category_id
     * @return void
     */
    public function postSave(int $expense_category_id = NEW_ENTRY): ResponseInterface
    {
        $expense_category_data = [
            'category_name'        => $this->request->getPost('category_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'category_description' => $this->request->getPost('category_description', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        ];

        if ($this->expense_category->save_value($expense_category_data, $expense_category_id)) {
            // New expense_category
            if ($expense_category_id == NEW_ENTRY) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Expenses_categories.successful_adding'),
                    'id'      => $expense_category_data['expense_category_id']
                ]);
            } else { // Existing Expense Category
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Expenses_categories.successful_updating'),
                    'id'      => $expense_category_id
                ]);
            }
        } else { // Failure
            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Expenses_categories.error_adding_updating') . ' ' . $expense_category_data['category_name'],
                'id'      => NEW_ENTRY
            ]);
        }
    }

    /**
     * @return void
     */
    public function postDelete(): ResponseInterface
    {
        $expense_category_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($this->expense_category->delete_list($expense_category_to_delete)) {    // TODO: Convert to ternary notation.
            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Expenses_categories.successful_deleted') . ' ' . count($expense_category_to_delete) . ' ' . lang('Expenses_categories.one_or_multiple')
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => lang('Expenses_categories.cannot_be_deleted')]);
        }
    }
}
