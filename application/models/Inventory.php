<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Inventory class
 */

class Inventory extends CI_Model
{
	public function insert($inventory_data)
	{
		return $this->db->insert('inventory', $inventory_data);
	}

	public function get_inventory_data_for_item($item_id, $location_id = FALSE)
	{
		$this->db->from('inventory');
		$this->db->where('trans_items', $item_id);
        if($location_id != FALSE)
        {
            $this->db->where('trans_location', $location_id);
        }
		$this->db->order_by('trans_date', 'desc');

		return $this->db->get();
	}

	public function reset_quantity($item_id)
	{
		$inventory_sums = $this->Inventory->get_inventory_sum($item_id);
		foreach($inventory_sums as $inventory_sum)
		{
			if($inventory_sum['sum'] > 0)
			{
				return $this->Inventory->insert(array(
					'trans_inventory' => -1 * $inventory_sum['sum'],
					'trans_items' => $item_id,
					'trans_location' => $inventory_sum['location_id'],
					'trans_comment' => $this->lang->line('items_is_deleted'),
					'trans_user' => $this->Employee->get_logged_in_employee_info()->person_id)
				);
			}
		}

		return TRUE;
	}

	public function get_inventory_sum($item_id)
	{
		$this->db->select('SUM(trans_inventory) AS sum, MAX(trans_location) AS location_id');
		$this->db->from('inventory');
		$this->db->where('trans_items', $item_id);
		$this->db->group_by('trans_location');

		return $this->db->get()->result_array();
	}
}
?>
