<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use ReflectionException;

/**
 * Inventory class
 *
 * @property employee employee
 *
 */
class Inventory extends Model
{
	public function insert(array $inventory_data = NULL, bool $returnID = TRUE): bool	//TODO: $returnID does not match variable naming conventions.  It's also never used in the function
	{
		$builder = $this->db->table('inventory');

		return $builder->insert($inventory_data);
	}

	public function update(string $comment = NULL, array $inventory_data = NULL): bool	//TODO: this function either needs a name change or to be brought in line with the parent function declaration.
	{
		$builder = $this->db->table('inventory');
		$builder->where('trans_comment', $comment);

		return $builder->update($inventory_data);
	}

	/**
	 * Retrieves inventory data given an item_id.  Called in the view.
	 * @param int $item_id
	 * @param bool $location_id
	 * @return ResultInterface
	 */
	public function get_inventory_data_for_item(int $item_id, bool $location_id = FALSE): ResultInterface
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

	/**
	 * @throws ReflectionException
	 */
	public function reset_quantity(int $item_id): bool
	{
		$inventory_sums = $this->get_inventory_sum($item_id);
		foreach($inventory_sums as $inventory_sum)
		{
			if($inventory_sum['sum'] > 0)
			{//TODO: Reflection Exception
				$employee = model(Employee::class);

				return $this->insert([
					'trans_inventory' => -1 * $inventory_sum['sum'],
					'trans_items' => $item_id,
					'trans_location' => $inventory_sum['location_id'],
					'trans_comment' => lang('Items.is_deleted'),
					'trans_user' => $employee->get_logged_in_employee_info()->person_id
				]);
			}
		}

		return TRUE;
	}

	public function get_inventory_sum(int $item_id): array
	{
		$builder = $this->db->table('inventory');
		$builder->select('SUM(trans_inventory) AS sum, MAX(trans_location) AS location_id');
		$builder->where('trans_items', $item_id);
		$builder->groupBy('trans_location');

		return $builder->get()->getResultArray();
	}
}