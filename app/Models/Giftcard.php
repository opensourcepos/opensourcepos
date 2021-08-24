<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Giftcard class
 */

class Giftcard extends Model
{
	/*
	* Determines if a given giftcard_id is a giftcard
	*/
	public function exists($giftcard_id): bool
	{
		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_id', $giftcard_id);
		$builder->where('deleted', 0);

		return ($builder->get()->getNumRows() == 1);
	}

	/*
	* Gets max gift card number
	*/
	public function get_max_number()
	{
		$builder = $this->db->table('giftcards');
		$builder->select('CAST(giftcard_number AS UNSIGNED) AS giftcard_number');
		$builder->where('giftcard_number REGEXP', "'^[0-9]+$'", FALSE);
		$builder->orderBy("giftcard_number","desc");
		$builder->limit(1);

		return $builder->get()->getRow();
	}

	/*
	* Gets total of rows
	*/
	public function get_total_rows()
	{
		$builder = $this->db->table('giftcards');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	/*
	* Gets information about a particular giftcard
	*/
	public function get_info($giftcard_id)
	{
		$builder = $this->db->table('giftcards');
		$builder->join('people', 'people.person_id = giftcards.person_id', 'left');
		$builder->where('giftcard_id', $giftcard_id);
		$builder->where('deleted', 0);

		$query = $builder->get();

		if($query->getNumRows() == 1)
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object, as $giftcard_id is NOT an giftcard
			$giftcard_obj = new stdClass();	//TODO: need to sort this out.

			//Get all the fields from giftcards table
			foreach($this->db->getFieldNames('giftcards') as $field)
			{
				$giftcard_obj->$field = '';
			}

			return $giftcard_obj;
		}
	}

	/*
	* Gets an giftcard id given a giftcard number
	*/
	public function get_giftcard_id($giftcard_number): bool
	{
		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcard_number);
		$builder->where('deleted', 0);

		$query = $builder->get();

		if($query->getNumRows() == 1)
		{
			return $query->getRow()->giftcard_id;
		}

		return FALSE;
	}

	/*
	* Gets information about multiple giftcards
	*/
	public function get_multiple_info($giftcard_ids)
	{
		$builder = $this->db->table('giftcards');
		$builder->whereIn('giftcard_id', $giftcard_ids);
		$builder->where('deleted', 0);
		$builder->orderBy('giftcard_number', 'asc');

		return $builder->get();
	}

	/*
	* Inserts or updates a giftcard
	*/
	public function save(&$giftcard_data, $giftcard_id = FALSE): bool
	{
		$builder = $this->db->table('giftcards');

		if(!$giftcard_id || !$this->exists($giftcard_id))
		{
			if($builder->insert('giftcards', $giftcard_data))
			{
				$giftcard_data['giftcard_number'] = $this->db->insertID();
				$giftcard_data['giftcard_id'] = $this->db->insertID();

				return TRUE;
			}

			return FALSE;
		}

		$builder->where('giftcard_id', $giftcard_id);

		return $builder->update('giftcards', $giftcard_data);
	}

	/*
	* Updates multiple giftcards at once
	*/
	public function update_multiple($giftcard_data, $giftcard_ids): bool
	{
		$builder = $this->db->table('giftcards');
		$builder->whereIn('giftcard_id', $giftcard_ids);

		return $builder->update($giftcard_data);
	}

	/*
	* Deletes one giftcard
	*/
	public function delete($giftcard_id): bool
	{
		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_id', $giftcard_id);

		return $builder->update(['deleted' => 1]);
	}

	/*
	* Deletes a list of giftcards
	*/
	public function delete_list($giftcard_ids): bool
	{
		$builder = $this->db->table('giftcards');
		$builder->whereIn('giftcard_id', $giftcard_ids);

		return $builder->update(['deleted' => 1]);
 	}

 	/*
	* Get search suggestions to find giftcards
	*/
	public function get_search_suggestions($search, $limit = 25): array
	{
		$suggestions = [];

		$builder = $this->db->table('giftcards');
		$builder->like('giftcard_number', $search);
		$builder->where('deleted', 0);
		$builder->orderBy('giftcard_number', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[]=array('label' => $row->giftcard_number);
		}

 		$builder = $this->db->table('customers');
		$builder->join('people', 'customers.person_id = people.person_id', 'left');
		$builder->groupStart();
			$builder->like('first_name', $search);
			$builder->orLike('last_name', $search);
			$builder->orLike('CONCAT(first_name, " ", last_name)', $search);
		$builder->groupEnd();
		$builder->where('deleted', 0);
		$builder->orderBy('last_name', 'asc');

		foreach($builder->get()->getResult() as $row)
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
	* Gets gift cards
	*/
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'giftcard_number', 'asc', TRUE);
	}

	/*
	* Performs a search on giftcards
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'giftcard_number', $order = 'asc', $count_only = FALSE)
	{
		$builder = $this->db->table('giftcards');

		// get_found_rows case
		if($count_only == TRUE)
		{
			$builder->select('COUNT(giftcards.giftcard_id) as count');
		}

		$builder->join('people AS person', 'giftcards.person_id = person.person_id', 'left');
		$builder->groupStart();
			$builder->like('person.first_name', $search);
			$builder->orLike('person.last_name', $search);
			$builder->orLike('CONCAT(person.first_name, " ", person.last_name)', $search);
			$builder->orLike('giftcards.giftcard_number', $search);
			$builder->orLike('giftcards.person_id', $search);
		$builder->groupEnd();
		$builder->where('giftcards.deleted', 0);

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $builder->get()->getRow()->count;
		}

		$builder->orderBy($sort, $order);

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/*
	* Gets gift card value
	*/
	public function get_giftcard_value($giftcard_number)
	{
		if( !$this->exists($this->get_giftcard_id($giftcard_number)) )
		{
			return 0;
		}

		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcard_number);

		return $builder->get()->getRow()->value;
	}

	/*
	* Updates gift card value
	*/
	public function update_giftcard_value($giftcard_number, $value)
	{
		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcard_number);
		$builder->update(['value' => $value]);
	}

	/*
	* Determines if a given giftcard_name exists
	*/
	public function exists_gitcard_name($giftcard_name): bool
	{
		$giftcard_name = strtoupper($giftcard_name);

		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcard_name);
		$builder->where('deleted', 0);

		return ($builder->get()->getNumRows() == 1);
	}

	/*
	* Generate unique gift card name/number
	*/
	public function generate_unique_giftcard_name($value): string
	{
		$value = str_replace('.', 'DE', $value);
		$random = bin2hex(openssl_random_pseudo_bytes(3));	//TODO: it wants to add this to composer because it says that it's missing.
		$giftcard_name = (string)$random . '-' . $value;

		if($this->exists_gitcard_name($giftcard_name))
		{
			$this->generate_unique_giftcard_name($value);
		}

		return strtoupper($giftcard_name);
	}

	/*
	* Gets gift card customer
	*/
	public function get_giftcard_customer($giftcard_number): int
	{
		if( !$this->exists($this->get_giftcard_id($giftcard_number)) )
		{
			return 0;
		}

		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcard_number);

		return $builder->get()->getRow()->person_id;
	}
}
?>
