<?php
require_once("Summary_report.php");
class Summary_sales extends Summary_report
{
	function __construct()
	{
		parent::__construct();
	}

	public function getDataColumns()
	{
		return array($this->lang->line('reports_date'), $this->lang->line('reports_quantity'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_cost'), $this->lang->line('reports_profit'));
	}

	public function getData(array $inputs)
	{
		$this->commonSelect($inputs);

		$this->db->select('
				DATE(sales.sale_time) AS sale_date, 
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');

		$this->commonFrom();

		$this->commonWhere($inputs);   

		$this->db->group_by('sale_date');
		$this->db->order_by('sale_date');

		return $this->db->get()->result_array();
	}
}
?>