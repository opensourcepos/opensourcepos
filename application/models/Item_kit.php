<?php
class Item_kit extends CI_Model
{
	/*
	Determines if a given item_id is an item kit
	*/
	function exists($item_kit_id)
	{
		$this->db->from('item_kits');
		$this->db->where('item_kit_id', $item_kit_id);

		return ($this->db->get()->num_rows()==1);
	}
	
	function get_total_rows()
	{
		$this->db->from('item_kits');

		return $this->db->count_all_results();
	}
	
	/*
	Gets information about a particular item kit
	*/
	function get_info($item_kit_id)
	{
		$this->db->from('item_kits');
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
			$fields = $this->db->list_fields('item_kits');

			foreach ($fields as $field)
			{
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}

	/*
	Gets information about multiple item kits
	*/
	function get_multiple_info($item_kit_ids)
	{
		$this->db->from('item_kits');
		$this->db->where_in('item_kit_id', $item_kit_ids);
		$this->db->order_by('name', 'asc');

		return $this->db->get();
	}

	/*
	Inserts or updates an item kit
	*/
	function save(&$item_kit_data, $item_kit_id=false)
	{
		if (!$item_kit_id or !$this->exists($item_kit_id))
		{
			if($this->db->insert('item_kits', $item_kit_data))
			{
				$item_kit_data['item_kit_id'] = $this->db->insert_id();

				return true;
			}

			return false;
		}

		$this->db->where('item_kit_id', $item_kit_id);

		return $this->db->update('item_kits', $item_kit_data);
	}

	/*
	Deletes one item kit
	*/
	function delete($item_kit_id)
	{
		return $this->db->delete('item_kits', array('item_kit_id' => $id)); 	
	}

	/*
	Deletes a list of item kits
	*/
	function delete_list($item_kit_ids)
	{
		$this->db->where_in('item_kit_id', $item_kit_ids);

		return $this->db->delete('item_kits');		
 	}

	function get_search_suggestions($search, $limit=25)
	{
		$suggestions = array();

		$this->db->from('item_kits');

		//KIT #
		if (stripos($search, 'KIT ') !== false)
		{
			$this->db->like('item_kit_id', str_ireplace('KIT ', '', $search));

			$this->db->order_by('item_kit_id', 'asc');
			$by_name = $this->db->get();

			foreach($by_name->result() as $row)
			{
				$suggestions[] = array('value' => 'KIT '. $row->item_kit_id, 'label' => 'KIT ' . $row->item_kit_id);
			}
		}
		else
		{
			$this->db->like('name', $search);

			$this->db->order_by('name', 'asc');
			$by_name = $this->db->get();

			foreach($by_name->result() as $row)
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
	Perform a search on items
	*/
	function search($search, $rows=0, $limit_from=0)
	{
		$this->db->from('item_kits');
		$this->db->like('name', $search);
		$this->db->or_like('description', $search);
		
		//KIT #
		if (stripos($search, 'KIT ') !== false)
		{
			$this->db->or_like('item_kit_id', str_ireplace('KIT ', '', $search));
		}

		$this->db->order_by('name', 'asc');

		if ($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();	
	}
	
	function get_found_rows($search)
	{
		$this->db->from('item_kits');
		$this->db->like('name', $search);
		$this->db->or_like('description', $search);
		
		//KIT #
		if (stripos($search, 'KIT ') !== false)
		{
			$this->db->or_like('item_kit_id', str_ireplace('KIT ', '', $search));
		}

		return $this->db->get()->num_rows();
	}
}
?>