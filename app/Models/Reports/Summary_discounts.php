<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Summary_report.php");

class Summary_discounts extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('discount' => lang('Reports.discount'), 'sorter' => 'number_sorter'),
			array('count' => lang('Reports.count')),
			array('total' => lang('Reports.total')));
	}

	public function getData(array $inputs)
	{
		if($inputs['discount_type'] == FIXED)
		{
			$builder->select('SUM(sales_items.discount) AS total, MAX(CONCAT("'.$this->config->item('currency_symbol').'",sales_items.discount)) AS discount, count(*) AS count');
			$builder->where('discount_type', FIXED);
		}
		elseif($inputs['discount_type'] == PERCENT)
		{
			$builder->select('SUM(item_unit_price) * sales_items.discount / 100.0 AS total, MAX(CONCAT(sales_items.discount, "%")) AS discount, count(*) AS count');
			$builder->where('discount_type', PERCENT);
		}	
		
		$builder->where('discount >', 0);
		$builder->groupBy('sales_items.discount');
		$builder->orderBy('sales_items.discount');
		

		$builder = $this->db->table('sales_items AS sales_items');
		$builder->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');

		$this->_where($inputs);

		return $builder->get()->getResultArray();
	}
}
?>
