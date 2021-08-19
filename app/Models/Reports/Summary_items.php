<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Summary_report.php");

class Summary_items extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('item_name' => lang('Reports.item')),
			array('category' => lang('Reports.category')),
			array('unit_price' => lang('Reports.unit_price'), 'sorter' => 'number_sorter'),
			array('quantity' => lang('Reports.quantity')),
			array('subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'),
			array('total' => lang('Reports.total'), 'sorter' => 'number_sorter'),
			array('cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'),
			array('profit' => lang('Reports.profit'), 'sorter' => 'number_sorter'));
	}

	protected function _select(array $inputs)
	{
		parent::_select($inputs);

		$builder->select('
				MAX(items.name) AS name,
				MAX(items.category) AS category,
				MAX(items.unit_price) AS unit_price,
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
		$builder->groupBy('items.item_id');
		$builder->orderBy('name');
	}
}
?>
