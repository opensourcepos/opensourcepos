<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Rewards class
 */

class Rewards extends Model
{
	/*
	Inserts or updates a rewards
	*/
	public function save(&$rewards_data, $rewards_id = FALSE)
	{
		if(!$rewards_id || !$this->exists($rewards_id))
		{
			if($builder->insert('sales_reward_points', $rewards_data))
			{
				$rewards_data['id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$builder->where('id', $rewards_id);

		return $builder->update('sales_reward_points', $rewards_data);
	}
}
?>
