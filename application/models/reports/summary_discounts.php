<?php
require_once("report.php");
class Summary_discounts extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_discount_percent'),$this->lang->line('reports_count'));
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('CONCAT(discount_percent, "%") as discount_percent, count(*) as count', false);
		$this->db->from('sales_items_temp');
		$this->db->where('sale_date BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'" and discount_percent > 0');
		if ($inputs['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($inputs['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		$this->db->group_by('sales_items_temp.discount_percent');
		$this->db->order_by('discount_percent');
		return $this->db->get()->result_array();		
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax,sum(profit) as profit');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_date BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
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