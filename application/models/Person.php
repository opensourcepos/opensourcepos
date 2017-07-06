<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Base class for People classes
 */

class Person extends CI_Model
{
	/**
	 * Determines whether the given person exists in the people database table
	 *
	 * @param integer $person_id identifier of the person to verify the existence
	 *
	 * @return boolean TRUE if the person exists, FALSE if not
	 */
	public function exists($person_id)
	{
		$this->db->from('people');
		$this->db->where('people.person_id', $person_id);

		return ($this->db->get()->num_rows() == 1);
	}

	/**
	 * Gets all people from the database table
	 *
	 * @param integer $limit limits the query return rows
	 *
	 * @param integer $offset offset the query
	 *
	 * @return array array of people table rows
	 */
	public function get_all($limit = 10000, $offset = 0)
	{
		$this->db->from('people');
		$this->db->order_by('last_name', 'asc');
		$this->db->limit($limit);
		$this->db->offset($offset);

		return $this->db->get();
	}

	/**
	 * Gets total of rows of people database table
	 *
	 * @return integer row counter
	 */
	public function get_total_rows()
	{
		$this->db->from('people');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/**
	 * Gets information about a person as an array
	 *
	 * @param integer $person_id identifier of the person
	 *
	 * @return array containing all the fields of the table row
	 */
	public function get_info($person_id)
	{
		$query = $this->db->get_where('people', array('person_id' => $person_id), 1);

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//create object with empty properties.
			$person_obj = new stdClass;

			foreach($this->db->list_fields('people') as $field)
			{
				$person_obj->$field = '';
			}

			return $person_obj;
		}
	}

	/**
	 * Gets information about people as an array of rows
	 *
	 * @param array $person_ids array of people identifiers
	 *
	 * @return array containing all the fields of the table row
	 */
	public function get_multiple_info($person_ids)
	{
		$this->db->from('people');
		$this->db->where_in('person_id', $person_ids);
		$this->db->order_by('last_name', 'asc');

		return $this->db->get();
	}

	/**
	 * Inserts or updates a person
	 *
	 * @param array $person_data array containing person information
	 *
	 * @param var $person_id identifier of the person to update the information
	 *
	 * @return boolean TRUE if the save was successful, FALSE if not
	 */
	public function save(&$person_data, $person_id = FALSE)
	{
		if(!$person_id || !$this->exists($person_id))
		{
			if($this->db->insert('people', $person_data))
			{
				$person_data['person_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('person_id', $person_id);

		return $this->db->update('people', $person_data);
	}

	/**
	 * Get search suggestions to find person
	 *
	 * @param string $search string containing the term to search in the people table
	 *
	 * @param integer $limit limit the search
	 *
	 * @return array array with the suggestion strings
	 */
	public function get_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

//		$this->db->select('person_id');
//		$this->db->from('people');
//		$this->db->where('deleted', 0);
//		$this->db->where('person_id', $search);
//		$this->db->group_start();
//			$this->db->like('first_name', $search);
//			$this->db->or_like('last_name', $search);
//			$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
//			$this->db->or_like('email', $search);
//			$this->db->or_like('phone_number', $search);
//			$this->db->group_end();
//		$this->db->order_by('last_name', 'asc');

		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('label' => $row->person_id);
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

	/**
	 * Deletes one Person (dummy base function)
	 *
	 * @param integer $person_id person identificator
	 *
	 * @return boolean always TRUE
	 */
	public function delete($person_id)
	{
		return TRUE;
	}

	/**
	 * Deletes a list of people (dummy base function)
	 *
	 * @param array $person_ids list of person identificators
	 *
	 * @return boolean always TRUE
	 */
	public function delete_list($person_ids)
	{
		return TRUE;
 	}
}
?>
