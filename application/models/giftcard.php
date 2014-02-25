<?php
class Giftcard extends CI_Model
{
	/*
	Determines if a given giftcard_id is an giftcard
	*/
	function exists( $giftcard_id )
	{
		$this->db->from('giftcards');
		$this->db->where('giftcard_id',$giftcard_id);
		$this->db->where('deleted',0);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	/*
	Returns all the giftcards
	*/
	function get_all($limit=10000, $offset=0)
	{
		$this->db->from('giftcards');
		$this->db->join('people','people.person_id=giftcards.person_id');//GARRISON ADDED 4/25/2013
		$this->db->where('deleted',0);
		$this->db->order_by("giftcard_number", "asc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}
	
	function count_all()
	{
		$this->db->from('giftcards');
		$this->db->where('deleted',0);
		return $this->db->count_all_results();
	}

	/*
	Gets information about a particular giftcard
	*/
	function get_info($giftcard_id)
	{
		$this->db->from('giftcards');
		$this->db->where('giftcard_id',$giftcard_id);
		$this->db->where('deleted',0);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $giftcard_id is NOT an giftcard
			$giftcard_obj=new stdClass();

			//Get all the fields from giftcards table
			$fields = $this->db->list_fields('giftcards');

			foreach ($fields as $field)
			{
				$giftcard_obj->$field='';
			}

			return $giftcard_obj;
		}
	}

	/*
	Get an giftcard id given an giftcard number
	*/
	function get_giftcard_id($giftcard_number)
	{
		$this->db->from('giftcards');
		$this->db->where('giftcard_number',$giftcard_number);
		$this->db->where('deleted',0);

		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row()->giftcard_id;
		}

		return false;
	}

	/*
	Gets information about multiple giftcards
	*/
	function get_multiple_info($giftcard_ids)
	{
		$this->db->from('giftcards');
		$this->db->where_in('giftcard_id',$giftcard_ids);
		$this->db->where('deleted',0);
		$this->db->order_by("giftcard_number", "asc");
		return $this->db->get();
	}

	/*
	Inserts or updates a giftcard
	*/
	function save(&$giftcard_data,$giftcard_id=false)
	{
		if (!$giftcard_id or !$this->exists($giftcard_id))
		{
			if($this->db->insert('giftcards',$giftcard_data))
			{
				$giftcard_data['giftcard_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('giftcard_id', $giftcard_id);
		return $this->db->update('giftcards',$giftcard_data);
	}

	/*
	Updates multiple giftcards at once
	*/
	function update_multiple($giftcard_data,$giftcard_ids)
	{
		$this->db->where_in('giftcard_id',$giftcard_ids);
		return $this->db->update('giftcards',$giftcard_data);
	}

	/*
	Deletes one giftcard
	*/
	function delete($giftcard_id)
	{
		$this->db->where('giftcard_id', $giftcard_id);
		return $this->db->update('giftcards', array('deleted' => 1));
	}

	/*
	Deletes a list of giftcards
	*/
	function delete_list($giftcard_ids)
	{
		$this->db->where_in('giftcard_id',$giftcard_ids);
		return $this->db->update('giftcards', array('deleted' => 1));
 	}

 	/*
	Get search suggestions to find giftcards
	*/
	function get_search_suggestions($search,$limit=25)
	{
		$suggestions = array();

		$this->db->from('giftcards');
		$this->db->like('giftcard_number', $search);
		$this->db->where('deleted',0);
		$this->db->order_by("giftcard_number", "asc");
		$by_number = $this->db->get();
		
		foreach($by_number->result() as $row)
		{
			$suggestions[]=$row->giftcard_number;
		}

/** GARRISON MODIFIED 4/24/2013 **/
 		$this->db->from('customers');
		$this->db->join('people','customers.person_id=people.person_id');
		$this->db->like("first_name",$this->db->escape_like_str($search));
		$this->db->or_like("last_name",$this->db->escape_like_str($search)); 
		$this->db->or_like("CONCAT(`first_name`,' ',`last_name`)",$this->db->escape_like_str($search));
		$this->db->where("deleted","0");
		$this->db->order_by("last_name", "asc");
		$by_name = $this->db->get();
		
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->first_name.' '.$row->last_name;
		}
/** END GARRISON MODIFIED **/				

	//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
	}
	
	/** GARRISON ADDED 5/3/2013 **/
	/*
	 Get search suggestions to find customers
	*/
	function get_person_search_suggestions($search,$limit=25)
	{
		$suggestions = array();
	
		$this->db->select('person_id');
		$this->db->from('people');
		$this->db->like('person_id',$this->db->escape_like_str($search));
		$this->db->or_like('first_name',$this->db->escape_like_str($search));
		$this->db->or_like('last_name',$this->db->escape_like_str($search));
		$this->db->or_like("CONCAT(`first_name`,' ',`last_name`)",$this->db->escape_like_str($search));
		$this->db->or_like('email',$this->db->escape_like_str($search));
		$this->db->or_like('phone_number',$this->db->escape_like_str($search));
		$this->db->order_by('person_id', 'asc');
		$by_person_id = $this->db->get();
	
		foreach($by_person_id->result() as $row)
		{
			$suggestions[]=$row->person_id;
		}
	
	//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
	}	
	
/** GARRISON MODIFIED 4/24/2013 **/	
	/*
	Preform a search on giftcards
	*/
	function search($search)
	{
		$this->db->from('giftcards');
		$this->db->join('people','giftcards.person_id=people.person_id');
		$this->db->like("first_name",$this->db->escape_like_str($search));
		$this->db->or_like("last_name",$this->db->escape_like_str($search));
		$this->db->or_like("CONCAT(`first_name`,' ',`last_name`)",$this->db->escape_like_str($search));
		$this->db->or_like("giftcard_number",$this->db->escape_like_str($search));
		$this->db->or_like("giftcards.person_id",$this->db->escape_like_str($search));
		$this->db->where('deleted',$this->db->escape('0'));
		$this->db->order_by("giftcard_number", "asc");
		return $this->db->get();
	}
	
	public function get_giftcard_value( $giftcard_number )
	{
		if ( !$this->exists( $this->get_giftcard_id($giftcard_number)))
			return 0;
		
		$this->db->from('giftcards');
		$this->db->where('giftcard_number',$giftcard_number);
		return $this->db->get()->row()->value;
	}
	
	function update_giftcard_value( $giftcard_number, $value )
	{
		$this->db->where('giftcard_number', $giftcard_number);
		$this->db->update('giftcards', array('value' => $value));
	}
}
?>
