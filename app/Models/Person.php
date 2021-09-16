<?php

namespace App\Models;

use CodeIgniter\Model;
use stdClass;

/**
 * Base class for People classes
 */

class Person extends Model
{
	/**
	 * Determines whether the given person exists in the people database table
	 *
	 * @param integer $person_id identifier of the person to verify the existence
	 *
	 * @return boolean TRUE if the person exists, FALSE if not
	 */
	public function exists(int $person_id): bool
	{
		$builder = $this->db->table('people');
		$builder->where('people.person_id', $person_id);

		return ($builder->get()->getNumRows() == 1);
	}

	/**
	 * Gets all people from the database table
	 *
	 * @param integer $limit limits the query return rows
	 *
	 * @param integer $offset offset the query
	 *
	 * @return array array of people table rows	//TODO: I don't think get() returns an array... I think we may need to use get_array() here.
	 */
	public function get_all(int $limit = 10000, int $offset = 0)
	{
		$builder = $this->db->table('people');
		$builder->orderBy('last_name', 'asc');
		$builder->limit($limit);
		$builder->offset($offset);

		return $builder->get();
	}

	/**
	 * Gets total of rows of people database table
	 *
	 * @return integer row counter
	 */
	public function get_total_rows(): int
	{
		$builder = $this->db->table('people');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	/**
	 * Gets information about a person as an array
	 *
	 * @param integer $person_id identifier of the person
	 *
	 * @return array containing all the fields of the table row	//TODO: $person_obj is of type stdClass but the PHPDoc here says array
	 */
	public function get_info(int $person_id)
	{
		$builder = $this->db->table('people');
		$query = $builder->getWhere(['person_id' => $person_id], 1);

		if($query->getNumRows() == 1)
		{
			return $query->getRow();
		}
		else
		{
			//create object with empty properties.
			$person_obj = new stdClass();

			foreach($this->db->getFieldNames('people') as $field)
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
	 * @return array containing all the fields of the table row	//TODO: I don't think get() returns an array... I think we may need to use get_array() here.
	 */
	public function get_multiple_info(array $person_ids)
	{
		$builder = $this->db->table('people');
		$builder->whereIn('person_id', $person_ids);
		$builder->orderBy('last_name', 'asc');

		return $builder->get();
	}

	/**
	 * Inserts or updates a person
	 *
	 * @param array $person_data array containing person information
	 *
	 * @param bool $person_id identifier of the person to update the information
	 *
	 * @return boolean TRUE if the save was successful, FALSE if not
	 */
	public function save(array &$person_data, bool $person_id = FALSE): bool
	{
		$builder = $this->db->table('people');

		if(!$person_id || !$this->exists($person_id))
		{
			if($builder->insert($person_data))
			{
				$person_data['person_id'] = $this->db->insertID();

				return TRUE;
			}

			return FALSE;
		}

		$builder->where('person_id', $person_id);

		return $builder->update($person_data);
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
	public function get_search_suggestions(string $search, int $limit = 25): array
	{
		$suggestions = [];

		$builder = $this->db->table('people');

//TODO: If this won't be added back into the code later, we should delete this commented section of code
//		$builder->select('person_id');
//		$builder->where('deleted', 0);
//		$builder->where('person_id', $search);
//		$builder->groupStart();
//			$builder->like('first_name', $search);
//			$builder->orLike('last_name', $search);
//			$builder->orLike('CONCAT(first_name, " ", last_name)', $search);
//			$builder->orLike('email', $search);
//			$builder->orLike('phone_number', $search);
//			$builder->groupEnd();
//		$builder->orderBy('last_name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = ['label' => $row->person_id);
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
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
	public function delete(int $person_id = null, bool $purge = false): bool
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
	public function delete_list(array $person_ids): bool
	{
		return TRUE;
 	}
}
?>
