<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Summary_report.php");

class Summary_categories extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('category' => lang('Reports.category')),
			array('quantity' => lang('Reports.quantity'), 'sorter' => 'number_sorter'),
			array('subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'),
			array('total' => lang('Reports.total'), 'sorter' => 'number_sorter'),
			array('cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'),
			array('profit' => lang('Reports.profit'), 'sorter' => 'number_sorter'));
	}

	protected function _select(array $inputs)
	{
		parent::_select($inputs);

		$this->db->select('
				items.category AS category,
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');
	}

	protected function _from()
	{
		parent::_from();

		$this->db->join('items AS items', 'sales_items.item_id = items.item_id', 'inner');
	}

	protected function _group_order()
	{
		$this->db->group_by('category');
		$builder->orderBy('category');
	}
}
?>
