<?php

namespace App\Models\Reports;

use App\Models\Receiving;

/**
 *
 *
 * @property receiving receiving
 *
 */
class Detailed_receivings extends Report
{
	/**
	 * @param array $inputs
	 * @return void
	 */
	public function create(array $inputs): void
	{
		//Create our temp tables to work with the data in our report
		$receiving = model(Receiving::class);
		$receiving->create_temp_table($inputs);
	}

	/**
	 * @return array
	 */
	public function getDataColumns(): array
	{
		return [
			'summary' => [
				['id' => lang('Reports.receiving_id')],
				['receiving_time' => lang('Reports.date'), 'sortable' => false],
				['quantity' => lang('Reports.quantity')],
				['employee_name' => lang('Reports.received_by')],
				['supplier_name' => lang('Reports.supplied_by')],
				['total' => lang('Reports.total'), 'sorter' => 'number_sorter'],
				['payment_type' => lang('Reports.payment_type')],
				['comment' => lang('Reports.comments')],
				['reference' => lang('Receivings.reference')]
			],
			'details' => [
				lang('Reports.item_number'),
				lang('Reports.name'),
				lang('Reports.category'),
				lang('Reports.quantity'),
				lang('Reports.total'),
				lang('Reports.discount')
			]
		];
	}

	/**
	 * @param string $receiving_id
	 * @return array
	 */
	public function getDataByReceivingId(string $receiving_id): array
	{
		$builder = $this->db->table('receivings_items_temp');
		$builder->select('receiving_id,
			MAX(receiving_time) as receiving_time,
			SUM(quantity_purchased) AS items_purchased,
			MAX(CONCAT(employee.first_name, " ", employee.last_name)) AS employee_name,
			MAX(supplier.company_name) AS supplier_name,
			SUM(subtotal) AS subtotal,
			SUM(total) AS total,
			SUM(profit) AS profit,
			MAX(payment_type) as payment_type,
			MAX(comment) as comment,
			MAX(reference) as reference');
		$builder->join('people AS employee', 'receivings_items_temp.employee_id = employee.person_id');
		$builder->join('suppliers AS supplier', 'receivings_items_temp.supplier_id = supplier.person_id', 'left');
		$builder->where('receiving_id', $receiving_id);
		$builder->groupBy('receiving_id');

		return $builder->get()->getRowArray();
	}

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getData(array $inputs): array
	{
		$builder = $this->db->table('receivings_items_temp AS receivings_items_temp');
		$builder->select('receiving_id,
			MAX(receiving_time) as receiving_time,
			SUM(quantity_purchased) AS items_purchased,
			MAX(CONCAT(employee.first_name," ",employee.last_name)) AS employee_name,
			MAX(supplier.company_name) AS supplier_name,
			SUM(total) AS total,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(comment) AS comment,
			MAX(reference) AS reference');
		$builder->join('people AS employee', 'receivings_items_temp.employee_id = employee.person_id');
		$builder->join('suppliers AS supplier', 'receivings_items_temp.supplier_id = supplier.person_id', 'left');

		if($inputs['location_id'] != 'all')
		{
			$builder->where('item_location', $inputs['location_id']);
		}

		if($inputs['receiving_type'] == 'receiving')	//TODO: These if statements should be replaced with a switch statement
		{
			$builder->where('quantity_purchased >', 0);
		}
		elseif($inputs['receiving_type'] == 'returns')
		{
			$builder->where('quantity_purchased <', 0);
		}
		elseif($inputs['receiving_type'] == 'requisitions')
		{
			$builder->having('items_purchased = 0');
		}

		$builder->groupBy('receiving_id', 'receiving_time');
		$builder->orderBy('receiving_id');

		$data = [];
		$data['summary'] = $builder->get()->getResultArray();
		$data['details'] = [];

		$builder = $this->db->table('receivings_items_temp');

		foreach($data['summary'] as $key => $value)
		{
			$builder->select('
				MAX(name) AS name, 
				MAX(item_number) AS item_number, 
				MAX(category) AS category, 
				MAX(quantity_purchased) AS quantity_purchased, 
				MAX(serialnumber) AS serialnumber, 
				MAX(total) AS total, 
				MAX(discount) AS discount, 
				MAX(discount_type) AS discount_type, 
				MAX(item_location) AS item_location, 
				MAX(item_receiving_quantity) AS receiving_quantity');
			$builder->join('items', 'receivings_items_temp.item_id = items.item_id');

			if(count($inputs['definition_ids']) > 0)
			{
				$format = $this->db->escape(dateformat_mysql());
				$builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_value) ORDER BY definition_id SEPARATOR \'|\') AS attribute_values');
				$builder->select("GROUP_CONCAT(DISTINCT CONCAT_WS('_', definition_id, DATE_FORMAT(attribute_date, $format)) SEPARATOR '|') AS attribute_dtvalues");
				$builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_decimal) SEPARATOR \'|\') AS attribute_dvalues');
				$builder->join('attribute_links', 'attribute_links.item_id = items.item_id AND attribute_links.receiving_id = receivings_items_temp.receiving_id AND definition_id IN (' . implode(',', $inputs['definition_ids']) . ')', 'left');
				$builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id', 'left');
			}

			$builder->where('receivings_items_temp.receiving_id', $value['receiving_id']);
			$builder->groupBy('receivings_items_temp.receiving_id, receivings_items_temp.item_id');
			$data['details'][$key] = $builder->get()->getResultArray();
		}

		return $data;
	}

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getSummaryData(array $inputs): array
	{
		$builder = $this->db->table('receivings_items_temp');
		$builder->select('SUM(total) AS total');

		if($inputs['location_id'] != 'all')
		{
			$builder->where('item_location', $inputs['location_id']);
		}

		switch($inputs['receiving_type'])
		{
			case 'receiving':
				$builder->where('quantity_purchased >', 0);
				break;

			case 'returns':
				$builder->where('quantity_purchased <', 0);
				break;

			case 'requisitions':
				$builder->where('quantity_purchased', 0);
				break;
		}

		return $builder->get()->getRowArray();
	}
}
