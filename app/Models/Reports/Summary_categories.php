<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Summary_report.php");

class Summary_categories extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('category' => lang('reports_category')),
			array('quantity' => lang('reports_quantity'), 'sorter' => 'number_sorter'),
			array('subtotal' => lang('reports_subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => lang('reports_tax'), 'sorter' => 'number_sorter'),
			array('total' => lang('reports_total'), 'sorter' => 'number_sorter'),
			array('cost' => lang('reports_cost'), 'sorter' => 'number_sorter'),
			array('profit' => lang('reports_profit'), 'sorter' => 'number_sorter'));
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
		$this->db->order_by('category');
	}
}
?>
