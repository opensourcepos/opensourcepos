<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_discounts extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('discount' => $this->lang->line('reports_discount_percent'), 'sorter' => 'number_sorter'),
			array('count' => $this->lang->line('reports_count')));
	}

	public function getData(array $inputs)
	{
		$this->db->select('MAX(CONCAT(sales_items.discount_percent, "%")) AS discount_percent, count(*) AS count');
		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');

		$this->db->where('discount_percent > 0');

		$this->_where($inputs);

		$this->db->group_by('sales_items.discount_percent');
		$this->db->order_by('sales_items.discount_percent');

		return $this->db->get()->result_array();
	}
}
?>
