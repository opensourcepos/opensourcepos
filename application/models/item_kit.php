<?php
class Item_kit extends CI_Model
{
	/*
	Determines if a given item_id is an item kit
	*/
	function exists($item_kit_id)
	{
		$this->db->from('item_kits');
		$this->db->where('item_kit_id',$item_kit_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	/*
	Returns all the item kits
	*/
	function get_all($limit=10000, $offset=0)
	{
		$this->db->from('item_kits');
		$this->db->order_by("name", "asc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}
	
	function count_all()
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
		$this->db->where('item_kit_id',$item_kit_id);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $item_kit_id is NOT an item kit
			$item_obj=new stdClass();

			//Get all the fields from items table
			$fields = $this->db->list_fields('item_kits');

			foreach ($fields as $field)
			{
				$item_obj->$field='';
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
		$this->db->where_in('item_kit_id',$item_kit_ids);
		$this->db->order_by("name", "asc");
		return $this->db->get();
	}

	/*
	Inserts or updates an item kit
	*/
	function save(&$item_kit_data,$item_kit_id=false)
	{
		if (!$item_kit_id or !$this->exists($item_kit_id))
		{
			if($this->db->insert('item_kits',$item_kit_data))
			{
				$item_kit_data['item_kit_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('item_kit_id', $item_kit_id);
		return $this->db->update('item_kits',$item_kit_data);
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
		$this->db->where_in('item_kit_id',$item_kit_ids);
		return $this->db->delete('item_kits');		
 	}

 	/*
	Get search suggestions to find kits
	*/
	function get_search_suggestions($search,$limit=25)
	{
		$suggestions = array();

		$this->db->from('item_kits');
		$this->db->like('name', $search);
		$this->db->order_by("name", "asc");
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->name;
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}
	
	function get_item_kit_search_suggestions($search, $limit=25)
	{
		$suggestions = array();

		$this->db->from('item_kits');
		$this->db->like('name', $search);
		$this->db->order_by("name", "asc");
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]='KIT '.$row->item_kit_id.'|'.$row->name;
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
		
	}

	/*
	Preform a search on items
	*/
	function search($search)
	{
		$this->db->from('item_kits');
		$this->db->where("name LIKE '%".$this->db->escape_like_str($search)."%' or 
		description LIKE '%".$this->db->escape_like_str($search)."%'");
		$this->db->order_by("name", "asc");
		return $this->db->get();	
	}
}
?>