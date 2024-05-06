<?php

namespace App\Models\Reports;

use App\Models\Sale;

/**
 *
 *
 * @property sale sale
 *
 */
class Specific_supplier extends Report
{
	/**
	 * @param array $inputs
	 * @return void
	 */
	public function create(array $inputs): void
	{
		//Create our temp tables to work with the data in our report
		$sale = model(Sale::class);
		$sale->create_temp_table($inputs);
	}

	/**
	 * @return array[]
	 */
	public function getDataColumns(): array
	{
		return [
			['id' => lang('Reports.sale_id')],
			['type_code' => lang('Reports.code_type')],
			['sale_time' => lang('Reports.date'), 'sortable' => false],
			['name' => lang('Reports.name')],
			['category' => lang('Reports.category')],
			['item_number' => lang('Reports.item_number')],
			['quantity' => lang('Reports.quantity')],
			['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
			['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'],
			['total' => lang('Reports.total'), 'sorter' => 'number_sorter'],
			['cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'],
			['profit' => lang('Reports.profit'), 'sorter' => 'number_sorter'],
			['discount' => lang('Reports.discount')]
		];
	}

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getData(array $inputs): array
	{
		$builder = $this->db->table('sales_items_temp');
		$builder->select('
			sale_id,
			MAX(CASE
			WHEN sale_type = ' . SALE_TYPE_POS . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('Reports.code_pos') . '\'
			WHEN sale_type = ' . SALE_TYPE_INVOICE . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('Reports.code_invoice') . '\'
			WHEN sale_type = ' . SALE_TYPE_WORK_ORDER . ' && sale_status = ' . SUSPENDED . ' THEN \'' . lang('Reports.code_work_order') . '\'
			WHEN sale_type = ' . SALE_TYPE_QUOTE . ' && sale_status = ' . SUSPENDED . ' THEN \'' . lang('Reports.code_quote') . '\'
			WHEN sale_type = ' . SALE_TYPE_RETURN . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('Reports.code_return') . '\'
			WHEN sale_status = ' . CANCELED . ' THEN \'' . lang('Reports.code_canceled') . '\'
			ELSE \'\'
			END) AS type_code,
			MAX(sale_status) as sale_status,
			MAX(sale_time) AS sale_time,
			MAX(name) AS name,
			MAX(category) AS category,
			MAX(item_number) AS item_number,
			SUM(quantity_purchased) AS items_purchased,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(discount_type) AS discount_type,
			MAX(discount) AS discount');

		$builder->where('supplier_id', $inputs['supplier_id']);	//TODO: Duplicated code

		switch($inputs['sale_type'])
		{
			case 'complete':
				$builder->where('sale_status', COMPLETED);
				$builder->groupStart();
				$builder->where('sale_type', SALE_TYPE_POS);
				$builder->orWhere('sale_type', SALE_TYPE_INVOICE);
				$builder->orWhere('sale_type', SALE_TYPE_RETURN);
				$builder->groupEnd();
				break;

			case 'sales':
				$builder->where('sale_status', COMPLETED);
				$builder->groupStart();
				$builder->where('sale_type', SALE_TYPE_POS);
				$builder->orWhere('sale_type', SALE_TYPE_INVOICE);
				$builder->groupEnd();
				break;

			case 'quotes':
				$builder->where('sale_status', SUSPENDED);
				$builder->where('sale_type', SALE_TYPE_QUOTE);
				break;

			case 'work_orders':
				$builder->where('sale_status', SUSPENDED);
				$builder->where('sale_type', SALE_TYPE_WORK_ORDER);
				break;

			case 'canceled':
				$builder->where('sale_status', CANCELED);
				break;

			case 'returns':
				$builder->where('sale_status', COMPLETED);
				$builder->where('sale_type', SALE_TYPE_RETURN);
				break;
		}

		$builder->groupBy(['item_id', 'sale_id']);
		$builder->orderBy('sale_id');

		return $builder->get()->getResultArray();
	}

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getSummaryData(array $inputs): array
	{
		$builder = $this->db->table('sales_items_temp');
		$builder->select('SUM(subtotal) AS subtotal, SUM(tax) AS tax, SUM(total) AS total, SUM(cost) AS cost, SUM(profit) AS profit');
		$builder->where('supplier_id', $inputs['supplier_id']);

		switch($inputs['sale_type'])
		{
			case 'complete':
				$builder->where('sale_status', COMPLETED);
				$builder->groupStart();
				$builder->where('sale_type', SALE_TYPE_POS);
				$builder->orWhere('sale_type', SALE_TYPE_INVOICE);
				$builder->orWhere('sale_type', SALE_TYPE_RETURN);
				$builder->groupEnd();
				break;

			case 'sales':
				$builder->where('sale_status', COMPLETED);
				$builder->groupStart();
				$builder->where('sale_type', SALE_TYPE_POS);
				$builder->orWhere('sale_type', SALE_TYPE_INVOICE);
				$builder->groupEnd();
				break;

			case 'quotes':
				$builder->where('sale_status', SUSPENDED);
				$builder->where('sale_type', SALE_TYPE_QUOTE);
				break;

			case 'work_orders':
				$builder->where('sale_status', SUSPENDED);
				$builder->where('sale_type', SALE_TYPE_WORK_ORDER);
				break;

			case 'canceled':
				$builder->where('sale_status', CANCELED);
				break;

			case 'returns':
				$builder->where('sale_status', COMPLETED);
				$builder->where('sale_type', SALE_TYPE_RETURN);
				break;
		}

		return $builder->get()->getRowArray();
	}
}
