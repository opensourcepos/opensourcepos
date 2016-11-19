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
		$where = "";
		if(empty($this->config->item('filter_datetime_format')))
		{
			$where .= ' WHERE DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']).' ';
		}
		else
		{
			$where .= ' WHERE sale_time BETWEEN ' . $this->db->escape(str_replace('%20',' ', $inputs['start_date'])) . ' AND ' . $this->db->escape(str_replace('%20',' ', $inputs['end_date'])).' ';
		}
		// create a temporary table to contain all the payment types and amount
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') . 
			' (INDEX(sale_id), INDEX(item_id))
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					SUM(sales_items_taxes.percent) AS percent
				FROM ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line 
				'. $where .'
				GROUP BY sales_items_taxes.sale_id, sales_items_taxes.item_id
			)'
		);

		if($this->config->item('tax_included'))
		{
			$sale_total = 'SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100))';
			$sale_subtotal = 'SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100) * (100 / (100 + sales_items_taxes.percent)))';
			$sale_tax = 'SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100) * (1 - 100 / (100 + sales_items_taxes.percent)))';
		}
		else
		{
			$sale_total = 'SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100) * (1 + (sales_items_taxes.percent / 100)))';
			$sale_subtotal = 'SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100))';
			$sale_tax = 'SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100) * (sales_items_taxes.percent / 100))';
		}

		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';

		$decimals = totals_decimals();

		$this->db->select("
				ROUND($sale_subtotal, $decimals) AS subtotal,
				IFNULL(ROUND($sale_total, $decimals), ROUND($sale_subtotal, $decimals)) AS total,
				IFNULL(ROUND($sale_tax, $decimals), 0) AS tax,
				ROUND($sale_cost, $decimals) AS cost,
				ROUND($sale_total - IFNULL($sale_tax, 0) - $sale_cost, $decimals) AS profit
		");
	}

	protected function commonFrom()
	{
		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$this->db->join('sales_items_taxes_temp AS sales_items_taxes', 'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id', 'left outer');
	}

	protected function commonWhere(array $inputs)
	{
		if(empty($this->config->item('filter_datetime_format')))
		{
			$this->db->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$this->db->where('sales.sale_time BETWEEN ' . $this->db->escape(str_replace('%20',' ', $inputs['start_date'])) . ' AND ' . $this->db->escape(str_replace('%20',' ', $inputs['end_date'])));
		}

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

		$this->commonFrom();

		$this->commonWhere($inputs);

		return $this->db->get()->row_array();		
	}
}
?>