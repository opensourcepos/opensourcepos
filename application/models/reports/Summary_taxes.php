<?php
require_once("Report.php");
class Summary_taxes extends Report
{
	function __construct()
	{
		parent::__construct();

		//Create our temp tables to work with the data in our report
		$this->Sale->create_temp_table();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_tax_percent'), $this->lang->line('reports_count'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'));
	}
	
	public function getData(array $inputs)
	{
		$quantity_cond = '';
		if ($inputs['sale_type'] == 'sales')
		{
			$quantity_cond = 'and quantity_purchased > 0';
		}
		elseif ($inputs['sale_type'] == 'returns')
		{
			$quantity_cond = 'and quantity_purchased < 0';
		}

		if ($inputs['location_id'] != 'all')
		{
			$quantity_cond .= 'and item_location = '. $this->db->escape($inputs['location_id']);
		}

		if ($this->config->item('tax_included'))
		{
			$total    = "1";
			$subtotal = "(100/(100+percent))";
			$tax      = "(1 - (100/(100 +percent)))";
		}
		else
		{
			$tax      = "(percent/100)";
			$total    = "(1+(percent/100))";
			$subtotal = "1";
		}
		
		$decimals = totals_decimals();

		//	Modify by Jorge Colmenarez 2016-11-01 20:31 
		//	Set WHERE Clause with support for DateTime filter field
		$clauseWhere =	"";
		if(empty($inputs['datetime_filter']))
			$clauseWhere = "WHERE date(sale_time) BETWEEN " . $this->db->escape($inputs['start_date']) . " AND " . $this->db->escape($inputs['end_date']);
		else
			$clauseWhere = "WHERE sale_time BETWEEN " . $this->db->escape(str_replace("%20"," ", $inputs['start_date'])) . " AND " . $this->db->escape(str_replace("%20"," ", $inputs['end_date']));

		$clauseWhere.=" ".$quantity_cond;

		$query = $this->db->query("SELECT percent, count(*) AS count, SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax
			FROM (SELECT name, CONCAT(ROUND(percent, $decimals), '%') AS percent,
			ROUND((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100) * $subtotal, $decimals) AS subtotal,
			ROUND((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100) * $total, $decimals) AS total,
			ROUND((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100) * $tax, $decimals) AS tax
			FROM ".$this->db->dbprefix('sales_items_taxes')."
			JOIN ".$this->db->dbprefix('sales_items')." ON "
			.$this->db->dbprefix('sales_items').'.sale_id='.$this->db->dbprefix('sales_items_taxes').'.sale_id'." AND "
			.$this->db->dbprefix('sales_items').'.item_id='.$this->db->dbprefix('sales_items_taxes').'.item_id'." AND "
			.$this->db->dbprefix('sales_items').'.line='.$this->db->dbprefix('sales_items_taxes').'.line'
			." JOIN ".$this->db->dbprefix('sales')." ON ".$this->db->dbprefix('sales_items_taxes').".sale_id=".$this->db->dbprefix('sales').".sale_id
			$clauseWhere) AS temp_taxes
			GROUP BY percent");

		return $query->result_array();
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');
		//	Modify by Jorge Colmenarez 2016-11-01 20:32 
		//	Set DateTime filter field
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