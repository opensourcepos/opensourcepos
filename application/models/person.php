<?php
class Person extends CI_Model 
{
	/*Determines whether the given person exists*/
	function exists($person_id)
	{
		$this->db->from('people');	
		$this->db->where('people.person_id',$person_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	/*Gets all people*/
	function get_all($limit=10000, $offset=0)
	{
		$this->db->from('people');
		$this->db->order_by("last_name", "asc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();		
	}
	
	function count_all()
	{
		$this->db->from('people');
		$this->db->where('deleted',0);
		return $this->db->count_all_results();
	}
	
	/*
	Gets information about a person as an array.
	*/
	function get_info($person_id)
	{
		$query = $this->db->get_where('people', array('person_id' => $person_id), 1);
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//create object with empty properties.
			$fields = $this->db->list_fields('people');
			$person_obj = new stdClass;
			
			foreach ($fields as $field)
			{
				$person_obj->$field='';
			}
			
			return $person_obj;
		}
	}
	
	/*
	Get people with specific ids
	*/
	function get_multiple_info($person_ids)
	{
		$this->db->from('people');
		$this->db->where_in('person_id',$person_ids);
		$this->db->order_by("last_name", "asc");
		return $this->db->get();		
	}
	
	/*
	Inserts or updates a person
	*/
	function save(&$person_data,$person_id=false)
	{		
		if (!$person_id or !$this->exists($person_id))
		{
			if ($this->db->insert('people',$person_data))
			{
				$person_data['person_id']=$this->db->insert_id();
				return true;
			}
			
			return false;
		}
		
		$this->db->where('person_id', $person_id);
		return $this->db->update('people',$person_data);
	}
	
	/*
	Deletes one Person (doesn't actually do anything)
	*/
	function delete($person_id)
	{
		return true;; 
	}
	
	/*
	Deletes a list of people (doesn't actually do anything)
	*/
	function delete_list($person_ids)
	{	
		return true;	
 	}
	
}
?>
