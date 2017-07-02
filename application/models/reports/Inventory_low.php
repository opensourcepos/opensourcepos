<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Inventory_low extends Report
{
	public function getDataColumns()
	{
		return array(
			array('item_name' => $this->lang->line('reports_item_name')),
			array('item_number' => $this->lang->line('reports_item_number')),
			array('quantity' => $this->lang->line('reports_quantity')),
			array('reorder_level' => $this->lang->line('reports_reorder_level')),
			array('location_name' => $this->lang->line('reports_stock_location')));
	}

	public function getData(array $inputs)
	{
		$this->db->select('items.name, items.item_number, item_quantities.quantity, items.reorder_level, stock_locations.location_name');
		$this->db->from('items');
		$this->db->join('item_quantities', 'items.item_id = item_quantities.item_id');
		$this->db->join('stock_locations', 'item_quantities.location_id = stock_locations.location_id');
		$this->db->where('items.deleted', 0);
		$this->db->where('stock_locations.deleted', 0);
		$this->db->where('items.stock_type', 0);
		$this->db->where('item_quantities.quantity <= items.reorder_level');
		$this->db->order_by('items.name');

		return $this->db->get()->result_array();
	}

	public function getSummaryData(array $inputs)
	{
		return array();
	}
}
?>
