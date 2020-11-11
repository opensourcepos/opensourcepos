<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Item_kit_items class
 */

class Item_kit_items extends CI_Model
{
	/*
	Gets item kit items for a particular item kit
	*/
	public function get_info($item_kit_id)
	{
		$this->db->select('item_kits.item_kit_id, item_kit_items.item_id, quantity, kit_sequence, unit_price, item_type, stock_type');
		$this->db->from('item_kit_items as item_kit_items');
		$this->db->join('items as items', 'item_kit_items.item_id = items.item_id');
		$this->db->join('item_kits as item_kits', 'item_kits.item_kit_id = item_kit_items.item_kit_id');
		$this->db->where('item_kits.item_kit_id', $item_kit_id);
		$this->db->or_where('item_kit_number', $item_kit_id);
		$this->db->order_by('kit_sequence', 'asc');

		//return an array of item kit items for an item
		return $this->db->get()->result_array();
	}

	/*
	Gets item kit items for a particular item kit
	*/
	public function get_info_for_sale($item_kit_id)
	{
		$this->db->from('item_kit_items');
		$this->db->where('item_kit_id', $item_kit_id);

		$this->db->order_by('kit_sequence', 'desc');

		//return an array of item kit items for an item
		return $this->db->get()->result_array();
	}
	/*
	Inserts or updates an item kit's items
	*/
	public function save(&$item_kit_items_data, $item_kit_id)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing

		$this->db->trans_start();

		$this->delete($item_kit_id);

		if($item_kit_items_data != NULL)
		{
			foreach($item_kit_items_data as $row)
			{
				$row['item_kit_id'] = $item_kit_id;
				$success &= $this->db->insert('item_kit_items', $row);
			}
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	/*
	Deletes item kit items given an item kit
	*/
	public function delete($item_kit_id)
	{
		return $this->db->delete('item_kit_items', array('item_kit_id' => $item_kit_id));
	}
}
?>
