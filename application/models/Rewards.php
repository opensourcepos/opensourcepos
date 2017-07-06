<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Rewards class
 */

class Rewards extends CI_Model
{
	/*
	Inserts or updates a rewards
	*/
	public function save(&$rewards_data, $id = -1)
	{
		if($id == -1 || !$this->exists($id))
		{
			if($this->db->insert('sales_reward_points', $rewards_data))
			{
				$rewards_data['id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('id', $id);

		return $this->db->update('sales_reward_points', $rewards_data);
	}
}
?>
