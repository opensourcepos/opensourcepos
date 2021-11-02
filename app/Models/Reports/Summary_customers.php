<?php

namespace App\Models\Reports;

class Summary_customers extends Summary_report
{
	protected function _get_data_columns(): array	//TODO: Hungarian notation
	{
		return [
			['customer_name' => lang('Reports.customer')],
			['sales' => lang('Reports.sales'), 'sorter' => 'number_sorter'],
			['quantity' => lang('Reports.quantity'), 'sorter' => 'number_sorter'],
			['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
			['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'],
			['total' => lang('Reports.total'), 'sorter' => 'number_sorter'],
			['cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'],
			['profit' => lang('Reports.profit'), 'sorter' => 'number_sorter']
		];
	}

	protected function _select(array $inputs): void	//TODO: Hungarian notation
	{
		parent::_select($inputs);	//TODO: Hungarian notation
//TODO: Probably going to need to rework these since you can't reference $builder without it's instantiation.

		$builder->select('
				MAX(CONCAT(customer_p.first_name, " ", customer_p.last_name)) AS customer,
				SUM(sales_items.quantity_purchased) AS quantity_purchased,
				COUNT(DISTINCT sales.sale_id) AS sales
		');
	}

	protected function _from(): void	//TODO: Hungarian notation
	{
		parent::_from();	//TODO: Hungarian notation

		$builder->join('people AS customer_p', 'sales.customer_id = customer_p.person_id');
	}

	protected function _group_order(): void	//TODO: Hungarian notation
	{
		$builder->groupBy('sales.customer_id');
		$builder->orderBy('customer_p.last_name');
	}
}
?>
