<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Summary_report.php");

class Summary_expenses_categories extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('category_name' => lang('Reports.expenses_category')),
			array('count' => lang('Reports.count')),
			array('total_amount' => lang('Reports.expenses_amount'), 'sorter' => 'number_sorter'),
			array('total_tax_amount' => lang('Reports.expenses_tax_amount'), 'sorter' => 'number_sorter'));
	}

	public function getData(array $inputs)
	{
		$builder->select('expense_categories.category_name AS category_name, COUNT(expenses.expense_id) AS count, SUM(expenses.amount) AS total_amount, SUM(expenses.tax_amount) AS total_tax_amount');
		$builder = $this->db->table('expenses AS expenses');
		$builder->join('expense_categories AS expense_categories', 'expense_categories.expense_category_id = expenses.expense_category_id', 'LEFT');

		if(empty($this->config->get('date_or_time_format')))
		{
			$builder->where('DATE(expenses.date) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$builder->where('expenses.date BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}

		$builder->where('expenses.deleted', 0);

		$builder->groupBy('expense_categories.category_name');
		$builder->orderBy('expense_categories.category_name');

		return $builder->get()->getResultArray();
	}

	public function getSummaryData(array $inputs)
	{
		$builder->select('SUM(expenses.amount) AS expenses_total_amount, SUM(expenses.tax_amount) AS expenses_total_tax_amount');
		$builder = $this->db->table('expenses AS expenses');

		if(empty($this->config->get('date_or_time_format')))
		{
			$builder->where('DATE(expenses.date) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$builder->where('expenses.date BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}

		$builder->where('expenses.deleted', 0);

		return $builder->get()->getRowArray();
	}
}
?>
