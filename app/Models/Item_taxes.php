<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Item_taxes class
 */

class Item_taxes extends Model
{
	/*
	Gets tax info for a particular item
	*/
	public function get_info($item_id)
	{
		$builder = $this->db->table('items_taxes');
		$builder->where('item_id',$item_id);

		//return an array of taxes for an item
		return $builder->get()->getResultArray();
	}

	/*
	Inserts or updates an item's taxes
	*/
	public function save(&$items_taxes_data, $item_id)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		$this->delete($item_id);

		foreach($items_taxes_data as $row)
		{
			$row['item_id'] = $item_id;
			$success &= $builder->insert('items_taxes', $row);
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	/*
	Saves taxes for multiple items
	*/
	public function save_multiple(&$items_taxes_data, $item_ids)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		foreach(explode(':', $item_ids) as $item_id)
		{
			$this->delete($item_id);

			foreach($items_taxes_data as $row)
			{
				$row['item_id'] = $item_id;
				$success &= $builder->insert('items_taxes', $row);
			}
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	/*
	Deletes taxes given an item
	*/
	public function delete($item_id)
	{
		return $builder->delete('items_taxes', array('item_id' => $item_id));
	}
}
?>
