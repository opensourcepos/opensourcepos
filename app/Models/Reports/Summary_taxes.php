<?php

namespace App\Models\Reports;

class Summary_taxes extends Summary_report
{
	protected function _get_data_columns(): array	//TODO: hungarian notation
	{
		return [
			['tax_name' => lang('Reports.tax_name'), 'sortable' => FALSE],
			['tax_percent' => lang('Reports.tax_percent'), 'sorter' => 'number_sorter'],
			['report_count' => lang('Reports.sales'), 'sorter' => 'number_sorter'],
			['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
			['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'],
			['total' => lang('Reports.total'), 'sorter' => 'number_sorter']
		];
	}

	protected function _where(array $inputs, &$builder): void	//TODO: hungarian notation
	{
		$config = config(OSPOS::class)->settings;

		$builder->where('sales.sale_status', COMPLETED);

		if(empty($config['date_or_time_format']))
		{
			$builder->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$builder->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}
	}

	public function getData(array $inputs): array
	{
		$config = config(OSPOS::class)->settings;

		$where = 'WHERE sale_status = ' . COMPLETED . ' ';	//TODO: Duplicated code

		if(empty($config['date_or_time_format']))	//TODO: Ternary notation
		{
			$where .= 'AND DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
		}
		else
		{
			$where .= 'AND sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}
		$decimals = totals_decimals();

		if($config['tax_included'])
		{
			$sale_total = '(CASE WHEN sales_items.discount_type = ' . PERCENT
				. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals)"
				. ' ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END)';

			$sale_subtotal = '(CASE WHEN sales_items.discount_type = ' . PERCENT
				. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals) "
				. 'ELSE sales_items.quantity_purchased * sales_items.item_unit_price - sales_items.discount END * (100 / (100 + sales_items_taxes.percent)))';
		}
		else
		{
			$sale_total = '(CASE WHEN sales_items.discount_type = ' . PERCENT
				. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals)"
				. ' ELSE sales_items.quantity_purchased * sales_items.item_unit_price - sales_items.discount END * (1 + (sales_items_taxes.percent / 100)))';

			$sale_subtotal = '(CASE WHEN sales_items.discount_type = ' . PERCENT
				. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals)"
				. ' ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END)';
		}

		$query = $this->db->query("SELECT name as name, percent, COUNT(DISTINCT sale_id) AS count, ROUND(SUM(subtotal), $decimals) AS subtotal, ROUND(SUM(tax), $decimals) AS tax, ROUND(SUM(total), $decimals) AS total
			FROM (
				SELECT
					name AS name,
					CONCAT(IFNULL(ROUND(percent, $decimals), 0), '%') AS percent,
					sales.sale_id AS sale_id,
					$sale_subtotal AS subtotal,
					IFNULL(sales_items_taxes.item_tax_amount, 0) AS tax,
					IFNULL($sale_total, $sale_subtotal) AS total
					FROM " . $this->db->prefixTable('sales_items') . ' AS sales_items
					INNER JOIN ' . $this->db->prefixTable('sales') . ' AS sales
						ON sales_items.sale_id = sales.sale_id
					LEFT OUTER JOIN ' . $this->db->prefixTable('sales_items_taxes') . ' AS sales_items_taxes
						ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line
					' . $where . '
				) AS temp_taxes
			GROUP BY percent'
		);

		return $query->getResultArray();
	}
}
