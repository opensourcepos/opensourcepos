<?php
require_once("Report.php");
abstract class Summary_report extends Report
{
	function __construct()
	{
		parent::__construct();
	}

	protected function commonSelect(array $inputs)
	{
		if($this->config->item('tax_included'))
		{
			$total    = '1';
			$subtotal = '(1 - (SUM(1 - 100 / (100 + sales_items_taxes.percent))))';
			$tax      = '(SUM(1 - 100 / (100 + sales_items_taxes.percent)))';
		}
		else
		{
			$tax      = '(SUM(sales_items_taxes.percent) / 100)';
			$total    = '(1 + (SUM(sales_items_taxes.percent / 100)))';
			$subtotal = '1';
		}

		$sale_total = 'SUM(sales_items.item_unit_price * sales_items.quantity_purchased - sales_items.item_unit_price * sales_items.quantity_purchased * sales_items.discount_percent / 100)';
		$sale_cost  = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';

		$decimals = totals_decimals();

		$this->db->select("
				ROUND($sale_total * $subtotal, $decimals) AS subtotal,
				IFNULL(ROUND($sale_total * $total, $decimals), ROUND($sale_total * $subtotal, $decimals)) AS total,
				IFNULL(ROUND($sale_total * $tax, $decimals), 0) AS tax,
				ROUND($sale_cost, $decimals) AS cost,
				ROUND($sale_total - $sale_cost, $decimals) AS profit
		");
	}
	
	protected function commonWhere(array $inputs)
	{
		$this->db->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));

		if($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

		if($inputs['sale_type'] == 'sales')
        {
            $this->db->where('quantity_purchased > 0');
        }
        elseif($inputs['sale_type'] == 'returns')
        {
            $this->db->where('quantity_purchased < 0');
        }
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->commonSelect($inputs);

		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$this->db->join('sales_items_taxes AS sales_items_taxes', 'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line', 'left outer');

		$this->commonWhere($inputs);

		return $this->db->get()->row_array();		
	}
}
?>