<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Customer_rewards class
 */

class Customer_rewards extends CI_Model
{
	public function exists($package_id)
	{
		$this->db->from('customers_packages');
		$this->db->where('package_id', $package_id);

		return ($this->db->get()->num_rows() >= 1);
	}

	public function save($package_data, $package_id)
	{
		$package_data_to_save = array('package_name' => $package_data['package_name'], 'deleted' => 0, 'points_percent' => $package_data['points_percent']);

		if(!$this->exists($package_id))
		{
			return $this->db->insert('customers_packages', $package_data_to_save);
		}

		$this->db->where('package_id', $package_id);

		return $this->db->update('customers_packages', $package_data_to_save);
	}

	public function get_name($package_id)
	{
		$this->db->from('customers_packages');
		$this->db->where('package_id', $package_id);

		return $this->db->get()->row()->package_name;
	}

	public function get_points_percent($package_id)
	{
		$this->db->from('customers_packages');
		$this->db->where('package_id', $package_id);

		return $this->db->get()->row()->points_percent;
	}

	public function get_all()
	{
		$this->db->from('customers_packages');
		$this->db->where('deleted', 0);

		return $this->db->get();
	}

	/**
	Deletes one reward package
	*/
	public function delete($package_id)
	{
		$this->db->where('package_id', $package_id);

		return $this->db->update('customers_packages', array('deleted' => 1));
	}
}
?>
