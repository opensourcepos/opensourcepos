<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use stdClass;

/**
 * Giftcard class
 */
class Giftcard extends Model
{
	protected $table = 'giftcards';
	protected $primaryKey = 'giftcard_id';
	protected $useAutoIncrement = true;
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'giftcard_number',
		'value',
		'deleted',
		'person_id',
		'record_time'
	];

	/**
	 * Determines if a given giftcard_id is a giftcard
	 */
	public function exists(int $giftcard_id): bool
	{
		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_id', $giftcard_id);
		$builder->where('deleted', 0);

		return ($builder->get()->getNumRows() == 1);	//TODO: ===
	}

	/**
	 * Gets max gift card number	//TODO: This isn't entirely accurate.  It returns the object and the results then pulls the giftcard_number
	 */
	public function get_max_number(): ?object
	{
		$builder = $this->db->table('giftcards');
		$builder->select('CAST(giftcard_number AS UNSIGNED) AS giftcard_number');
		$builder->where('giftcard_number REGEXP \'^[0-9]+$\' = 0');
		$builder->orderBy("giftcard_number","desc");
		$builder->limit(1);

		return $builder->get()->getRow();
	}

	/**
	 * Gets total of rows
	 */
	public function get_total_rows(): int
	{
		$builder = $this->db->table('giftcards');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	/**
	 * Gets information about a particular giftcard
	 */
	public function get_info(int $giftcard_id): object
	{
		$builder = $this->db->table('giftcards');
		$builder->join('people', 'people.person_id = giftcards.person_id', 'left');
		$builder->where('giftcard_id', $giftcard_id);
		$builder->where('deleted', 0);

		$query = $builder->get();

		if($query->getNumRows() == 1)	//TODO: ===
		{
			return $query->getRow();
		}
		else	//TODO: No need for this else statement.  Just put it's contents outside of the else since the if has a return in it.
		{
			return $this->getEmptyObject('giftcards');
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

		foreach($this->db->getFieldData($table_name) as $field) {

			$field_name = $field->name;

			if(in_array($field->type, array('int', 'tinyint', 'decimal')))
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
	 * Gets a giftcard id given a giftcard number
	 */
	public function get_giftcard_id(string $giftcard_number): bool
	{
		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcard_number);
		$builder->where('deleted', 0);

		$query = $builder->get();

		if($query->getNumRows() == 1)	//TODO: ===
		{
			return $query->getRow()->giftcard_id;
		}

		return false;
	}

	/**
	 * Gets information about multiple giftcards
	 */
	public function get_multiple_info(array $giftcard_ids): ResultInterface
	{
		$builder = $this->db->table('giftcards');
		$builder->whereIn('giftcard_id', $giftcard_ids);
		$builder->where('deleted', 0);
		$builder->orderBy('giftcard_number', 'asc');

		return $builder->get();
	}

	/**
	 * Inserts or updates a giftcard
	 */
	public function save_value(array &$giftcard_data, int $giftcard_id = NEW_ENTRY): bool
	{
		$builder = $this->db->table('giftcards');

		if($giftcard_id == NEW_ENTRY || !$this->exists($giftcard_id))
		{
			if($builder->insert($giftcard_data))
			{
				$giftcard_data['giftcard_number'] = $this->db->insertID();
				$giftcard_data['giftcard_id'] = $this->db->insertID();

				return true;
			}

			return false;
		}

		$builder->where('giftcard_id', $giftcard_id);

		return $builder->update($giftcard_data);
	}

	/**
	 * Updates multiple giftcards at once
	 */
	public function update_multiple(array $giftcard_data, array $giftcard_ids): bool	//TODO: This function appears to never be used in the code.
	{
		$builder = $this->db->table('giftcards');
		$builder->whereIn('giftcard_id', $giftcard_ids);

		return $builder->update($giftcard_data);
	}

	/**
	 * Deletes one giftcard
	 */
	public function delete($giftcard_id = null, bool $purge = false): bool
	{
		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_id', $giftcard_id);

		return $builder->update(['deleted' => 1]);
	}

	/**
	 * Deletes a list of giftcards
	 */
	public function delete_list(array $giftcard_ids): bool
	{
		$builder = $this->db->table('giftcards');
		$builder->whereIn('giftcard_id', $giftcard_ids);

		return $builder->update(['deleted' => 1]);
 	}

 	/**
	 * Get search suggestions to find giftcards
	 */
	public function get_search_suggestions(string $search, int $limit = 25): array
	{
		$suggestions = [];

		$builder = $this->db->table('giftcards');
		$builder->like('giftcard_number', $search);
		$builder->where('deleted', 0);
		$builder->orderBy('giftcard_number', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[]= ['label' => $row->giftcard_number];
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
			$suggestions[] = ['label' => $row->first_name.' '.$row->last_name];
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

	/**
	 * Gets gift cards
	 */
	public function get_found_rows(string $search): int
	{
		return $this->search($search, 0, 0, 'giftcard_number', 'asc', true);
	}

	/**
	 * Performs a search on giftcards
	 */
	public function search(string $search, ?int $rows = 0, ?int $limit_from = 0, ?string $sort = 'giftcard_number', ?string $order = 'asc', ?bool $count_only = false)
	{
		// Set default values
		if($rows == null) $rows = 0;
		if($limit_from == null) $limit_from = 0;
		if($sort == null) $sort = 'giftcard_number';
		if($order == null) $order = 'asc';
		if($count_only == null) $count_only = false;

		// Set default values
		if($rows == null) $rows = 0;
		if($limit_from == null) $limit_from = 0;
		if($sort == null) $sort = 'giftcard_number';
		if($order == null) $order = 'asc';
		if($count_only == null) $count_only = false;

		$builder = $this->db->table('giftcards');

		// get_found_rows case
		if($count_only)	//TODO: replace this with `if($count_only)`
		{
			$builder->select('COUNT(giftcard_id) as count');
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
		if($count_only)
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

	/**
	 * Gets gift card value
	 */
	public function get_giftcard_value(string $giftcard_number): float	//TODO: we may need to do a search for all float values and for currencies cast them to strings at the point where we get them from the database.
	{
		if(!$this->exists($this->get_giftcard_id($giftcard_number)))
		{
			return 0;
		}

		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcard_number);

		return $builder->get()->getRow()->value;
	}

	/**
	 * Updates gift card value
	 */
	public function update_giftcard_value(string $giftcard_number, float $value): void	//TODO: Should we return the value of update like other similar functions do?
	{
		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcard_number);
		$builder->update(['value' => $value]);
	}

	/**
	 * Determines if a given giftcard_name exists
	 */
	public function exists_giftcard_name($giftcard_name): bool
	{
		$giftcard_name = strtoupper($giftcard_name);

		$builder = $this->db->table('giftcards');
		$builder->where('giftcard_number', $giftcard_name);
		$builder->where('deleted', 0);

		return ($builder->get()->getNumRows() == 1);	//TODO: ===
	}

	/**
	 * Generate unique gift card name/number
	 */
	public function generate_unique_giftcard_name(string $value): string
	{
		$value = str_replace('.', 'DE', $value);
		$random = bin2hex(openssl_random_pseudo_bytes(3));
		$giftcard_name = "$random-$value";

		if($this->exists_giftcard_name($giftcard_name))
		{
			$this->generate_unique_giftcard_name($value);
		}

		return strtoupper($giftcard_name);
	}

	/**
	 * Gets gift card customer
	 */
	public function get_giftcard_customer(string $giftcard_number): int
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
