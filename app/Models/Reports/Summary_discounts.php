<?php

namespace App\Models\Reports;

class Summary_discounts extends Summary_report
{
	protected function _get_data_columns(): array	//TODO: Hungarian notation
	{
		return [
			['discount' => lang('Reports.discount'), 'sorter' => 'number_sorter'],
			['count' => lang('Reports.count')],
			['total' => lang('Reports.total')]
		];
	}

	public function getData(array $inputs): array
	{
		$config = config(OSPOS::class)->settings;
		$builder = $this->db->table('sales_items AS sales_items');

		if($inputs['discount_type'] == FIXED)	//TODO: if there are only two options for this if/else statement then it needs to be refactored to use ternary operators. Also ===?
		{
			$builder->select('SUM(sales_items.discount) AS total, MAX(CONCAT("' . $config['currency_symbol'] . '",sales_items.discount)) AS discount, count(*) AS count');
			$builder->where('discount_type', FIXED);
		}
		elseif($inputs['discount_type'] == PERCENT)	//TODO: === ?
		{
			$builder->select('SUM(item_unit_price) * sales_items.discount / 100.0 AS total, MAX(CONCAT(sales_items.discount, "%")) AS discount, count(*) AS count');
			$builder->where('discount_type', PERCENT);
		}

		$builder->where('discount >', 0);
		$builder->groupBy('sales_items.discount');
		$builder->orderBy('sales_items.discount');

		$builder->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');

		$this->_where($inputs, $builder);	//TODO: Hungarian notation

		return $builder->get()->getResultArray();
	}
}
