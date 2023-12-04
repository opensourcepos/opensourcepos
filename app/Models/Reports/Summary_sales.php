<?php

namespace App\Models\Reports;

class Summary_sales extends Summary_report
{
	protected function _get_data_columns(): array
	{
		return [
			['sale_date' => lang('Reports.date'), 'sortable' => false],
			['sales' => lang('Reports.sales'), 'sorter' => 'number_sorter'],
			['quantity' => lang('Reports.quantity'), 'sorter' => 'number_sorter'],
			['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
			['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'],
			['total' => lang('Reports.total'), 'sorter' => 'number_sorter'],
			['cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'],
			['profit' => lang('Reports.profit'), 'sorter' => 'number_sorter']
		];
	}

	protected function _select(array $inputs, object &$builder): void	//TODO: hungarian notation
	{
		parent::_select($inputs, $builder);	//TODO: hungarian notation

		$builder->select('
				DATE(sales.sale_time) AS sale_date,
				SUM(sales_items.quantity_purchased) AS quantity_purchased,
				COUNT(DISTINCT sales.sale_id) AS sales
		');
	}

	protected function _group_order(object &$builder): void	//TODO: hungarian notation
	{
		$builder->groupBy('sale_date');
		$builder->orderBy('sale_date');
	}
}
