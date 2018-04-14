<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Item_kit class
 */

class Item_kit extends CI_Model
{
	/*
	Determines if a given item_id is an item kit
	*/
	public function exists($item_kit_id)
	{
		$this->db->from('item_kits');
		$this->db->where('item_kit_id', $item_kit_id);

		return ($this->db->get()->num_rows() == 1);
	}

	/*
	Check if a given item_id is an item kit
	*/
	public function is_valid_item_kit($item_kit_id)
	{
		if(!empty($item_kit_id))
		{
			//KIT #
			$pieces = explode(' ', $item_kit_id);

			if(count($pieces) == 2 && preg_match('/(KIT)/i', $pieces[0]))
			{
				return $this->exists($pieces[1]);
			}
		}

		return FALSE;
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('item_kits');

		return $this->db->count_all_results();
	}

	/*
	Gets information about a particular item kit
	*/
	public function get_info($item_kit_id)
	{
		$this->db->select('
		item_kit_id,
		item_kits.name as name,
		items.name as item_name,
		item_kits.description,
		items.description as item_description,
		item_kits.item_id as kit_item_id,
		kit_discount_percent,
		price_option,
		print_option,
		category,
		supplier_id,
		item_number,
		cost_price,
		unit_price,
		reorder_level,
		receiving_quantity,
		pic_filename,
		allow_alt_description,
		is_serialized,
		items.deleted,
		custom1,
		custom2,
		custom3,
		custom4,
		custom5,
		custom6,
		custom7,
		custom8,
		custom9,
		custom10,
		item_type,
		stock_type');

		$this->db->from('item_kits');
		$this->db->join('items', 'item_kits.item_id = items.item_id', 'left');
		$this->db->where('item_kit_id', $item_kit_id);

		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $item_kit_id is NOT an item kit
			$item_obj = new stdClass();

			//Get all the fields from items table
			foreach($this->db->list_fields('item_kits') as $field)
			{
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}

	/*
	Gets information about multiple item kits
	*/
	public function get_multiple_info($item_kit_ids)
	{
		$this->db->from('item_kits');
		$this->db->where_in('item_kit_id', $item_kit_ids);
		$this->db->order_by('name', 'asc');

		return $this->db->get();
	}

	/*
	Inserts or updates an item kit
	*/
	public function save(&$item_kit_data, $item_kit_id = FALSE)
	{
		if(!$item_kit_id || !$this->exists($item_kit_id))
		{
			if($this->db->insert('item_kits', $item_kit_data))
			{
				$item_kit_data['item_kit_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('item_kit_id', $item_kit_id);

		return $this->db->update('item_kits', $item_kit_data);
	}

	/*
	Deletes one item kit
	*/
	public function delete($item_kit_id)
	{
		return $this->db->delete('item_kits', array('item_kit_id' => $id));
	}

	/*
	Deletes a list of item kits
	*/
	public function delete_list($item_kit_ids)
	{
		$this->db->where_in('item_kit_id', $item_kit_ids);

		return $this->db->delete('item_kits');
	}

	public function get_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		$this->db->from('item_kits');

		//KIT #
		if(stripos($search, 'KIT ') !== FALSE)
		{
			$this->db->like('item_kit_id', str_ireplace('KIT ', '', $search));
			$this->db->order_by('item_kit_id', 'asc');

			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('value' => 'KIT '. $row->item_kit_id, 'label' => 'KIT ' . $row->item_kit_id);
			}
		}
		else
		{
			$this->db->like('name', $search);
			$this->db->order_by('name', 'asc');

			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('value' => 'KIT ' . $row->item_kit_id, 'label' => $row->name);
			}
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

 	/*
	Gets rows
	*/
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'name', 'asc', TRUE);
	}

	/*
	Perform a search on items
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'name', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(item_kits.item_kit_id) as count');
		}

		$this->db->from('item_kits AS item_kits');
		$this->db->like('name', $search);
		$this->db->or_like('description', $search);

		//KIT #
		if(stripos($search, 'KIT ') !== FALSE)
		{
			$this->db->or_like('item_kit_id', str_ireplace('KIT ', '', $search));
		}

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $this->db->get()->row()->count;
		}

		$this->db->order_by($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}
}
?>
