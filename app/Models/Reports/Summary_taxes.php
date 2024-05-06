<?php

namespace App\Models\Reports;

use Config\OSPOS;

class Summary_taxes extends Summary_report
{
	private array $config;

	public function __construct()
	{
		parent::__construct();
		$this->config = config(OSPOS::class)->settings;
	}

	/**
	 * @return array[]
	 */
	protected function _get_data_columns(): array	//TODO: hungarian notation
	{
		return [
			['tax_name' => lang('Reports.tax_name'), 'sortable' => false],
			['tax_percent' => lang('Reports.tax_percent'), 'sorter' => 'number_sorter'],
			['report_count' => lang('Reports.sales'), 'sorter' => 'number_sorter'],
			['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
			['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'],
			['total' => lang('Reports.total'), 'sorter' => 'number_sorter']
		];
	}

	/**
	 * @param array $inputs
	 * @param $builder
	 * @return void
	 */
	protected function _where(array $inputs, &$builder): void	//TODO: hungarian notation
	{
		$builder->where('sales.sale_status', COMPLETED);

		if(empty($this->config['date_or_time_format']))
		{
			$builder->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$builder->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}
	}

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getData(array $inputs): array
	{
		$decimals = totals_decimals();
		$db_prefix = $this->db->getPrefix();

		if($this->config['tax_included'])
		{
			$sale_total = '(CASE WHEN ' . $db_prefix . 'sales_items.discount_type = ' . PERCENT
				. " THEN " . $db_prefix . "sales_items.quantity_purchased * " . $db_prefix . "sales_items.item_unit_price - ROUND(" . $db_prefix . "sales_items.quantity_purchased * " . $db_prefix . "sales_items.item_unit_price * " . $db_prefix . "sales_items.discount / 100, $decimals)"
				. ' ELSE ' . $db_prefix . 'sales_items.quantity_purchased * (' . $db_prefix . 'sales_items.item_unit_price - ' . $db_prefix . 'sales_items.discount) END)';

			$sale_subtotal = '(CASE WHEN ' . $db_prefix . 'sales_items.discount_type = ' . PERCENT
				. " THEN " . $db_prefix . "sales_items.quantity_purchased * " . $db_prefix . "sales_items.item_unit_price - ROUND(" . $db_prefix . "sales_items.quantity_purchased * " . $db_prefix . "sales_items.item_unit_price * " . $db_prefix . "sales_items.discount / 100, $decimals) "
				. 'ELSE ' . $db_prefix . 'sales_items.quantity_purchased * ' . $db_prefix . 'sales_items.item_unit_price - ' . $db_prefix . 'sales_items.discount END * (100 / (100 + ' . $db_prefix . 'sales_items_taxes.percent)))';
		}
		else
		{
			$sale_total = '(CASE WHEN ' . $db_prefix . 'sales_items.discount_type = ' . PERCENT
				. " THEN " . $db_prefix . "sales_items.quantity_purchased * " . $db_prefix . "sales_items.item_unit_price - ROUND(" . $db_prefix . "sales_items.quantity_purchased * " . $db_prefix . "sales_items.item_unit_price * " . $db_prefix . "sales_items.discount / 100, $decimals)"
				. ' ELSE ' . $db_prefix . 'sales_items.quantity_purchased * ' . $db_prefix . 'sales_items.item_unit_price - ' . $db_prefix . 'sales_items.discount END * (1 + (' . $db_prefix . 'sales_items_taxes.percent / 100)))';

			$sale_subtotal = '(CASE WHEN ' . $db_prefix . 'sales_items.discount_type = ' . PERCENT
				. " THEN " . $db_prefix . "sales_items.quantity_purchased * " . $db_prefix . "sales_items.item_unit_price - ROUND(" . $db_prefix . "sales_items.quantity_purchased * " . $db_prefix . "sales_items.item_unit_price * " . $db_prefix . "sales_items.discount / 100, $decimals)"
				. ' ELSE ' . $db_prefix . 'sales_items.quantity_purchased * (' . $db_prefix . 'sales_items.item_unit_price - ' . $db_prefix . 'sales_items.discount) END)';
		}

		$subquery_builder = $this->db->table('sales_items');
		$subquery_builder->select("name AS name, CONCAT(IFNULL(ROUND(percent, $decimals), 0), '%') AS percent, sales.sale_id AS sale_id, $sale_subtotal AS subtotal, IFNULL($db_prefix"."sales_items_taxes.item_tax_amount, 0) AS tax, IFNULL($sale_total, $sale_subtotal) AS total");

		$subquery_builder->join('sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$subquery_builder->join('sales_items_taxes', 'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line', 'left outer');

		$subquery_builder->where('sale_status', COMPLETED);

		if(empty($this->config['date_or_time_format']))
		{
			$subquery_builder->where('DATE(' . $db_prefix . 'sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$subquery_builder->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}

		$builder = $this->db->newQuery()->fromSubquery($subquery_builder, 'temp_taxes');
		$builder->select("name, percent, COUNT(DISTINCT sale_id) AS count, ROUND(SUM(subtotal), $decimals) AS subtotal, ROUND(SUM(tax), $decimals) AS tax, ROUND(SUM(total), $decimals) total");
		$builder->groupBy('percent, name');

		return $builder->get()->getResultArray();
	}
}
