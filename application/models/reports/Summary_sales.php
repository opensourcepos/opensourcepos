<?php
require_once("Report.php");
class Summary_sales extends Report
{
	function __construct()
	{
		parent::__construct();

		//Create our temp tables to work with the data in our report
		$this->Sale->create_temp_table();
	}

	public function getDataColumns()
	{
		return array($this->lang->line('reports_date'), $this->lang->line('reports_quantity'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_cost'), $this->lang->line('reports_profit'));
	}
	
	public function getData(array $inputs)
	{		
		$this->db->select('sale_date, SUM(quantity_purchased) AS quantity_purchased, SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');

		if(empty($inputs['datetime_filter']))
			$this->db->where("sale_date BETWEEN " . $this->db->escape($inputs['start_date']) . " AND " . $this->db->escape($inputs['end_date']));
		else
			$this->db->where("sale_time BETWEEN " . $this->db->escape(str_replace("%20"," ", $inputs['start_date'])) . " AND " . $this->db->escape(str_replace("%20"," ", $inputs['end_date'])));

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
        
		$this->db->group_by('sale_date');
		$this->db->order_by('sale_date');

		return $this->db->get()->result_array();
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');
		
		if(empty($inputs['datetime_filter']))
			$this->db->where("sale_date BETWEEN " . $this->db->escape($inputs['start_date']) . " AND " . $this->db->escape($inputs['end_date']));
		else
			$this->db->where("sale_time BETWEEN " . $this->db->escape(str_replace("%20"," ", $inputs['start_date'])) . " AND " . $this->db->escape(str_replace("%20"," ", $inputs['end_date'])));

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