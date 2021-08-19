<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Customer_rewards class
 */

class Customer_rewards extends Model
{
	public function exists($package_id): bool
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return ($builder->get()->getNumRows() >= 1);
	}

	public function save($package_data, $package_id): bool
	{
		$package_data_to_save = array('package_name' => $package_data['package_name'], 'deleted' => 0, 'points_percent' => $package_data['points_percent']);

		if(!$this->exists($package_id))
		{
			$builder = $this->db->table('customers_packages');
			return $builder->insert($package_data_to_save);
		}

		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->update($package_data_to_save);
	}

	public function get_name($package_id)
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->get()->getRow()->package_name;
	}

	public function get_points_percent($package_id)
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->get()->getRow()->points_percent;
	}

	public function get_all()
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('deleted', 0);

		return $builder->get();
	}
//TODO: need to fix this function so it either isn't overriding the basemodel function or get it in line
	/**
	Deletes one reward package
	*/
	public function delete($package_id): bool
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->update('customers_packages', array('deleted' => 1));
	}
}
?>
