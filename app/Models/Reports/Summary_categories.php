<?php

namespace App\Models\Reports;

use CodeIgniter\Model;



class Summary_categories extends Summary_report
{
	protected function _get_data_columns()
	{
		return [
			['category' => lang('Reports.category')],
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
				items.category AS category,
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');
	}

	protected function _from()
	{
		parent::_from();

		$builder->join('items AS items', 'sales_items.item_id = items.item_id', 'inner');
	}

	protected function _group_order()
	{
		$builder->groupBy('category');
		$builder->orderBy('category');
	}
}
?>
