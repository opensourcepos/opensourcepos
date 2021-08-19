<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Item_kit_items class
 */

class Item_kit_items extends Model
{
	/*
	Gets item kit items for a particular item kit
	*/
	public function get_info($item_kit_id)
	{
		$builder->select('item_kits.item_kit_id, item_kit_items.item_id, quantity, kit_sequence, unit_price, item_type, stock_type');
		$builder = $this->db->table('item_kit_items as item_kit_items');
		$builder->join('items as items', 'item_kit_items.item_id = items.item_id');
		$builder->join('item_kits as item_kits', 'item_kits.item_kit_id = item_kit_items.item_kit_id');
		$builder->where('item_kits.item_kit_id', $item_kit_id);
		$builder->orWhere('item_kit_number', $item_kit_id);
		$builder->orderBy('kit_sequence', 'asc');

		//return an array of item kit items for an item
		return $builder->get()->getResultArray();
	}

	/*
	Gets item kit items for a particular item kit
	*/
	public function get_info_for_sale($item_kit_id)
	{
		$builder = $this->db->table('item_kit_items');
		$builder->where('item_kit_id', $item_kit_id);

		$builder->orderBy('kit_sequence', 'desc');

		//return an array of item kit items for an item
		return $builder->get()->getResultArray();
	}
	/*
	Inserts or updates an item kit's items
	*/
	public function save(&$item_kit_items_data, $item_kit_id)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing

		$this->db->transStart();

		$this->delete($item_kit_id);

		if($item_kit_items_data != NULL)
		{
			foreach($item_kit_items_data as $row)
			{
				$row['item_kit_id'] = $item_kit_id;
				$success &= $builder->insert('item_kit_items', $row);
			}
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	/*
	Deletes item kit items given an item kit
	*/
	public function delete($item_kit_id)
	{
		return $builder->delete('item_kit_items', array('item_kit_id' => $item_kit_id));
	}
}
?>
