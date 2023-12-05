<?php

namespace App\Models\Reports;

use Config\OSPOS;

class Summary_expenses_categories extends Summary_report
{
	/**
	 * @return array[]
	 */
	protected function _get_data_columns(): array	//TODO: Hungarian notation
	{
		return [
			['category_name' => lang('Reports.expenses_category')],
			['count' => lang('Reports.count')],
			['total_amount' => lang('Reports.expenses_amount'), 'sorter' => 'number_sorter'],
			['total_tax_amount' => lang('Reports.expenses_tax_amount'), 'sorter' => 'number_sorter']
		];
	}

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getData(array $inputs): array
	{
		$config = config(OSPOS::class)->settings;

		$builder = $this->db->table('expenses AS expenses');
		$builder->select('expense_categories.category_name AS category_name, COUNT(expenses.expense_id) AS count, SUM(expenses.amount) AS total_amount, SUM(expenses.tax_amount) AS total_tax_amount');
		$builder->join('expense_categories AS expense_categories', 'expense_categories.expense_category_id = expenses.expense_category_id', 'LEFT');

		//TODO: convert this to ternary notation
		if(empty($config['date_or_time_format']))	//TODO: Duplicated code
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

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getSummaryData(array $inputs): array
	{
		$config = config(OSPOS::class)->settings;

		$builder = $this->db->table('expenses AS expenses');
		$builder->select('SUM(expenses.amount) AS expenses_total_amount, SUM(expenses.tax_amount) AS expenses_total_tax_amount');

		if(empty($config['date_or_time_format']))	//TODO: Duplicated code
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
