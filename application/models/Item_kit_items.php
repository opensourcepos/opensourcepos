<?php
class Item_kit_items extends CI_Model
{
	/*
	Gets item kit items for a particular item kit
	*/
	public function get_info($item_kit_id)
	{
		$this->db->from('item_kit_items');
		$this->db->where('item_kit_id', $item_kit_id);
		
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
		
		foreach($item_kit_items_data as $row)
		{
			$row['item_kit_id'] = $item_kit_id;
			$success &= $this->db->insert('item_kit_items', $row);		
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
