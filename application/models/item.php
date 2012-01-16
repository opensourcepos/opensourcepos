<?php
class Item extends CI_Model
{
	/*
	Determines if a given item_id is an item
	*/
	function exists($item_id)
	{
		$this->db->from('items');
		$this->db->where('item_id',$item_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	/*
	Returns all the items
	*/
	function get_all($limit=10000, $offset=0)
	{
		$this->db->from('items');
		$this->db->where('deleted',0);
		$this->db->order_by("name", "asc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}
	
	function count_all()
	{
		$this->db->from('items');
		$this->db->where('deleted',0);
		return $this->db->count_all_results();
	}

	function get_all_filtered($low_inventory=0,$is_serialized=0,$no_description)
	{
		$this->db->from('items');
		if ($low_inventory !=0 )
		{
			$this->db->where('quantity <=','reorder_level', false);
		}
		if ($is_serialized !=0 )
		{
			$this->db->where('is_serialized',1);
		}
		if ($no_description!=0 )
		{
			$this->db->where('description','');
		}
		$this->db->where('deleted',0);
		$this->db->order_by("name", "asc");
		return $this->db->get();
	}

	/*
	Gets information about a particular item
	*/
	function get_info($item_id)
	{
		$this->db->from('items');
		$this->db->where('item_id',$item_id);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj=new stdClass();

			//Get all the fields from items table
			$fields = $this->db->list_fields('items');

			foreach ($fields as $field)
			{
				$item_obj->$field='';
			}

			return $item_obj;
		}
	}

	/*
	Get an item id given an item number
	*/
	function get_item_id($item_number)
	{
		$this->db->from('items');
		$this->db->where('item_number',$item_number);

		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row()->item_id;
		}

		return false;
	}

	/*
	Gets information about multiple items
	*/
	function get_multiple_info($item_ids)
	{
		$this->db->from('items');
		$this->db->where_in('item_id',$item_ids);
		$this->db->order_by("item", "asc");
		return $this->db->get();
	}

	/*
	Inserts or updates a item
	*/
	function save(&$item_data,$item_id=false)
	{
		if (!$item_id or !$this->exists($item_id))
		{
			if($this->db->insert('items',$item_data))
			{
				$item_data['item_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('item_id', $item_id);
		return $this->db->update('items',$item_data);
	}

	/*
	Updates multiple items at once
	*/
	function update_multiple($item_data,$item_ids)
	{
		$this->db->where_in('item_id',$item_ids);
		return $this->db->update('items',$item_data);
	}

	/*
	Deletes one item
	*/
	function delete($item_id)
	{
		$this->db->where('item_id', $item_id);
		return $this->db->update('items', array('deleted' => 1));
	}

	/*
	Deletes a list of items
	*/
	function delete_list($item_ids)
	{
		$this->db->where_in('item_id',$item_ids);
		return $this->db->update('items', array('deleted' => 1));
 	}

 	/*
	Get search suggestions to find items
	*/
	function get_search_suggestions($search,$limit=25)
	{
		$suggestions = array();

		$this->db->from('items');
		$this->db->like('name', $search);
		$this->db->where('deleted',0);
		$this->db->order_by("name", "asc");
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->name;
		}

		$this->db->select('category');
		$this->db->from('items');
		$this->db->where('deleted',0);
		$this->db->distinct();
		$this->db->like('category', $search);
		$this->db->order_by("category", "asc");
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]=$row->category;
		}

		$this->db->from('items');
		$this->db->like('item_number', $search);
		$this->db->where('deleted',0);
		$this->db->order_by("item_number", "asc");
		$by_item_number = $this->db->get();
		foreach($by_item_number->result() as $row)
		{
			$suggestions[]=$row->item_number;
		}


		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}

	function get_item_search_suggestions($search,$limit=25)
	{
		$suggestions = array();

		$this->db->from('items');
		$this->db->where('deleted',0);
		$this->db->like('name', $search);
		$this->db->order_by("name", "asc");
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->item_id.'|'.$row->name;
		}

		$this->db->from('items');
		$this->db->where('deleted',0);
		$this->db->like('item_number', $search);
		$this->db->order_by("item_number", "asc");
		$by_item_number = $this->db->get();
		foreach($by_item_number->result() as $row)
		{
			$suggestions[]=$row->item_id.'|'.$row->item_number;
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}

	function get_category_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('category');
		$this->db->from('items');
		$this->db->like('category', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by("category", "asc");
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]=$row->category;
		}

		return $suggestions;
	}

	/*
	Preform a search on items
	*/
	function search($search)
	{
		$this->db->from('items');
		$this->db->where("(name LIKE '%".$this->db->escape_like_str($search)."%' or 
		item_number LIKE '%".$this->db->escape_like_str($search)."%' or 
		category LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");
		$this->db->order_by("name", "asc");
		return $this->db->get();	
	}

	function get_categories()
	{
		$this->db->select('category');
		$this->db->from('items');
		$this->db->where('deleted',0);
		$this->db->distinct();
		$this->db->order_by("category", "asc");

		return $this->db->get();
	}
}
?>
