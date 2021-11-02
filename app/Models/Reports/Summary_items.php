<?php

namespace App\Models\Reports;

class Summary_items extends Summary_report
{
	protected function _get_data_columns(): array	//TODO: Hungarian notation
	{
		return [
			['item_name' => lang('Reports.item')],
			['category' => lang('Reports.category')],
			['unit_price' => lang('Reports.unit_price'), 'sorter' => 'number_sorter'],
			['quantity' => lang('Reports.quantity')],
			['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
			['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'],
			['total' => lang('Reports.total'), 'sorter' => 'number_sorter'],
			['cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'],
			['profit' => lang('Reports.profit'), 'sorter' => 'number_sorter']
		];
	}
//TODO: Probably going to need to rework these since you can't reference $builder without it's instantiation.
	protected function _select(array $inputs): void	//TODO: hungarian notation
	{
		parent::_select($inputs);	//TODO: hungarian notation

		$builder->select('
				MAX(items.name) AS name,
				MAX(items.category) AS category,
				MAX(items.unit_price) AS unit_price,
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');
	}

	protected function _from(): void	//TODO: hungarian notation
	{
		parent::_from();

		$builder->join('items AS items', 'sales_items.item_id = items.item_id', 'inner');
	}

	protected function _group_order(): void	//TODO: hungarian notation
	{
		$builder->groupBy('items.item_id');
		$builder->orderBy('name');
	}
}
?>
