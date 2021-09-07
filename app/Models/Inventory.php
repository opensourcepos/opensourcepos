<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Inventory class
 *
 * @property mixed employee
 *
 */

class Inventory extends Model
{
	public function __construct()
	{
		parent::__construct();

		$this->employee = model('Employee');
	}

	public function insert(array $inventory_data = null, bool $returnId = true): bool
	{
		$builder = $this->db->table('inventory');
		return $builder->insert($inventory_data);
	}

	public function update(string $comment = null, array $inventory_data = null): bool	//TODO: this function either needs a name change or to be brought in line with the parent function declaration.
	{
		$builder = $this->db->table('inventory');
		$builder->where('trans_comment', $comment);
		return $builder->update($inventory_data);
	}

	public function get_inventory_data_for_item(int $item_id, bool $location_id = FALSE)
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

	public function reset_quantity(int $item_id): bool
	{
		$inventory_sums = $this->get_inventory_sum($item_id);
		foreach($inventory_sums as $inventory_sum)
		{
			if($inventory_sum['sum'] > 0)
			{
				return $this->insert([
					'trans_inventory' => -1 * $inventory_sum['sum'],
					'trans_items' => $item_id,
					'trans_location' => $inventory_sum['location_id'],
					'trans_comment' => lang('Items.is_deleted'),
					'trans_user' => $this->employee->get_logged_in_employee_info()->person_id
				]);
			}
		}

		return TRUE;
	}

	public function get_inventory_sum($item_id): array
	{
		$builder = $this->db->table('inventory');
		$builder->select('SUM(trans_inventory) AS sum, MAX(trans_location) AS location_id');
		$builder->where('trans_items', $item_id);
		$builder->groupBy('trans_location');

		return $builder->get()->getResultArray();
	}
}
?>
