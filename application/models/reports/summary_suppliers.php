<?php
require_once("report.php");
class Summary_suppliers extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_supplier'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'));
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('CONCAT(first_name, " ",last_name) as supplier, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax,sum(profit) as profit', false);
		$this->db->from('sales_items_temp');
		$this->db->join('suppliers', 'suppliers.person_id = sales_items_temp.supplier_id');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
		$this->db->where('sale_date BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		if ($inputs['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($inputs['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		$this->db->group_by('supplier_id');
		$this->db->order_by('last_name');
		
		return $this->db->get()->result_array();
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit');
		$this->db->from('sales_items_temp');
		$this->db->join('suppliers', 'suppliers.person_id = sales_items_temp.supplier_id');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
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