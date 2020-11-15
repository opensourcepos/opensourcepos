<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_categories extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('category' => $this->lang->line('reports_category')),
			array('quantity' => $this->lang->line('reports_quantity'), 'sorter' => 'number_sorter'),
			array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
			array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
			array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
			array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'));
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
