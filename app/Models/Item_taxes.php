<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Item_taxes class
 */

class Item_taxes extends CI_Model
{
	/*
	Gets tax info for a particular item
	*/
	public function get_info($item_id)
	{
		$this->db->from('items_taxes');
		$this->db->where('item_id',$item_id);

		//return an array of taxes for an item
		return $this->db->get()->result_array();
	}

	/*
	Inserts or updates an item's taxes
	*/
	public function save(&$items_taxes_data, $item_id)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->delete($item_id);

		foreach($items_taxes_data as $row)
		{
			$row['item_id'] = $item_id;
			$success &= $this->db->insert('items_taxes', $row);
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	/*
	Saves taxes for multiple items
	*/
	public function save_multiple(&$items_taxes_data, $item_ids)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		foreach(explode(':', $item_ids) as $item_id)
		{
			$this->delete($item_id);

			foreach($items_taxes_data as $row)
			{
				$row['item_id'] = $item_id;
				$success &= $this->db->insert('items_taxes', $row);
			}
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	/*
	Deletes taxes given an item
	*/
	public function delete($item_id)
	{
		return $this->db->delete('items_taxes', array('item_id' => $item_id));
	}
}
?>
