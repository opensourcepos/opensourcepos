<?php
require_once("report.php");
class Summary_taxes extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_tax_percent'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'));
	}
	
	public function getData(array $inputs)
	{
		
		$quanitity_cond = '';
		if ($inputs['sale_type'] == 'sales')
		{
			$quanitity_cond = 'and quantity_purchased > 0';
		}
		elseif ($inputs['sale_type'] == 'returns')
		{
			$quanitity_cond = 'and quantity_purchased < 0';
		}
		
		$query = $this->db->query("SELECT percent, SUM(subtotal) as subtotal, sum(total) as total, sum(tax) as tax 
		FROM (SELECT name, CONCAT( percent,  '%' ) AS percent, (
		item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100
		) AS subtotal, ROUND( (
		item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100
		) * ( 1 + ( percent /100 ) ) , 2 ) AS total, ROUND( (
		item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100
		) * ( percent /100 ) , 2 ) AS tax
		FROM ".$this->db->dbprefix('sales_items_taxes')."
		JOIN ".$this->db->dbprefix('sales_items')." ON "
		.$this->db->dbprefix('sales_items').'.sale_id='.$this->db->dbprefix('sales_items_taxes').'.sale_id'." and "
		.$this->db->dbprefix('sales_items').'.item_id='.$this->db->dbprefix('sales_items_taxes').'.item_id'." and "
		.$this->db->dbprefix('sales_items').'.line='.$this->db->dbprefix('sales_items_taxes').'.line'
		." JOIN ".$this->db->dbprefix('sales')." ON ".$this->db->dbprefix('sales_items_taxes').".sale_id=".$this->db->dbprefix('sales').".sale_id
		WHERE date(sale_time) BETWEEN '".$inputs['start_date']."' and '".$inputs['end_date']."' $quanitity_cond) as temp_taxes
		GROUP BY percent");
		return $query->result_array();
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit');
		$this->db->from('sales_items_temp');
		$this->db->join('items', 'sales_items_temp.item_id = items.item_id');
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