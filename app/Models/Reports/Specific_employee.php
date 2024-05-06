<?php

namespace App\Models\Reports;

use App\Models\Sale;

/**
 *
 *
 * @property sale sale
 *
 */
class Specific_employee extends Report
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
	 * @return array
	 */
	public function getDataColumns(): array
	{
		return [
			'summary' => [
				['id' => lang('Reports.sale_id')],
				['type_code' => lang('Reports.code_type')],
				['sale_time' => lang('Reports.date'), 'sortable' => false],
				['quantity' => lang('Reports.quantity')],
				['customer_name' => lang('Reports.sold_to')],
				['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
				['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'],
				['total' => lang('Reports.total'), 'sorter' => 'number_sorter'],
				['cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'],
				['profit' => lang('Reports.profit'), 'sorter' => 'number_sorter'],
				['payment_type' => lang('Reports.payment_type'), 'sortable' => false],
				['comment' => lang('Reports.comments')]
			],
			'details' => [
				lang('Reports.name'),
				lang('Reports.category'),
				lang('Reports.item_number'),
				lang('Reports.description'),
				lang('Reports.quantity'),
				lang('Reports.subtotal'),
				lang('Reports.tax'),
				lang('Reports.total'),
				lang('Reports.cost'),
				lang('Reports.profit'),
				lang('Reports.discount')
			],
			'details_rewards' => [
				lang('Reports.used'),
				lang('Reports.earned')
			]
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
			SUM(quantity_purchased) AS items_purchased,
			MAX(customer_name) AS customer_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(comment) AS comment');

		$builder->where('employee_id', $inputs['employee_id']);	//TODO: Duplicated code

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

		$builder->groupBy('sale_id');
		$builder->orderBy('MAX(sale_time)');

		$data = [];
		$data['summary'] = $builder->get()->getResultArray();
		$data['details'] = [];
		$data['rewards'] = [];

		foreach($data['summary'] as $key => $value)
		{
			$builder = $this->db->table('sales_items_temp');
			$builder->select('name, category, item_number, description, quantity_purchased, subtotal, tax, total, cost, profit, discount, discount_type');
			$builder->where('sale_id', $value['sale_id']);
			$data['details'][$key] = $builder->get()->getResultArray();

			$builder = $this->db->table('sales_reward_points');
			$builder->select('used, earned');
			$builder->where('sale_id', $value['sale_id']);
			$data['rewards'][$key] = $builder->get()->getResultArray();
		}

		return $data;
	}

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getSummaryData(array $inputs): array
	{
		$builder = $this->db->table('sales_items_temp');
		$builder->select('SUM(subtotal) AS subtotal, SUM(tax) AS tax, SUM(total) AS total, SUM(cost) AS cost, SUM(profit) AS profit');
		$builder->where('employee_id', $inputs['employee_id']);	//TODO: Duplicated code

		//TODO: this needs to be converted to a switch statement
		if($inputs['sale_type'] == 'complete')
		{
			$builder->where('sale_status', COMPLETED);
			$builder->groupStart();
			$builder->where('sale_type', SALE_TYPE_POS);
			$builder->orWhere('sale_type', SALE_TYPE_INVOICE);
			$builder->orWhere('sale_type', SALE_TYPE_RETURN);
			$builder->groupEnd();
		}
		elseif($inputs['sale_type'] == 'sales')
		{
			$builder->where('sale_status', COMPLETED);
			$builder->groupStart();
			$builder->where('sale_type', SALE_TYPE_POS);
			$builder->orWhere('sale_type', SALE_TYPE_INVOICE);
			$builder->groupEnd();
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$builder->where('sale_status', SUSPENDED);
			$builder->where('sale_type', SALE_TYPE_QUOTE);
		}
		elseif($inputs['sale_type'] == 'work_orders')
		{
			$builder->where('sale_status', SUSPENDED);
			$builder->where('sale_type', SALE_TYPE_WORK_ORDER);
		}
		elseif($inputs['sale_type'] == 'canceled')
		{
			$builder->where('sale_status', CANCELED);
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$builder->where('sale_status', COMPLETED);
			$builder->where('sale_type', SALE_TYPE_RETURN);
		}

		return $builder->get()->getRowArray();
	}
}
