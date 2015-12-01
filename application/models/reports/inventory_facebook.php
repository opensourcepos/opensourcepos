<?php
require_once("report.php");
class Inventory_facebook extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_item_name'),
					$this->lang->line('reports_category'),
					$this->lang->line('reports_count'),					
					$this->lang->line('reports_unit_price'));
	}
	
	public function getData(array $inputs)
	{
	    $this->db->from('items');
        $this->db->join('item_quantities','items.item_id=item_quantities.item_id');
		$this->db->select('name, category, item_quantities.quantity, unit_price');
		$this->db->where('items.deleted', 0);
		$this->db->where('quantity >', 0);
		$this->db->order_by('category');	
		$this->db->order_by('name');

		return $this->db->get()->result_array();
	}
	
	/**
	 * calulcates the total value of the given inventory summary by summing all sub_total_values (see Inventory_summary::getData())
	 * 
	 * @param array $inputs expects the reports-data-array which Inventory_summary::getData() returns
	 * @return array
	 */
	public function getSummaryData(array $inputs)
	{
		$return = array('total_items_available' => 0);
		foreach($inputs as $input)
		{
			$return['total_items_available'] += $input['quantity'];
		}
		return $return;
	}
	
}
?>