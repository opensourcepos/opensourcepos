<?php
require_once("Report.php");
class Inventory_low extends Report
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
					$this->lang->line('reports_stock_location'));
	}
	
    public function getData(array $inputs)
    {
        $this->db->from('items');
        $this->db->join('item_quantities','items.item_id=item_quantities.item_id');
        $this->db->join('stock_locations','item_quantities.location_id=stock_locations.location_id');
        $this->db->select('name, item_number, reorder_level, item_quantities.quantity, description, location_name');
        $this->db->where('item_quantities.quantity <= reorder_level');
        $this->db->where('items.deleted', 0);
        $this->db->order_by('name');
 
        return $this->db->get()->result_array();
    }
	
	public function getSummaryData(array $inputs)
	{
		return array();
	}
}
?>