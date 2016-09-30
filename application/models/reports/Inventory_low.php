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
					$this->lang->line('reports_quantity'), 
					$this->lang->line('reports_reorder_level'), 
					$this->lang->line('reports_stock_location'));
	}
	
    public function getData(array $inputs)
    {
        $this->db->select('name, item_number, quantity, reorder_level, location_name');
        $this->db->from('items_temp');
        $this->db->where('quantity <= reorder_level');
        $this->db->order_by('name');

        return $this->db->get()->result_array();
    }
	
	public function getSummaryData(array $inputs)
	{
		return array();
	}
}
?>