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

		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$this->db->join('sales_items_taxes AS sales_items_taxes', 'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line', 'left outer');

		$this->commonWhere($inputs);   
        
		$this->db->group_by('sale_date');
		$this->db->order_by('sale_date');

		return $this->db->get()->result_array();
	}
}
?>