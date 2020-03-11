<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_taxes extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('tax_percent' => $this->lang->line('reports_tax_percent'), 'sorter' => 'number_sorter'),
			array('report_count' => $this->lang->line('reports_count')),
			array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
			array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'));
	}

	protected function _where(array $inputs)
	{
		$this->db->where('sales.sale_status', COMPLETED);

		if(empty($this->config->item('date_or_time_format')))
		{
			$this->db->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$this->db->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}
	}

	public function getData(array $inputs)
	{
		$where = 'WHERE sale_status = ' . COMPLETED . ' ';

		if(empty($this->config->item('date_or_time_format')))
		{
			$where .= 'AND DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
		}
		else
		{
			$where .= 'AND sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}

		if($this->config->item('tax_included'))
		{
			$sale_total = '(CASE WHEN sales_items.discount_type = ' . PERCENT . ' THEN sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount / 100) ELSE sales_items.item_unit_price * sales_items.quantity_purchased - sales_items.discount END)';
			$sale_subtotal = '(CASE WHEN sales_items.discount_type = ' . PERCENT . ' THEN sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount / 100) ELSE sales_items.item_unit_price * sales_items.quantity_purchased - sales_items.discount END * (100 / (100 + sales_items_taxes.percent)))';
		}
		else
		{
			$sale_total = '(CASE WHEN sales_items.discount_type = ' . PERCENT . ' THEN sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount / 100) ELSE sales_items.item_unit_price * sales_items.quantity_purchased - sales_items.discount END * (1 + (sales_items_taxes.percent / 100)))';
			$sale_subtotal = '(CASE WHEN sales_items.discount_type = ' . PERCENT . ' THEN sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount / 100) ELSE sales_items.item_unit_price * sales_items.quantity_purchased - sales_items.discount END)';
		}

		$decimals = totals_decimals();

		$query = $this->db->query("SELECT percent, count(*) AS count, ROUND(SUM(subtotal), $decimals) AS subtotal, ROUND(SUM(tax), $decimals) AS tax, ROUND(SUM(total), $decimals) AS total
			FROM (
				SELECT
					CONCAT(IFNULL(ROUND(percent, $decimals), 0), '%') AS percent,
					$sale_subtotal AS subtotal,
					IFNULL(sales_items_taxes.item_tax_amount, 0) AS tax,
					IFNULL($sale_total, $sale_subtotal) AS total
					FROM " . $this->db->dbprefix('sales_items') . ' AS sales_items
					INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
						ON sales_items.sale_id = sales.sale_id
					LEFT OUTER JOIN ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
						ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line
					' . $where . '
				) AS temp_taxes
			GROUP BY percent'
		);

		return $query->result_array();
	}
}
?>
