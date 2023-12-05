<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use stdClass;

/**
 * Tax Category class
 */

class Tax_category extends Model
{
	protected $table = 'tax_categories';
	protected $primaryKey = 'tax_category_id';
	protected $useAutoIncrement = true;
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'tax_category',
		'tax_group_sequence',
		'deleted'
	];

	/**
	 *  Determines if it exists in the table
	 */
	public function exists(int $tax_category_id): bool
	{
		$builder = $this->db->table('tax_categories');
		$builder->where('tax_category_id', $tax_category_id);

		return ($builder->get()->getNumRows() == 1);	//TODO: probably should be ===
	}

	/**
	 *  Gets total of rows
	 */
	public function get_total_rows(): int
	{
		$builder = $this->db->table('tax_categories');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	/**
	 * Gets information about the particular record
	 */
	public function get_info(int $tax_category_id): object
	{
		$builder = $this->db->table('tax_categories');
		$builder->where('tax_category_id', $tax_category_id);
		$builder->where('deleted', 0);
		$query = $builder->get();

		if($query->getNumRows() == 1)	//TODO: probably should be === since getNumRows returns an int
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object
			$tax_category_obj = new stdClass();

			//Get all the fields from the table
			foreach($this->db->getFieldNames('tax_categories') as $field)
			{
				$tax_category_obj->$field = '';	//TODO: This logic doesn't make sense to me... it appears that each field is being assigned to '' rather than the result.  Shouldn't this be $tax_category_obj->field = $field;?
			}
			return $tax_category_obj;
		}
	}

	/**
	 *  Returns all rows from the table
	 *///TODO: I think we should work toward having all these get_all functions with the same signature.  It makes it easier to use them.  This signature is different from the others.
	public function get_all(int $rows = 0, int $limit_from = 0, bool $no_deleted = true): ResultInterface	//TODO: $no_deleted needs a new name.  $not_deleted is the correct grammar, but it's a bit confusing by naming the variable a negative.  Probably better to name it is_deleted and flip the logic
	{
		$builder = $this->db->table('tax_categories');
		if($no_deleted)
		{
			$builder->where('deleted', 0);
		}

		$builder->orderBy('tax_group_sequence', 'asc');
		$builder->orderBy('tax_category', 'asc');

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/**
	 *  Returns multiple rows
	 */
	public function get_multiple_info(array $tax_category_ids): ResultInterface
	{
		$builder = $this->db->table('tax_categories');
		$builder->whereIn('tax_category_id', $tax_category_ids);
		$builder->orderBy('tax_category', 'asc');

		return $builder->get();
	}

	/**
	 *  Inserts or updates a row
	 */
	public function save_value(array &$tax_category_data, int $tax_category_id = NEW_ENTRY): bool
	{
		$builder = $this->db->table('tax_categories');

		if($tax_category_id == NEW_ENTRY || !$this->exists($tax_category_id))
		{
			if($builder->insert($tax_category_data))
			{
				$tax_category_data['tax_category_id'] = $this->db->insertID();

				return true;
			}

			return false;
		}

		$builder->where('tax_category_id', $tax_category_id);

		return $builder->update($tax_category_data);
	}

	/**
	 * Saves changes to the tax categories table
	 */
	public function save_categories(array $array_save): bool	//TODO: $array_save probably needs to be renamed here to $categories or something similar.  Datatype in the variable name is a code smell.
	{
		$this->db->transStart();

		$not_to_delete = [];

		foreach($array_save as $key => $value)
		{
			// save or update
			$tax_category_data = [
				'tax_category' => $value['tax_category'],
				'tax_group_sequence' => $value['tax_group_sequence'],
				'deleted' => '0'
			];

			$this->save_value($tax_category_data, $value['tax_category_id']);

			if($value['tax_category_id'] == NEW_ENTRY)
			{
				$not_to_delete[] = $tax_category_data['tax_category_id'];
			}
			else
			{
				$not_to_delete[] = $value['tax_category_id'];
			}
		}

		// all entries not available in post will be deleted now
		$deleted_tax_categories = $this->get_all()->getResultArray();

		foreach($deleted_tax_categories as $key => $tax_category_data)
		{
			if(!in_array($tax_category_data['tax_category_id'], $not_to_delete))
			{
				$this->delete($tax_category_data['tax_category_id']);
			}
		}

		$this->db->transComplete();
		return $this->db->transStatus();
	}

	/**
	 * Soft delete a specific row
	 */
	public function delete($tax_category_id = null, bool $purge = false): bool
	{
		$builder = $this->db->table('tax_categories');
		$builder->where('tax_category_id', $tax_category_id);

		return $builder->update(['deleted' => 1]);
	}

	/**
	 * Deletes a list of rows
	 */
	public function delete_list(array $tax_category_ids): bool
	{
		$builder = $this->db->table('tax_categories');
		$builder->whereIn('tax_category_id', $tax_category_ids);

		return $builder->update(['deleted' => 1]);
 	}

	/**
	 * Gets rows
	 */
	public function get_found_rows(string $search): int
	{
		return $this->search($search, 0, 0, 'tax_category', 'asc', true);
	}

	/**
	 *  Perform a search for a set of rows
	 */
	public function search(string $search, ?int $rows = 0, ?int $limit_from = 0, ?string $sort = 'tax_category', ?string $order = 'asc', ?bool $count_only = false)
	{
		// Set default values
		if($rows == null) $rows = 0;
		if($limit_from == null) $limit_from = 0;
		if($sort == null) $sort = 'tax_category';
		if($order == null) $order = 'asc';
		if($count_only == null) $count_only = false;

		$builder = $this->db->table('tax_categories AS tax_categories');

		// get_found_rows case
		if($count_only)
		{
			$builder->select('COUNT(tax_categories.tax_category_id) as count');
		}

		$builder->like('tax_category', $search);
		$builder->where('deleted', 0);

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
	 * @param string $search
	 * @return array
	 */
	public function get_tax_category_suggestions(string $search): array
	{
		$suggestions = [];

		$builder = $this->db->table('tax_categories');
		$builder->where('deleted', 0);

		if(!empty($search))
		{
			$builder->like('tax_category', '%'.$search.'%');
		}

		$builder->orderBy('tax_category', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = ['value' => $row->tax_category_id, 'label' => $row->tax_category];
		}

		return $suggestions;
	}

	/**
	 * @return array[]
	 */
	public function get_empty_row(): array
	{
		return [
			'0' => [
				'tax_category_id' => NEW_ENTRY,
				'tax_category' => '',
				'tax_group_sequence' => '',
				'deleted' => ''
			]
		];
	}
}
