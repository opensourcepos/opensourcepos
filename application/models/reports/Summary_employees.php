<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_employees extends Summary_report
{
	protected function get_data_columns()
	{
		return array(
			array('employee_name' => $this->lang->line('reports_employee')),
			array('sales' => $this->lang->line('reports_sales'), 'sorter' => 'number_sorter'),
			array('quantity' => $this->lang->line('reports_quantity'), 'sorter' => 'number_sorter'),
			array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
			array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
			array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
			array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'));
	}

	protected function select(array $inputs)
	{
		parent::select($inputs);

		$this->db->select('
				MAX(CONCAT(employee_p.first_name, " ", employee_p.last_name)) AS employee,
				SUM(sales_items.quantity_purchased) AS quantity_purchased,
				COUNT(DISTINCT sales.sale_id) AS sales
		');
	}

	protected function from()
	{
		parent::from();

		$this->db->join('people AS employee_p', 'sales.employee_id = employee_p.person_id');
	}

	protected function group_order()
	{
		$this->db->group_by('sales.employee_id');
		$this->db->order_by('employee_p.last_name');
	}
}
?>
