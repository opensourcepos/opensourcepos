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
				'payment_type' => $this->lang->line('reports_payment_type'),
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
		$this->db->select('sale_id, DATE_FORMAT(sale_time, "%d-%m-%Y") AS sale_date, sum(quantity_purchased) as items_purchased, CONCAT(employee.first_name, " ", employee.last_name) as employee_name, CONCAT(customer.first_name," ",customer.last_name) as customer_name, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(cost) as cost, sum(profit) as profit, payment_type, comment', false);
		$this->db->from('sales_items_temp');
		$this->db->join('people as employee', 'sales_items_temp.employee_id = employee.person_id');
		$this->db->join('people as customer', 'sales_items_temp.customer_id = customer.person_id', 'left');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row_array();
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('sale_id, sale_date, sum(quantity_purchased) as items_purchased, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(customer.first_name," ",customer.last_name) as customer_name, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(cost) as cost, sum(profit) as profit, payment_type, comment', false);
		$this->db->from('sales_items_temp');
		$this->db->join('people as employee', 'sales_items_temp.employee_id = employee.person_id');
		$this->db->join('people as customer', 'sales_items_temp.customer_id = customer.person_id', 'left');
		$this->db->where('sale_date BETWEEN '. $this->db->escape($inputs['start_date']). ' and '. $this->db->escape($inputs['end_date']));

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
			$this->db->select('name, category, quantity_purchased, item_location, serialnumber, sales_items_temp.description, subtotal, total, tax, cost, profit, discount_percent');
			$this->db->from('sales_items_temp');
			$this->db->join('items', 'sales_items_temp.item_id = items.item_id');
			$this->db->where('sale_id', $value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		return $data;
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(cost) as cost, sum(profit) as profit');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_date BETWEEN '. $this->db->escape($inputs['start_date']). ' and '. $this->db->escape($inputs['end_date']));

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