<?php
require_once("Summary_report.php");
class Summary_customers extends Summary_report
{
	function __construct()
	{
		parent::__construct();
	}

	public function getDataColumns()
	{
		return array($this->lang->line('reports_customer'), $this->lang->line('reports_quantity'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_cost'), $this->lang->line('reports_profit'));
	}

	public function getData(array $inputs)
	{
		$this->commonSelect($inputs);

		$this->db->select('
				CONCAT(customer_p.first_name, " ", customer_p.last_name) AS customer,
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');

		$this->commonFrom();

		$this->db->join('people AS customer_p', 'sales.customer_id = customer_p.person_id');

		$this->commonWhere($inputs);

		$this->db->group_by('sales.customer_id');
		$this->db->order_by('customer_p.last_name');

		return $this->db->get()->result_array();		
	}
}
?>