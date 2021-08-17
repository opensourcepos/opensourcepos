<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Summary_report.php");

class Summary_customers extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('customer_name' => lang('reports_customer')),
			array('sales' => lang('reports_sales'), 'sorter' => 'number_sorter'),
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
				MAX(CONCAT(customer_p.first_name, " ", customer_p.last_name)) AS customer,
				SUM(sales_items.quantity_purchased) AS quantity_purchased,
				COUNT(DISTINCT sales.sale_id) AS sales
		');
	}

	protected function _from()
	{
		parent::_from();

		$this->db->join('people AS customer_p', 'sales.customer_id = customer_p.person_id');
	}

	protected function _group_order()
	{
		$this->db->group_by('sales.customer_id');
		$this->db->order_by('customer_p.last_name');
	}
}
?>
