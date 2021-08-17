<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Customer_rewards class
 */

class Customer_rewards extends Model
{
	public function exists($package_id)
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return ($builder->get()->getNumRows() >= 1);
	}

	public function save($package_data, $package_id)
	{
		$package_data_to_save = array('package_name' => $package_data['package_name'], 'deleted' => 0, 'points_percent' => $package_data['points_percent']);

		if(!$this->exists($package_id))
		{
			return $builder->insert('customers_packages', $package_data_to_save);
		}

		$builder->where('package_id', $package_id);

		return $builder->update('customers_packages', $package_data_to_save);
	}

	public function get_name($package_id)
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->get()->row()->package_name;
	}

	public function get_points_percent($package_id)
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->get()->row()->points_percent;
	}

	public function get_all()
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('deleted', 0);

		return $builder->get();
	}

	/**
	Deletes one reward package
	*/
	public function delete($package_id)
	{
		$builder->where('package_id', $package_id);

		return $builder->update('customers_packages', array('deleted' => 1));
	}
}
?>
