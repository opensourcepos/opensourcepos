<?php

namespace App\Models\Reports;

use Config\OSPOS;

abstract class Summary_report extends Report
{
	/**
	 * Private interface implementing the core basic functionality for all reports
	 */
	private function __common_select(array $inputs, &$builder): void	//TODO: Hungarian notation
	{
		$config = config(OSPOS::class)->settings;
		$where = '';	//TODO: Duplicated code

		if(empty($config['date_or_time_format']))
		{
			$where .= 'DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
		}
		else
		{
			$where .= 'sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}

		$decimals = totals_decimals();

		$sale_price = 'CASE WHEN sales_items.discount_type = ' . PERCENT
			. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals) "
			. 'ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END';

		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';
		$sales_tax = "IFNULL(SUM(sales_items_taxes.tax), 0)";

		$cash_adjustment = 'IFNULL(SUM(payments.sale_cash_adjustment), 0)';


		if($config['tax_included'])
		{
			$sale_total = "ROUND(SUM($sale_price), $decimals) + $cash_adjustment";
			$sale_subtotal = "$sale_total - $sales_tax";

		}
		else
		{
			$sale_subtotal = "ROUND(SUM($sale_price), $decimals) + $cash_adjustment";
			$sale_total = "ROUND(SUM($sale_price), $decimals) + $sales_tax + $cash_adjustment";
		}

		// create a temporary table to contain all the sum of taxes per sale item
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sales_items_taxes_temp') .
			' (INDEX(sale_id), INDEX(item_id)) ENGINE=MEMORY
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(ROUND(sales_items_taxes.item_tax_amount,' . $decimals . ')) AS tax
				FROM ' . $this->db->prefixTable('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->prefixTable('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->prefixTable('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)'
		);

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sales_payments_temp') .
			' (PRIMARY KEY(sale_id), INDEX(sale_id))
			(
				SELECT payments.sale_id AS sale_id,
					SUM(CASE WHEN payments.cash_adjustment = 0 THEN payments.payment_amount ELSE 0 END) AS sale_payment_amount,
					SUM(CASE WHEN payments.cash_adjustment = 1 THEN payments.payment_amount ELSE 0 END) AS sale_cash_adjustment,
					SUM(payments.cash_refund) AS sale_cash_refund,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", (payments.payment_amount - payments.cash_refund)) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->prefixTable('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->prefixTable('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE ' . $where . '
				GROUP BY sale_id
			)'
		);

//TODO: Probably going to need to rework these since you can't reference $builder without it's instantiation.
		$builder->select("
				IFNULL($sale_subtotal, $sale_total) AS subtotal,
				$sales_tax AS tax,
				IFNULL($sale_total, $sale_subtotal) AS total,
				$sale_cost AS cost,
				(IFNULL($sale_subtotal, $sale_total) - $sale_cost) AS profit
		");
	}

	private function __common_from(): void	//TODO: hungarian notation
	{
		$builder = $this->db->table('sales_items AS sales_items');
		$builder->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$builder->join('sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'left outer');
		$builder->join('sales_payments_temp AS payments', 'sales.sale_id = payments.sale_id', 'LEFT OUTER');
	}

	private function __common_where(array $inputs, &$builder): void
	{
		$config = config(OSPOS::class)->settings;

		//TODO: Probably going to need to rework these since you can't reference $builder without it's instantiation.
		if(empty($config['date_or_time_format']))	//TODO: Duplicated code
		{
			$builder->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$builder->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}

		if($inputs['location_id'] != 'all')
		{
			$builder->where('sales_items.item_location', $inputs['location_id']);
		}

		if($inputs['sale_type'] == 'complete')
		{
			$builder->where('sales.sale_status', COMPLETED);
			$builder->groupStart();
				$builder->where('sales.sale_type', SALE_TYPE_POS);
				$builder->orWhere('sales.sale_type', SALE_TYPE_INVOICE);
				$builder->orWhere('sales.sale_type', SALE_TYPE_RETURN);
			$builder->groupEnd();
		}
		elseif($inputs['sale_type'] == 'sales')
		{
			$builder->where('sales.sale_status', COMPLETED);
			$builder->groupStart();
				$builder->where('sales.sale_type', SALE_TYPE_POS);
				$builder->orWhere('sales.sale_type', SALE_TYPE_INVOICE);
			$builder->groupEnd();
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$builder->where('sales.sale_status', SUSPENDED);
			$builder->where('sales.sale_type', SALE_TYPE_QUOTE);
		}
		elseif($inputs['sale_type'] == 'work_orders')
		{
			$builder->where('sales.sale_status', SUSPENDED);
			$builder->where('sales.sale_type', SALE_TYPE_WORK_ORDER);
		}
		elseif($inputs['sale_type'] == 'canceled')
		{
			$builder->where('sales.sale_status', CANCELED);
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$builder->where('sales.sale_status', COMPLETED);
			$builder->where('sales.sale_type', SALE_TYPE_RETURN);
		}
	}

	/**
	 * Protected class interface implemented by derived classes where required
	 */
	abstract protected function _get_data_columns(): array;	//TODO: hungarian notation

	protected function _select(array $inputs, object &$builder): void { $this->__common_select($inputs, $builder); }	//TODO: hungarian notation
	protected function _from(object &$builder): void { $this->__common_from(); }	//TODO: hungarian notation TODO: Do we need to pass &$builder to the __common_from()?
	protected function _where(array $inputs, object &$builder): void { $this->__common_where($inputs, $builder); }	//TODO: hungarian notation
	protected function _group_order(object &$builder): void {}	//TODO: hungarian notation

	/**
	 * Public interface implementing the base abstract class,
	 * in general it should not be extended unless there is a valid reason
	 * like a non sale report (e.g. expenses)
	 */

	public function getDataColumns(): array
	{
		return $this->_get_data_columns();
	}

	public function getData(array $inputs): array
	{
		$this->_select($inputs, $builder);
		$this->_from($builder);
		$this->_where($inputs, $builder);
		$this->_group_order($builder);

		return $builder->get()->getResultArray();
	}

	public function getSummaryData(array $inputs): array
	{
//TODO: Probably going to need to rework these since you can't reference $builder without it's instantiation.
		$this->__common_select($inputs, $builder);
		$this->__common_from();
		$this->_where($inputs, $builder);

		return $builder->get()->getRowArray();
	}
}
