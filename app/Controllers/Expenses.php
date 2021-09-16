<?php

namespace App\Controllers;

require_once("Secure_Controller.php");

class Expenses extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('expenses');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_expenses_manage_table_headers());

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

	public function search()
	{
		$payments = 0;
		$search   = $this->request->getGet('search');
		$limit    = $this->request->getGet('limit');
		$offset   = $this->request->getGet('offset');
		$sort     = $this->request->getGet('sort');
		$order    = $this->request->getGet('order');
		$filters  = [
			'start_date' => $this->request->getGet('start_date'),
			'end_date' => $this->request->getGet('end_date'),
			'only_cash' => FALSE,
			'only_due' => FALSE,
			'only_check' => FALSE,
			'only_credit' => FALSE,
			'only_debit' => FALSE,
			'is_deleted' => FALSE
		];

		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->request->getGet('filters'), TRUE);
		$filters = array_merge($filters, $filledup);
		$expenses = $this->Expense->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->Expense->get_found_rows($search, $filters);
		$payments = $this->Expense->get_payments_summary($search, $filters);
		$payment_summary = get_expenses_manage_payments_summary($payments, $expenses);
		$data_rows = [];
		foreach($expenses->getResult() as $expense)
		{
			$data_rows[] = $this->xss_clean(get_expenses_data_row($expense));
		}

		if($total_rows > 0)
		{
			$data_rows[] = $this->xss_clean(get_expenses_data_last_row($expenses));
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary]);
	}

	public function view($expense_id = -1)
	{
		$data = [];

		$data['employees'] = [];
		foreach($this->Employee->get_all()->getResult() as $employee)
		{
			foreach(get_object_vars($employee) as $property => $value)
			{
				$employee->$property = $this->xss_clean($value);
			}

			$data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
		}

		$data['expenses_info'] = $this->Expense->get_info($expense_id);

		$expense_categories = [];
		foreach($this->Expense_category->get_all(0, 0, TRUE)->getResultArray() as $row)
		{
			$expense_categories[$row['expense_category_id']] = $row['category_name'];
		}
		$data['expense_categories'] = $expense_categories;

		$expense_id = $data['expenses_info']->expense_id;

		if(empty($expense_id))
		{
			$data['expenses_info']->date = date('Y-m-d H:i:s');
			$data['expenses_info']->employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		}

		$data['payments'] = [];
		foreach($this->Expense->get_expense_payment($expense_id)->getResult() as $payment)
		{
			foreach(get_object_vars($payment) as $property => $value)
			{
				$payment->$property = $this->xss_clean($value);
			}

			$data['payments'][] = $payment;
		}

		// don't allow gift card to be a payment option in a sale transaction edit because it's a complex change
		$data['payment_options'] = $this->xss_clean($this->Expense->get_payment_options(FALSE));

		echo view("expenses/form", $data);
	}

	public function get_row($row_id)
	{
		$expense_info = $this->Expense->get_info($row_id);
		$data_row = $this->xss_clean(get_expenses_data_row($expense_info));

		echo json_encode($data_row);
	}

	public function save($expense_id = -1)
	{
		$newdate = $this->request->getPost('date');

		$date_formatter = date_create_from_format($this->config->get('dateformat') . ' ' . $this->config->get('timeformat'), $newdate);

		$expense_data = [
			'date' => $date_formatter->format('Y-m-d H:i:s'),
			'supplier_id' => $this->request->getPost('supplier_id') == '' ? NULL : $this->request->getPost('supplier_id'),
			'supplier_tax_code' => $this->request->getPost('supplier_tax_code'),
			'amount' => parse_decimals($this->request->getPost('amount')),
			'tax_amount' => parse_decimals($this->request->getPost('tax_amount')),
			'payment_type' => $this->request->getPost('payment_type'),
			'expense_category_id' => $this->request->getPost('expense_category_id'),
			'description' => $this->request->getPost('description'),
			'employee_id' => $this->request->getPost('employee_id'),
			'deleted' => $this->request->getPost('deleted') != NULL
		];

		if($this->Expense->save($expense_data, $expense_id))
		{
			$expense_data = $this->xss_clean($expense_data);

			//New expense_id
			if($expense_id == -1)
			{
				echo json_encode (['success' => TRUE, 'message' => lang('Expenses.successful_adding'), 'id' => $expense_data['expense_id']]);
			}
			else // Existing Expense
			{
				echo json_encode (['success' => TRUE, 'message' => lang('Expenses.successful_updating'), 'id' => $expense_id]);
			}
		}
		else//failure
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Expenses.error_adding_updating'), 'id' => -1]);	//TODO: Need to replace -1 with a constant
		}
	}

	public function ajax_check_amount()
	{
		$value = $this->request->getPost();
		$parsed_value = parse_decimals(array_pop($value));
		echo json_encode (['success' => $parsed_value !== FALSE]);
	}

	public function delete()
	{
		$expenses_to_delete = $this->request->getPost('ids');

		if($this->Expense->delete_list($expenses_to_delete))
		{
			echo json_encode (['success' => TRUE, 'message' => lang('Expenses.successful_deleted') . ' ' . count($expenses_to_delete) . ' ' . lang('Expenses.one_or_multiple'), 'ids' => $expenses_to_delete]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Expenses.cannot_be_deleted'), 'ids' => $expenses_to_delete]);
		}
	}
}
?>
