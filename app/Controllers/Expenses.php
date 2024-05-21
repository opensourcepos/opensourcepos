<?php

namespace App\Controllers;

use App\Models\Expense;
use App\Models\Expense_category;
use Config\OSPOS;
use Config\Services;

class Expenses extends Secure_Controller
{
	private Expense $expense;
	private Expense_category $expense_category;

	public function __construct()
	{
		parent::__construct('expenses');

		$this->expense = model(Expense::class);
		$this->expense_category = model(Expense_category::class);
	}

	/**
	 * @return void
	 */
	public function getIndex(): void
	{
		$data['table_headers'] = get_expenses_manage_table_headers();

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = [
			'only_cash' => lang('Expenses.cash_filter'),
			'only_due' => lang('Expenses.due_filter'),
			'only_check' => lang('Expenses.check_filter'),
			'only_credit' => lang('Expenses.credit_filter'),
			'only_debit' => lang('Expenses.debit_filter'),
			'is_deleted' => lang('Expenses.is_deleted')
		];

		echo view('expenses/manage', $data);
	}

	/**
	 * @return void
	 */
	public function getSearch(): void
	{
		$search   = Services::htmlPurifier()->purify($this->request->getGet('search'));
		$limit    = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset   = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort     = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order    = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$filters  = [
			'start_date' => $this->request->getGet('start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'end_date' => $this->request->getGet('end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'only_cash' => false,
			'only_due' => false,
			'only_check' => false,
			'only_credit' => false,
			'only_debit' => false,
			'is_deleted' => false
		];

		// check if any filter is set in the multiselect dropdown
		$request_filters = array_fill_keys($this->request->getGet('filters', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [], true);
		$filters = array_merge($filters, $request_filters);
		$expenses = $this->expense->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->expense->get_found_rows($search, $filters);
		$payments = $this->expense->get_payments_summary($search, $filters);
		$payment_summary = get_expenses_manage_payments_summary($payments, $expenses);
		$data_rows = [];

		foreach($expenses->getResult() as $expense)
		{
			$data_rows[] = get_expenses_data_row($expense);
		}

		if($total_rows > 0)
		{
			$data_rows[] = get_expenses_data_last_row($expenses);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary]);
	}

	/**
	 * @param int $expense_id
	 * @return void
	 */
	public function getView(int $expense_id = NEW_ENTRY): void
	{
		$data = [];	//TODO: Duplicated code

		$data['employees'] = [];
		foreach($this->employee->get_all()->getResult() as $employee)
		{
			foreach(get_object_vars($employee) as $property => $value)
			{
				$employee->$property = $value;
			}

			$data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
		}

		$data['expenses_info'] = $this->expense->get_info($expense_id);

		$expense_categories = [];
		foreach($this->expense_category->get_all(0, 0, true)->getResultArray() as $row)
		{
			$expense_categories[$row['expense_category_id']] = $row['category_name'];
		}
		$data['expense_categories'] = $expense_categories;

		$expense_id = $data['expenses_info']->expense_id;

		if($expense_id == NEW_ENTRY)
		{
			$data['expenses_info']->date = date('Y-m-d H:i:s');
			$data['expenses_info']->employee_id = $this->employee->get_logged_in_employee_info()->person_id;
		}

		$data['payments'] = [];
		foreach($this->expense->get_expense_payment($expense_id)->getResult() as $payment)
		{
			foreach(get_object_vars($payment) as $property => $value)
			{
				$payment->$property = $value;
			}

			$data['payments'][] = $payment;
		}

		// don't allow gift card to be a payment option in a sale transaction edit because it's a complex change
		$data['payment_options'] = $this->expense->get_payment_options();

		echo view("expenses/form", $data);
	}

	/**
	 * @param int $row_id
	 * @return void
	 */
	public function getRow(int $row_id): void
	{
		$expense_info = $this->expense->get_info($row_id);
		$data_row = get_expenses_data_row($expense_info);

		echo json_encode($data_row);
	}

	/**
	 * @param int $expense_id
	 * @return void
	 */
	public function postSave(int $expense_id = NEW_ENTRY): void
	{
		$config = config(OSPOS::class)->settings;
		$newdate = $this->request->getPost('date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$date_formatter = date_create_from_format($config['dateformat'] . ' ' . $config['timeformat'], $newdate);
		$amount = prepare_decimal($this->request->getPost('amount'));
		$tax_amount = prepare_decimal($this->request->getPost('tax_amount'));

		$expense_data = [
			'date' => $date_formatter->format('Y-m-d H:i:s'),
			'supplier_id' => $this->request->getPost('supplier_id') == '' ? null : $this->request->getPost('supplier_id', FILTER_SANITIZE_NUMBER_INT),
			'supplier_tax_code' => $this->request->getPost('supplier_tax_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'amount' => filter_var($amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
			'tax_amount' => filter_var($tax_amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
			'payment_type' => $this->request->getPost('payment_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'expense_category_id' => $this->request->getPost('expense_category_id', FILTER_SANITIZE_NUMBER_INT),
			'description' => $this->request->getPost('description', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'employee_id' => $this->request->getPost('employee_id', FILTER_SANITIZE_NUMBER_INT),
			'deleted' => $this->request->getPost('deleted') != null
		];

		if($this->expense->save_value($expense_data, $expense_id))
		{
			//New Expense
			if($expense_id == NEW_ENTRY)
			{
				echo json_encode (['success' => true, 'message' => lang('Expenses.successful_adding'), 'id' => $expense_data['expense_id']]);
			}
			else // Existing Expense
			{
				echo json_encode (['success' => true, 'message' => lang('Expenses.successful_updating'), 'id' => $expense_id]);
			}
		}
		else//failure
		{
			echo json_encode (['success' => false, 'message' => lang('Expenses.error_adding_updating'), 'id' => NEW_ENTRY]);
		}
	}

	/**
	 * @return void
	 */
	public function ajax_check_amount(): void
	{
		$value = $this->request->getPost();
		$parsed_value = filter_var(prepare_decimal(array_pop($value)), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		echo json_encode (['success' => $parsed_value !== false]);
	}

	/**
	 * @return void
	 */
	public function postDelete(): void
	{
		$expenses_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if($this->expense->delete_list($expenses_to_delete))
		{
			echo json_encode (['success' => true, 'message' => lang('Expenses.successful_deleted') . ' ' . count($expenses_to_delete) . ' ' . lang('Expenses.one_or_multiple'), 'ids' => $expenses_to_delete]);
		}
		else
		{
			echo json_encode (['success' => false, 'message' => lang('Expenses.cannot_be_deleted'), 'ids' => $expenses_to_delete]);
		}
	}
}
