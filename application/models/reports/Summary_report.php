<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

abstract class Summary_report extends Report
{
	/**
	 * Private interface
	 */

	private function _common_select(array $inputs)
	{
		$where = '';

		if(empty($this->config->item('date_or_time_format')))
		{
			$where .= 'DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
		}
		else
		{
			$where .= 'sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}

		$decimals = totals_decimals();

		$sale_price = 'sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100)';
		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';
		$tax = 'IFNULL(SUM(sales_items_taxes.tax), 0)';

		if($this->config->item('tax_included'))
		{
			$sale_total = 'ROUND(SUM(' . $sale_price . '), ' . $decimals . ')';
			$sale_subtotal = $sale_total . ' - ' . $tax;
		}
		else
		{
			$sale_subtotal = 'ROUND(SUM(' . $sale_price . '), ' . $decimals . ')';
			$sale_total = $sale_subtotal . ' + ' . $tax;
		}

		// create a temporary table to contain all the sum of taxes per sale item
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') .
			' (INDEX(sale_id), INDEX(item_id))
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(sales_items_taxes.item_tax_amount) AS tax
				FROM ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)'
		);

		$this->db->select("
				IFNULL($sale_subtotal, $sale_total) AS subtotal,
				$tax AS tax,
				IFNULL($sale_total, $sale_subtotal) AS total,
				$sale_cost AS cost,
				(IFNULL($sale_subtotal, $sale_total) - $sale_cost) AS profit
		");
	}

	private function _common_from()
	{
		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$this->db->join('sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'left outer');
	}

	private function _common_where(array $inputs)
	{
		if(empty($this->config->item('date_or_time_format')))
		{
			$this->db->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$this->db->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}

		if($inputs['location_id'] != 'all')
		{
			$this->db->where('sales_items.item_location', $inputs['location_id']);
		}

		if($inputs['sale_type'] == 'sales')
		{
			$this->db->where('sale_status = ' . COMPLETED . ' and quantity_purchased > 0');
		}
		elseif($inputs['sale_type'] == 'all')
		{
			$this->db->where('sale_status = ' . COMPLETED);
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$this->db->where('sale_status = ' . SUSPENDED . ' and quote_number IS NOT NULL');
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('sale_status = ' . COMPLETED . ' and quantity_purchased < 0');
		}


	}

	/**
	 * Protected class interface implemented by derived classes
	 */

	abstract protected function _get_data_columns();

	protected function _select(array $inputs)	{ $this->_common_select($inputs); }
	protected function _from()					{ $this->_common_from(); }
	protected function _where(array $inputs)	{ $this->_common_where($inputs); }
	protected function _group_order()			{}

	/**
	 * Public interface implementing the base abstract class, in general it should not be extended unless there is a valid reason
	*/

	public function getDataColumns()
	{
		return $this->_get_data_columns();
	}

	public function getData(array $inputs)
	{
		$this->_select($inputs);

		$this->_from();

		$this->_where($inputs);

		$this->_group_order();

		return $this->db->get()->result_array();
	}

	public function getSummaryData(array $inputs)
	{
		$this->_common_select($inputs);

		$this->_common_from();

		$this->_where($inputs);

		return $this->db->get()->row_array();
	}
}
?>
