<?php

namespace App\Models\Reports;

use CodeIgniter\Model;



class Summary_employees extends Summary_report
{
	protected function _get_data_columns()
	{
		return [
			['employee_name' => lang('Reports.employee')],
			['sales' => lang('Reports.sales'), 'sorter' => 'number_sorter'],
			['quantity' => lang('Reports.quantity'), 'sorter' => 'number_sorter'],
			['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
			['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'],
			['total' => lang('Reports.total'), 'sorter' => 'number_sorter'],
			['cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'],
			['profit' => lang('Reports.profit'), 'sorter' => 'number_sorter']
		];
	}

	protected function _select(array $inputs)
	{
		parent::_select($inputs);

		$builder->select('
				MAX(CONCAT(employee_p.first_name, " ", employee_p.last_name)) AS employee,
				SUM(sales_items.quantity_purchased) AS quantity_purchased,
				COUNT(DISTINCT sales.sale_id) AS sales
		');
	}

	protected function _from()
	{
		parent::_from();

		$builder->join('people AS employee_p', 'sales.employee_id = employee_p.person_id');
	}

	protected function _group_order()
	{
		$builder->groupBy('sales.employee_id');
		$builder->orderBy('employee_p.last_name');
	}
}
?>
