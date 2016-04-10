<?php
require_once("Report.php");
class Inventory_summary extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_item_name'),
					$this->lang->line('reports_item_number'),
					$this->lang->line('reports_description'),
					$this->lang->line('reports_quantity'),
					$this->lang->line('reports_reorder_level'),
					$this->lang->line('reports_stock_location'),
					$this->lang->line('reports_cost_price'),
					$this->lang->line('reports_unit_price'),
					$this->lang->line('reports_sub_total_value'));
	}
	
	public function getData(array $inputs)
	{
	    $this->db->from('items');
        $this->db->join('item_quantities','items.item_id=item_quantities.item_id');
        $this->db->join('stock_locations','item_quantities.location_id=stock_locations.location_id');
		$this->db->select('name, item_number, reorder_level, item_quantities.quantity, description, location_name, cost_price, unit_price, (cost_price*quantity) as sub_total_value');
		$this->db->where('items.deleted', 0);
		// should be corresponding to values Inventory_summary::getItemCountDropdownArray() returns...
		if($inputs['item_count'] == 'zero_and_less')
		{
			$this->db->where('quantity <=', 0);
		}
		elseif($inputs['item_count'] == 'more_than_zero')
		{
			$this->db->where('quantity >', 0);
		}
		
		if($inputs['location_id'] != 'all')
		{
			$this->db->where('item_quantities.location_id',$inputs['location_id']);
		}
			
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
		$return = array('total_inventory_value' => 0);
		foreach($inputs as $input)
		{
			$return['total_inventory_value'] += $input['sub_total_value'];
		}
		return $return;
	}
	
	/**
	 * returns the array for the dropdown-element item-count in the form for the inventory summary-report
	 * 
	 * @return array
	 */
	public function getItemCountDropdownArray()
	{
		return array(
					'all' => $this->lang->line('reports_all'),
					'zero_and_less' => $this->lang->line('reports_zero_and_less'),
					'more_than_zero' => $this->lang->line('reports_more_than_zero'));
	}
}
?>