<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Expense class
 */

class Expense extends Model
{
	/*
	* Determines if a given Expense_id is an Expense
	*/
	public function exists($expense_id): bool
	{
		$builder = $this->db->table('expenses');
		$builder->where('expense_id', $expense_id);

		return ($builder->get()->getNumRows() == 1);
	}

	/*
	Gets category info
	*/
	public function get_expense_category($expense_id)
	{
		$builder = $this->db->table('expenses');
		$builder->where('expense_id', $expense_id);

		return $this->Expense_category->get_info($builder->get()->getRow()->expense_category_id);
	}

	/*
	Gets employee info
	*/
	public function get_employee($expense_id)
	{
		$builder = $this->db->table('expenses');
		$builder->where('expense_id', $expense_id);

		return $this->Employee->get_info($builder->get()->getRow()->employee_id);
	}

	public function get_multiple_info($expense_ids)
	{
		$builder = $this->db->table('expenses');
		$builder->whereIn('expenses.expense_id', $expense_ids);
		$builder->orderBy('expense_id', 'asc');

		return $builder->get();
	}

	/*
	* Gets rows
	*/
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters, 0, 0, 'expense_id', 'asc', TRUE);
	}

	/*
	* Searches expenses
	*/
	public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'expense_id', $order = 'asc', $count_only = FALSE)
	{
		$builder = $this->db->table('expenses AS expenses');
		// get_found_rows case
		if($count_only == TRUE)
		{
			$builder->select('COUNT(DISTINCT expenses.expense_id) as count');
		}
		else
		{
			$builder->select('
				expenses.expense_id,
				MAX(expenses.date) AS date,
				MAX(suppliers.company_name) AS supplier_name,
				MAX(expenses.supplier_tax_code) AS supplier_tax_code,
				MAX(expenses.amount) AS amount,
				MAX(expenses.tax_amount) AS tax_amount,
				MAX(expenses.payment_type) AS payment_type,
				MAX(expenses.description) AS description,
				MAX(employees.first_name) AS first_name,
				MAX(employees.last_name) AS last_name,
				MAX(expense_categories.category_name) AS category_name
			');
		}

		$builder->join('people AS employees', 'employees.person_id = expenses.employee_id', 'LEFT');
		$builder->join('expense_categories AS expense_categories', 'expense_categories.expense_category_id = expenses.expense_category_id', 'LEFT');
		$builder->join('suppliers AS suppliers', 'suppliers.person_id = expenses.supplier_id', 'LEFT');

		$builder->groupStart();
			$builder->like('employees.first_name', $search);
			$builder->orLike('expenses.date', $search);
			$builder->orLike('employees.last_name', $search);
			$builder->orLike('expenses.payment_type', $search);
			$builder->orLike('expenses.amount', $search);
			$builder->orLike('expense_categories.category_name', $search);
			$builder->orLike('CONCAT(employees.first_name, " ", employees.last_name)', $search);
		$builder->groupEnd();

		$builder->where('expenses.deleted', $filters['is_deleted']);

		if(empty($this->config->item('date_or_time_format')))
		{
			$builder->where('DATE_FORMAT(expenses.date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$builder->where('expenses.date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if($filters['only_debit'] != FALSE)
		{
			$builder->like('expenses.payment_type', lang('Expenses.debit'));
		}

		if($filters['only_credit'] != FALSE)
		{
			$builder->like('expenses.payment_type', lang('Expenses.credit'));
		}

		if($filters['only_cash'] != FALSE)
		{
			$builder->groupStart();
				$builder->like('expenses.payment_type', lang('Expenses.cash'));
				$builder->orWhere('expenses.payment_type IS NULL');
			$builder->groupEnd();
		}

		if($filters['only_due'] != FALSE)
		{
			$builder->like('expenses.payment_type', lang('Expenses.due'));
		}

		if($filters['only_check'] != FALSE)
		{
			$builder->like('expenses.payment_type', lang('Expenses.check'));
		}

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $builder->get()->getRow()->count;
		}

		$builder->groupBy('expense_id');

		$builder->orderBy($sort, $order);

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/*
	Gets information about a particular expense
	*/
	public function get_info($expense_id)
	{
		$builder = $this->db->table('expenses AS expenses');
		$builder->select('
			expenses.expense_id AS expense_id,
			expenses.date AS date,
			suppliers.company_name AS supplier_name,
			expenses.supplier_id AS supplier_id,
			expenses.supplier_tax_code AS supplier_tax_code,
			expenses.amount AS amount,
			expenses.tax_amount AS tax_amount,
			expenses.payment_type AS payment_type,
			expenses.description AS description,
			expenses.employee_id AS employee_id,
			expenses.deleted AS deleted,
			employees.first_name AS first_name,
			employees.last_name AS last_name,
			expense_categories.expense_category_id AS expense_category_id,
			expense_categories.category_name AS category_name
		');

		$builder->join('people AS employees', 'employees.person_id = expenses.employee_id', 'LEFT');
		$builder->join('expense_categories AS expense_categories', 'expense_categories.expense_category_id = expenses.expense_category_id', 'LEFT');
		$builder->join('suppliers AS suppliers', 'suppliers.person_id = expenses.supplier_id', 'LEFT');
		$builder->where('expense_id', $expense_id);

		$query = $builder->get();
		if($query->getNumRows() == 1)
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object
			$expenses_obj = new stdClass();

			//Get all the fields from expenses table
			foreach($this->db->getFieldNames('expenses') as $field)
			{
				$expenses_obj->$field = '';
			}

			$expenses_obj->supplier_name = '';

			return $expenses_obj;
		}
	}

	/*
	Inserts or updates an expense
	*/
	public function save(&$expense_data, $expense_id = FALSE): bool
	{
		$builder = $this->db->table('expenses');

		if(!$expense_id || !$this->exists($expense_id))
		{
			if($builder->insert('expenses', $expense_data))
			{
				$expense_data['expense_id'] = $this->db->insertID();

				return TRUE;
			}

			return FALSE;
		}

		$builder->where('expense_id', $expense_id);

		return $builder->update($expense_data);
	}

	/*
	Deletes a list of expense_category
	*/
	public function delete_list($expense_ids): bool
	{
		$success = FALSE;
		$builder = $this->db->table('expenses');

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();
			$builder->whereIn('expense_id', $expense_ids);
			$success = $builder->update(['deleted' => 1]);
		$this->db->transComplete();

		return $success;
	}

	/*
	Gets the payment summary for the expenses (expenses/manage) view
	*/
	public function get_payments_summary($search, $filters): array
	{
		// get payment summary
		$builder = $this->db->table('expenses');
		$builder->select('payment_type, COUNT(amount) AS count, SUM(amount) AS amount');
		$builder->where('deleted', $filters['is_deleted']);

		if(empty($this->config->item('date_or_time_format')))
		{
			$builder->where('DATE_FORMAT(date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$builder->where('date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if($filters['only_cash'] != FALSE)
		{
			$builder->like('payment_type', lang('Expenses.cash'));
		}

		if($filters['only_due'] != FALSE)
		{
			$builder->like('payment_type', lang('Expenses.due'));
		}

		if($filters['only_check'] != FALSE)
		{
			$builder->like('payment_type', lang('Expenses.check'));
		}

		if($filters['only_credit'] != FALSE)
		{
			$builder->like('payment_type', lang('Expenses.credit'));
		}

		if($filters['only_debit'] != FALSE)
		{
			$builder->like('payment_type', lang('Expenses.debit'));
		}

		$builder->groupBy('payment_type');

		return $builder->get()->getResultArray();;
	}

	/*
	Gets the payment options to show in the expense forms
	*/
	public function get_payment_options(): array
	{
		return get_payment_options();
	}

	/*
	Gets the expense payment
	*/
	public function get_expense_payment($expense_id)
	{
		$builder = $this->db->table('expenses');
		$builder->where('expense_id', $expense_id);

		return $builder->get();
	}
}
?>
