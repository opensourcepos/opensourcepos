<?php
require_once("Report.php");
class Summary_discounts extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_discount_percent'), $this->lang->line('reports_count'));
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('CONCAT(discount_percent, "%") AS discount_percent, count(*) AS count');
		$this->db->from('sales_items_temp');
		$this->db->where("sale_date BETWEEN " . $this->db->escape($inputs['start_date']) . " AND " . $this->db->escape($inputs['end_date']));
		$this->db->where('discount_percent > 0');

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
		
		$this->db->group_by('sales_items_temp.discount_percent');
		$this->db->order_by('discount_percent');

		return $this->db->get()->result_array();		
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');
		$this->db->where("sale_date BETWEEN " . $this->db->escape($inputs['start_date']) . " AND " . $this->db->escape($inputs['end_date']));

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