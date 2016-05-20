<?php
require_once("Report.php");
class Summary_taxes extends Report
{
	function __construct()
	{
		parent::__construct();
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

		if ($this->config->item('tax_included'))
		{
			$total = "1";
			$subtotal = "(100/(100+percent))";
			$tax="(1 - (100/(100 +percent)))";
		}
		else
		{
			$tax = "(percent/100)";
			$total = "(1+(percent/100))";
			$subtotal = "1";
		}
		
		$decimals = totals_decimals();

		$query = $this->db->query("SELECT percent, count(*) as count, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax
		FROM (SELECT name, CONCAT(ROUND(percent, $decimals), '%') AS percent,
		ROUND((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100) * $subtotal, $decimals) AS subtotal,
		ROUND((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100) * $total, $decimals) AS total,
		ROUND((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100) * $tax, $decimals) AS tax
		FROM ".$this->db->dbprefix('sales_items_taxes')."
		JOIN ".$this->db->dbprefix('sales_items')." ON "
		.$this->db->dbprefix('sales_items').'.sale_id='.$this->db->dbprefix('sales_items_taxes').'.sale_id'." and "
		.$this->db->dbprefix('sales_items').'.item_id='.$this->db->dbprefix('sales_items_taxes').'.item_id'." and "
		.$this->db->dbprefix('sales_items').'.line='.$this->db->dbprefix('sales_items_taxes').'.line'
		." JOIN ".$this->db->dbprefix('sales')." ON ".$this->db->dbprefix('sales_items_taxes').".sale_id=".$this->db->dbprefix('sales').".sale_id
		WHERE date(sale_time) BETWEEN '".$inputs['start_date']."' and '".$inputs['end_date']."' $quantity_cond) as temp_taxes
		GROUP BY percent");

		return $query->result_array();
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(cost) as cost, sum(profit) as profit');
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