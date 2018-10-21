<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Expense class
 */

class Expense extends CI_Model
{
	/*
	Determines if a given Expense_id is an Expense
	*/
	public function exists($expense_id)
	{
		$this->db->from('expenses');
		$this->db->where('expense_id', $expense_id);

		return ($this->db->get()->num_rows() == 1);
	}

	/*
	Gets category info
	*/
	public function get_expense_category($expense_id)
	{
		$this->db->from('expenses');
		$this->db->where('expense_id', $expense_id);

		return $this->Expense_category->get_info($this->db->get()->row()->expense_category_id);
	}

	/*
	Gets employee info
	*/
	public function get_employee($expense_id)
	{
		$this->db->from('expenses');
		$this->db->where('expense_id', $expense_id);

		return $this->Employee->get_info($this->db->get()->row()->employee_id);
	}

	public function get_multiple_info($expense_ids)
	{
		$this->db->from('expenses');
		$this->db->where_in('expenses.expense_id', $expense_ids);
		$this->db->order_by('expense_id', 'asc');

		return $this->db->get();
	}

	/*
	Gets rows
	*/
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters, 0, 0, 'expense_id', 'asc', TRUE);
	}

	/*
	Searches expenses
	*/
	public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'expense_id', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(DISTINCT expenses.expense_id) as count');
		}
		else
		{
			$this->db->select('
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

		$this->db->from('expenses AS expenses');
		$this->db->join('people AS employees', 'employees.person_id = expenses.employee_id', 'LEFT');
		$this->db->join('expense_categories AS expense_categories', 'expense_categories.expense_category_id = expenses.expense_category_id', 'LEFT');
		$this->db->join('suppliers AS suppliers', 'suppliers.person_id = expenses.supplier_id', 'LEFT');

		$this->db->group_start();
			$this->db->like('employees.first_name', $search);
			$this->db->or_like('expenses.date', $search);
			$this->db->or_like('employees.last_name', $search);
			$this->db->or_like('expenses.payment_type', $search);
			$this->db->or_like('expenses.amount', $search);
			$this->db->or_like('expense_categories.category_name', $search);
			$this->db->or_like('CONCAT(employees.first_name, " ", employees.last_name)', $search);
		$this->db->group_end();

		$this->db->where('expenses.deleted', $filters['is_deleted']);

		if(empty($this->config->item('date_or_time_format')))
		{
			$this->db->where('DATE_FORMAT(expenses.date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$this->db->where('expenses.date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if($filters['only_debit'] != FALSE)
		{
			$this->db->like('expenses.payment_type', $this->lang->line('expenses_debit'));
		}

		if($filters['only_credit'] != FALSE)
		{
			$this->db->like('expenses.payment_type', $this->lang->line('expenses_credit'));
		}

		if($filters['only_cash'] != FALSE)
		{
			$this->db->group_start();
				$this->db->like('expenses.payment_type', $this->lang->line('expenses_cash'));
				$this->db->or_where('expenses.payment_type IS NULL');
			$this->db->group_end();
		}

		if($filters['only_due'] != FALSE)
		{
			$this->db->like('expenses.payment_type', $this->lang->line('expenses_due'));
		}

		if($filters['only_check'] != FALSE)
		{
			$this->db->like('expenses.payment_type', $this->lang->line('expenses_check'));
		}

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $this->db->get()->row()->count;
		}

		$this->db->group_by('expense_id');

		$this->db->order_by($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/*
	Gets information about a particular expense
	*/
	public function get_info($expense_id)
	{
		$this->db->select('
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
		$this->db->from('expenses AS expenses');
		$this->db->join('people AS employees', 'employees.person_id = expenses.employee_id', 'LEFT');
		$this->db->join('expense_categories AS expense_categories', 'expense_categories.expense_category_id = expenses.expense_category_id', 'LEFT');
		$this->db->join('suppliers AS suppliers', 'suppliers.person_id = expenses.supplier_id', 'LEFT');
		$this->db->where('expense_id', $expense_id);

		$query = $this->db->get();
		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object
			$expenses_obj = new stdClass();

			//Get all the fields from expenses table
			foreach($this->db->list_fields('expenses') as $field)
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
	public function save(&$expense_data, $expense_id = FALSE)
	{
		if(!$expense_id || !$this->exists($expense_id))
		{
			if($this->db->insert('expenses', $expense_data))
			{
				$expense_data['expense_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('expense_id', $expense_id);

		return $this->db->update('expenses', $expense_data);
	}

	/*
	Deletes a list of expense_category
	*/
	public function delete_list($expense_ids)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
			$this->db->where_in('expense_id', $expense_ids);
			$success = $this->db->update('expenses', array('deleted'=>1));
		$this->db->trans_complete();

		return $success;
	}

	/*
	Gets the payment summary for the expenses (expenses/manage) view
	*/
	public function get_payments_summary($search, $filters)
	{
		// get payment summary
		$this->db->select('payment_type, COUNT(amount) AS count, SUM(amount) AS amount');
		$this->db->from('expenses');
		$this->db->where('deleted', $filters['is_deleted']);

		if(empty($this->config->item('date_or_time_format')))
		{
			$this->db->where('DATE_FORMAT(date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$this->db->where('date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if($filters['only_cash'] != FALSE)
		{
			$this->db->like('payment_type', $this->lang->line('expenses_cash'));
		}

		if($filters['only_due'] != FALSE)
		{
			$this->db->like('payment_type', $this->lang->line('expenses_due'));
		}

		if($filters['only_check'] != FALSE)
		{
			$this->db->like('payment_type', $this->lang->line('expenses_check'));
		}

		if($filters['only_credit'] != FALSE)
		{
			$this->db->like('payment_type', $this->lang->line('expenses_credit'));
		}

		if($filters['only_debit'] != FALSE)
		{
			$this->db->like('payment_type', $this->lang->line('expenses_debit'));
		}

		$this->db->group_by('payment_type');

		$payments = $this->db->get()->result_array();

		return $payments;
	}

	/*
	Gets the payment options to show in the expense forms
	*/
	public function get_payment_options()
	{
		return get_payment_options();
	}

	/*
	Gets the expense payment
	*/
	public function get_expense_payment($expense_id)
	{
		$this->db->from('expenses');
		$this->db->where('expense_id', $expense_id);

		return $this->db->get();
	}
}
?>
