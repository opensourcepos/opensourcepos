<?php
require_once("Report.php");
class Detailed_sales extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array(
			'summary' => array(
				'id' => $this->lang->line('reports_sale_id'),
				'sale_date' => $this->lang->line('reports_date'),
				'quantity' => $this->lang->line('reports_quantity'),
				'employee' => $this->lang->line('reports_sold_by'),
				'customer' => $this->lang->line('reports_sold_to'),
				'subtotal' => $this->lang->line('reports_subtotal'),
				'total' => $this->lang->line('reports_total'),
				'tax' => $this->lang->line('reports_tax'),
				'cost' => $this->lang->line('reports_cost'),
				'profit' => $this->lang->line('reports_profit'),
				'payment_type' => $this->lang->line('sales_amount_tendered'),
				'comment' => $this->lang->line('reports_comments'),
				'edit' => ''),
			 'details' => array(
				 $this->lang->line('reports_name'),
				 $this->lang->line('reports_category'),
				 $this->lang->line('reports_serial_number'),
				 $this->lang->line('reports_description'),
				 $this->lang->line('reports_quantity'),
				 $this->lang->line('reports_subtotal'),
				 $this->lang->line('reports_total'),
				 $this->lang->line('reports_tax'),
				 $this->lang->line('reports_cost'),
				 $this->lang->line('reports_profit'),
				 $this->lang->line('reports_discount'))
		);		
	}
	
	public function getDataBySaleId($sale_id)
	{
		$this->db->select('sale_id, sale_date, SUM(quantity_purchased) AS items_purchased, employee_name, customer_name, SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, SUM(profit) AS profit, payment_type, comment');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row_array();
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('sale_id, sale_date, SUM(quantity_purchased) AS items_purchased, employee_name, customer_name, SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, SUM(profit) AS profit, payment_type, comment');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_date BETWEEN '. $this->db->escape($inputs['start_date']). ' AND '. $this->db->escape($inputs['end_date']));

		if ($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

		if ($inputs['sale_type'] == 'sales')
        {
            $this->db->where('quantity_purchased > 0');
        }
        elseif ($inputs['sale_type'] == 'returns')
        {
            $this->db->where('quantity_purchased < 0');
        }

		$this->db->group_by('sale_id');
		$this->db->order_by('sale_date');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('name, category, quantity_purchased, item_location, serialnumber, description, subtotal, total, tax, cost, profit, discount_percent');
			$this->db->from('sales_items_temp');
			$this->db->where('sale_id', $value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		return $data;
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_date BETWEEN '. $this->db->escape($inputs['start_date']). ' AND '. $this->db->escape($inputs['end_date']));

		if ($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

		if ($inputs['sale_type'] == 'sales')
        {
            $this->db->where('quantity_purchased > 0');
        }
        elseif ($inputs['sale_type'] == 'returns')
        {
            $this->db->where('quantity_purchased < 0');
        }

		return $this->db->get()->row_array();
	}
}
?>