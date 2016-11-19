<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_sales extends Summary_report
{
	function __construct()
	{
		parent::__construct();
	}

	protected function _get_data_columns()
	{
		return array($this->lang->line('reports_date'), $this->lang->line('reports_quantity'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_cost'), $this->lang->line('reports_profit'));
	}

	protected function _select(array $inputs)
	{
		parent::_select($inputs);

		$this->db->select('
				MAX(DATE(sales.sale_time)) AS sale_date, 
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');
	}
	
	protected function _group_order()
	{
		$this->db->group_by('sale_date');
		$this->db->order_by('sale_date');
	}
}
?>