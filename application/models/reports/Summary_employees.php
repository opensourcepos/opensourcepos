<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_employees extends Summary_report
{
	function __construct()
	{
		parent::__construct();
	}
	
	protected function _get_data_columns()
	{
		return array($this->lang->line('reports_employee'), $this->lang->line('reports_quantity'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_cost'), $this->lang->line('reports_profit'));
	}

	protected function _select(array $inputs)
	{
		parent::_select($inputs);

		$this->db->select('
				MAX(CONCAT(employee_p.first_name, " ", employee_p.last_name)) AS employee,
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');
	}

	protected function _from()
	{
		parent::_from();

		$this->db->join('people AS employee_p', 'sales.employee_id = employee_p.person_id');
	}

	protected function _group_order()
	{
		$this->db->group_by('sales.employee_id');
		$this->db->order_by('employee_p.last_name');
	}
}
?>