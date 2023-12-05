<?php

namespace App\Models\Reports;

use App\Models\Sale;

/**
 *
 *
 * @property sale sale
 *
 */
class Detailed_sales extends Report
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
		return [	//TODO: Duplicated code
			'summary' => [
				['id' => lang('Reports.sale_id')],
				['type_code' => lang('Reports.code_type')],
				['sale_time' => lang('Reports.date'), 'sortable' => false],
				['quantity' => lang('Reports.quantity')],
				['employee_name' => lang('Reports.sold_by')],
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
	 * @param int $sale_id
	 * @return array
	 */
	public function getDataBySaleId(int $sale_id): array
	{
		$builder = $this->db->table('sales_items_temp');
		$builder->select('sale_id,
			sale_time as sale_time,
			SUM(quantity_purchased) AS items_purchased,
			MAX(employee_name) AS employee_name,
			MAX(customer_name) AS customer_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(sale_status) AS sale_status,
			comment');
		$builder->where('sale_id', $sale_id);

		return $builder->get()->getRowArray();
	}

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getData(array $inputs): array
	{
		$builder = $this->db->table('sales_items_temp');
		$builder->select('sale_id, 
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
			MAX(employee_name) AS employee_name,
			MAX(customer_name) AS customer_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(comment) AS comment');

		if($inputs['location_id'] != 'all')	//TODO: Duplicated code
		{
			$builder->where('item_location', $inputs['location_id']);
		}

		//TODO: These if statements should be converted to a switch statement
		if($inputs['sale_type'] == 'complete')	//TODO: Duplicated code
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

		$builder->groupBy('sale_id');
		$builder->orderBy('MAX(sale_time)');

		$data = [];
		$data['summary'] = $builder->get()->getResultArray();
		$data['details'] = [];
		$data['rewards'] = [];

		foreach($data['summary'] as $key => $value)
		{
			$builder = $this->db->table('sales_items_temp');
			$builder->select('
				MAX(name) AS name, 
				MAX(category) AS category, 
				MAX(quantity_purchased) AS quantity_purchased, 
				MAX(item_location) AS item_location, 
				MAX(item_number) AS item_number, 
				MAX(description) AS description, 
				MAX(subtotal) AS subtotal, 
				MAX(tax) AS tax, 
				MAX(total) AS total, 
				MAX(cost) AS cost, 
				MAX(profit) AS profit, 
				MAX(discount) AS discount, 
				MAX(discount_type) AS discount_type, 
				MAX(sale_status) AS sale_status');

			if(count($inputs['definition_ids']) > 0)
			{
				$format = $this->db->escape(dateformat_mysql());
				$builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_value) ORDER BY definition_id SEPARATOR \'|\') AS attribute_values');
				$builder->select("GROUP_CONCAT(DISTINCT CONCAT_WS('_', definition_id, DATE_FORMAT(attribute_date, $format)) SEPARATOR '|') AS attribute_dtvalues");
				$builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_decimal) SEPARATOR \'|\') AS attribute_dvalues');
				$builder->join('attribute_links', 'attribute_links.item_id = sales_items_temp.item_id AND attribute_links.sale_id = sales_items_temp.sale_id AND definition_id IN (' . implode(',', $inputs['definition_ids']) . ')', 'left');
				$builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id', 'left');
			}

			$builder->groupBy('sales_items_temp.sale_id, sales_items_temp.item_id, sales_items_temp.sale_id');
			$builder->where('sales_items_temp.sale_id', $value['sale_id']);
			$data['details'][$key] = $builder->get()->getResultArray();

			$builder->select('used, earned');
			$builder = $this->db->table('sales_reward_points');
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

		if($inputs['location_id'] != 'all')	//TODO: Duplicated code
		{
			$builder->where('item_location', $inputs['location_id']);
		}

		//TODO: This should be converted to a switch statement
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
