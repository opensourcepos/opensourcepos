<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Giftcard class
 */

class Giftcard extends CI_Model
{
	/*
	Determines if a given giftcard_id is a giftcard
	*/
	public function exists($giftcard_id)
	{
		$this->db->from('giftcards');
		$this->db->where('giftcard_id', $giftcard_id);
		$this->db->where('deleted', 0);

		return ($this->db->get()->num_rows() == 1);
	}

	/*
	Gets max gift card number
	*/
	public function get_max_number()
	{
		$this->db->select('CAST(giftcard_number AS UNSIGNED) AS giftcard_number');
		$this->db->from('giftcards');
		$this->db->where('giftcard_number REGEXP', "'^[0-9]+$'", FALSE);
		$this->db->order_by("giftcard_number","desc");
		$this->db->limit(1);

		return $this->db->get()->row();
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('giftcards');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/*
	Gets information about a particular giftcard
	*/
	public function get_info($giftcard_id)
	{
		$this->db->from('giftcards');
		$this->db->join('people', 'people.person_id = giftcards.person_id', 'left');
		$this->db->where('giftcard_id', $giftcard_id);
		$this->db->where('deleted', 0);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $giftcard_id is NOT an giftcard
			$giftcard_obj = new stdClass();

			//Get all the fields from giftcards table
			foreach($this->db->list_fields('giftcards') as $field)
			{
				$giftcard_obj->$field = '';
			}

			return $giftcard_obj;
		}
	}

	/*
	Gets an giftcard id given a giftcard number
	*/
	public function get_giftcard_id($giftcard_number)
	{
		$this->db->from('giftcards');
		$this->db->where('giftcard_number', $giftcard_number);
		$this->db->where('deleted', 0);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row()->giftcard_id;
		}

		return FALSE;
	}

	/*
	Gets information about multiple giftcards
	*/
	public function get_multiple_info($giftcard_ids)
	{
		$this->db->from('giftcards');
		$this->db->where_in('giftcard_id', $giftcard_ids);
		$this->db->where('deleted', 0);
		$this->db->order_by('giftcard_number', 'asc');

		return $this->db->get();
	}

	/*
	Inserts or updates a giftcard
	*/
	public function save(&$giftcard_data, $giftcard_id = FALSE)
	{
		if(!$giftcard_id || !$this->exists($giftcard_id))
		{
			if($this->db->insert('giftcards', $giftcard_data))
			{
				$giftcard_data['giftcard_number'] = $this->db->insert_id();
				$giftcard_data['giftcard_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('giftcard_id', $giftcard_id);

		return $this->db->update('giftcards', $giftcard_data);
	}

	/*
	Updates multiple giftcards at once
	*/
	public function update_multiple($giftcard_data, $giftcard_ids)
	{
		$this->db->where_in('giftcard_id', $giftcard_ids);

		return $this->db->update('giftcards', $giftcard_data);
	}

	/*
	Deletes one giftcard
	*/
	public function delete($giftcard_id)
	{
		$this->db->where('giftcard_id', $giftcard_id);

		return $this->db->update('giftcards', array('deleted' => 1));
	}

	/*
	Deletes a list of giftcards
	*/
	public function delete_list($giftcard_ids)
	{
		$this->db->where_in('giftcard_id', $giftcard_ids);

		return $this->db->update('giftcards', array('deleted' => 1));
 	}

 	/*
	Get search suggestions to find giftcards
	*/
	public function get_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		$this->db->from('giftcards');
		$this->db->like('giftcard_number', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('giftcard_number', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[]=array('label' => $row->giftcard_number);
		}

 		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id = people.person_id', 'left');
		$this->db->group_start();
			$this->db->like('first_name', $search);
			$this->db->or_like('last_name', $search);
			$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
		$this->db->group_end();
		$this->db->where('deleted', 0);
		$this->db->order_by('last_name', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('label' => $row->first_name.' '.$row->last_name);
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

	/*
	Gets gift cards
	*/
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'giftcard_number', 'asc', TRUE);
	}

	/*
	Performs a search on giftcards
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'giftcard_number', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(giftcards.giftcard_id) as count');
		}

		$this->db->from('giftcards AS giftcards');
		$this->db->join('people AS person', 'giftcards.person_id = person.person_id', 'left');
		$this->db->group_start();
			$this->db->like('person.first_name', $search);
			$this->db->or_like('person.last_name', $search);
			$this->db->or_like('CONCAT(person.first_name, " ", person.last_name)', $search);
			$this->db->or_like('giftcards.giftcard_number', $search);
			$this->db->or_like('giftcards.person_id', $search);
		$this->db->group_end();
		$this->db->where('giftcards.deleted', 0);

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

	/*
	Gets gift card value
	*/
	public function get_giftcard_value($giftcard_number)
	{
		if( !$this->exists($this->get_giftcard_id($giftcard_number)) )
		{
			return 0;
		}

		$this->db->from('giftcards');
		$this->db->where('giftcard_number', $giftcard_number);

		return $this->db->get()->row()->value;
	}

	/*
	Updates gift card value
	*/
	public function update_giftcard_value($giftcard_number, $value)
	{
		$this->db->where('giftcard_number', $giftcard_number);
		$this->db->update('giftcards', array('value' => $value));
	}

	/*
	Determines if a given giftcard_name esists
	*/
	public function exists_gitcard_name($giftcard_name)
	{
		$giftcard_name = strtoupper($giftcard_name);
		$this->db->from('giftcards');
		$this->db->where('giftcard_number', $giftcard_name);
		$this->db->where('deleted', 0);

		return ($this->db->get()->num_rows() == 1);
	}

	/*
	Generate unique gift card name/number
	*/
	public function generate_unique_giftcard_name($value)
	{
		$value = str_replace('.', 'DE', $value);
		$random = bin2hex(openssl_random_pseudo_bytes(3));
		$giftcard_name = (string)$random . '-' . $value;
		if($this->exists_gitcard_name($giftcard_name))
		{
			$this->generate_unique_giftcard_name($value);
		}

		return strtoupper($giftcard_name);
	}

	/*
	Gets gift card customer
	*/
	public function get_giftcard_customer($giftcard_number)
	{
		if( !$this->exists($this->get_giftcard_id($giftcard_number)) )
		{
			return 0;
		}

		$this->db->from('giftcards');
		$this->db->where('giftcard_number', $giftcard_number);

		return $this->db->get()->row()->person_id;
	}
}
?>
