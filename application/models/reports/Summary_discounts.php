<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_discounts extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('discount' => $this->lang->line('reports_discount'), 'sorter' => 'number_sorter'),
			array('count' => $this->lang->line('reports_count')),
			array('total' => $this->lang->line('reports_total')));
	}

	public function getData(array $inputs)
	{
		if($inputs['discount_type'] == FIXED)
		{
			$this->db->select('SUM(sales_items.discount) AS total, MAX(CONCAT("'.$this->config->item('currency_symbol').'",sales_items.discount)) AS discount, count(*) AS count');
			$this->db->where('discount_type', FIXED);
		}
		elseif($inputs['discount_type'] == PERCENT)
		{
			$this->db->select('SUM(item_unit_price) * sales_items.discount / 100.0 AS total, MAX(CONCAT(sales_items.discount, "%")) AS discount, count(*) AS count');
			$this->db->where('discount_type', PERCENT);
		}	
		
		$this->db->where('discount >', 0);
		$this->db->group_by('sales_items.discount');
		$this->db->order_by('sales_items.discount');
		

		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');

		$this->_where($inputs);

		return $this->db->get()->result_array();
	}
}
?>
