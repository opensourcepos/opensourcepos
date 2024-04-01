<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use stdClass;

/**
 * Base class for People classes
 */
class Person extends Model
{
	protected $table = 'people';
	protected $primaryKey = 'person_id';
	protected $useAutoIncrement = true;
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'first_name',
		'last_name',
		'phone_number',
		'email',
		'address_1',
		'address_2',
		'city',
		'state',
		'zip',
		'country',
		'comments',
		'gender'
	];

	/**
	 * Determines whether the given person exists in the people database table
	 *
	 * @param integer $person_id identifier of the person to verify the existence
	 *
	 * @return boolean true if the person exists, false if not
	 */
	public function exists(int $person_id): bool
	{
		$builder = $this->db->table('people');
		$builder->where('people.person_id', $person_id);

		return ($builder->get()->getNumRows() == 1);	//TODO: ===
	}

	/**
	 * Gets all people from the database table
	 *
	 * @param integer $limit limits the query return rows
	 * @param integer $offset offset the query
	 */
	public function get_all(int $limit = 10000, int $offset = 0): ResultInterface
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
	 * @return object containing all the fields of the table row
	 */
	public function get_info(int $person_id): object
	{
		$builder = $this->db->table('people');
		$query = $builder->getWhere(['person_id' => $person_id], 1);

		if($query->getNumRows() == 1)
		{
			return $query->getRow();
		}
		else
		{
			return $this->getEmptyObject('people');
		}
	}


	/**
	 * Initializes an empty object based on database definitions
	 * @param string $table_name
	 * @return object
	 */
	private function getEmptyObject(string $table_name): object
	{
		// Return an empty base parent object, as $item_id is NOT an item
		$empty_obj = new stdClass();

		// Iterate through field definitions to determine how the fields should be initialized
		foreach($this->db->getFieldData($table_name) as $field)
		{
			$field_name = $field->name;

			if(in_array($field->type, ['int', 'tinyint', 'decimal']))
			{
				$empty_obj->$field_name = ($field->primary_key == 1) ? NEW_ENTRY : 0;
			}
			else
			{
				$empty_obj->$field_name = null;
			}
		}

		return $empty_obj;
	}

	/**
	 * Gets information about people as an array of rows
	 *
	 * @param array $person_ids array of people identifiers
	 *
	 */
	public function get_multiple_info(array $person_ids): ResultInterface
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
	 * @param int $person_id identifier of the person to update the information
	 * @return boolean true if the save was successful, false if not
	 */
	public function save_value(array &$person_data, int $person_id = NEW_ENTRY): bool
	{
		$builder = $this->db->table('people');

		if($person_id == NEW_ENTRY || !$this->exists($person_id))
		{
			if($builder->insert($person_data))
			{
				$person_data['person_id'] = $this->db->insertID();

				return true;
			}

			return false;
		}

		$builder->where('person_id', $person_id);

		return $builder->update($person_data);
	}

	/**
	 * Get search suggestions to find person
	 *
	 * @param string $search string containing the term to search in the people table
	 * @param int $limit limit the search
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
			$suggestions[] = ['label' => $row->person_id];
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
	 * @param integer $person_id person identifier
	 * @return boolean always true
	 */
	public function delete($person_id = null, bool $purge = false): bool
	{
		return true;
	}

	/**
	 * Deletes a list of people (dummy base function)
	 *
	 * @param array $person_ids list of person identifiers
	 * @return boolean always true
	 */
	public function delete_list(array $person_ids): bool
	{
		return true;
 	}
}
