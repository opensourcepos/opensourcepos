<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Inventory class
 */

class Inventory extends Model
{
	public function insert($inventory_data)
	{
		return $builder->insert('inventory', $inventory_data);
	}

	public function update($comment, $inventory_data)
	{
		$builder->where('trans_comment', $comment);
		return $builder->update('inventory', $inventory_data);
	}

	public function get_inventory_data_for_item($item_id, $location_id = FALSE)
	{
		$builder = $this->db->table('inventory');
		$builder->where('trans_items', $item_id);
        if($location_id != FALSE)
        {
            $builder->where('trans_location', $location_id);
        }
		$builder->orderBy('trans_date', 'desc');

		return $builder->get();
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
					'trans_comment' => lang('Items.is_deleted'),
					'trans_user' => $this->Employee->get_logged_in_employee_info()->person_id)
				);
			}
		}

		return TRUE;
	}

	public function get_inventory_sum($item_id)
	{
		$this->db->select('SUM(trans_inventory) AS sum, MAX(trans_location) AS location_id');
		$builder = $this->db->table('inventory');
		$builder->where('trans_items', $item_id);
		$this->db->group_by('trans_location');

		return $builder->get()->result_array();
	}
}
?>
