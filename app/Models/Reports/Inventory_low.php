<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Report.php");

class Inventory_low extends Report
{
	public function getDataColumns()
	{
		return [
			['item_name' => lang('Reports.item_name')),
			['item_number' => lang('Reports.item_number')),
			['quantity' => lang('Reports.quantity')),
			['reorder_level' => lang('Reports.reorder_level')),
			['location_name' => lang('Reports.stock_location')));
	}

	public function getData(array $inputs)
	{
		$query = $this->db->query("SELECT " . $this->Item->get_item_name('name') . ", 
			items.item_number,
			item_quantities.quantity, 
			items.reorder_level, 
			stock_locations.location_name
			FROM " . $this->db->prefixTable('items') . " AS items
			JOIN " . $this->db->prefixTable('item_quantities') . " AS item_quantities ON items.item_id = item_quantities.item_id
			JOIN " . $this->db->prefixTable('stock_locations') . " AS stock_locations ON item_quantities.location_id = stock_locations.location_id
			WHERE items.deleted = 0
			AND items.stock_type = 0
			AND item_quantities.quantity <= items.reorder_level
			AND stock_locations.deleted = 0
			ORDER BY items.name");

		return $query->getResultArray();
	}

	public function getSummaryData(array $inputs)
	{
		return [];
	}
}
?>
