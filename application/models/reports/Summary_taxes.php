<?php
require_once("Summary_report.php");
class Summary_taxes extends Summary_report
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
			$quantity_cond = 'AND quantity_purchased > 0';
		}
		elseif ($inputs['sale_type'] == 'returns')
		{
			$quantity_cond = 'AND quantity_purchased < 0';
		}

		if ($inputs['location_id'] != 'all')
		{
			$quantity_cond .= 'AND item_location = '. $this->db->escape($inputs['location_id']);
		}

		if ($this->config->item('tax_included'))
		{
			$total    = '1';
			$subtotal = '(100/(100+percent))';
			$tax      = '(1 - (100/(100 +percent)))';
		}
		else
		{
			$tax      = '(percent/100)';
			$total    = '(1+(percent/100))';
			$subtotal = '1';
		}

		$decimals = totals_decimals();

		$query = $this->db->query("SELECT percent, count(*) AS count, SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax
			FROM (
				SELECT
					CONCAT(ROUND(percent, $decimals), '%') AS percent,
					ROUND((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100) * $subtotal, $decimals) AS subtotal,
					ROUND((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100) * $total, $decimals) AS total,
					ROUND((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent /100) * $tax, $decimals) AS tax
					FROM ".$this->db->dbprefix('sales_items_taxes')."
					JOIN ".$this->db->dbprefix('sales_items')." ON "
						.$this->db->dbprefix('sales_items').'.sale_id='.$this->db->dbprefix('sales_items_taxes').'.sale_id'." AND "
						.$this->db->dbprefix('sales_items').'.item_id='.$this->db->dbprefix('sales_items_taxes').'.item_id'." AND "
						.$this->db->dbprefix('sales_items').'.line='.$this->db->dbprefix('sales_items_taxes').'.line'." 
					JOIN ".$this->db->dbprefix('sales')." ON ".$this->db->dbprefix('sales_items_taxes').".sale_id=".$this->db->dbprefix('sales').".sale_id
					WHERE date(sale_time) BETWEEN " . $this->db->escape($inputs['start_date']) . " AND " . $this->db->escape($inputs['end_date']) . " $quantity_cond) AS temp_taxes
					GROUP BY percent");

		return $query->result_array();
	}
}
?>