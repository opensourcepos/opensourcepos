<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use stdClass;

/**
 * Tax class
 */
class Tax extends Model
{
	protected $table = 'tax_rates';
	protected $primaryKey = 'tax_rate_id';
	protected $useAutoIncrement = true;
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'rate_tax_code_id',
		'rate_tax_category_id',
		'rate_jurisdiction_id',
		'tax_rate',
		'tax_rounding_code'
	];

	/**
	 * Determines if a given row is on file
	 */
	public function exists(int $tax_rate_id): bool
	{
		$builder = $this->db->table('tax_rates');
		$builder->where('tax_rate_id', $tax_rate_id);

		return ($builder->get()->getNumRows() == 1);	//TODO: ===
	}

	/**
	 * Gets total of rows
	 */
	public function get_total_rows(): int
	{
		$builder = $this->db->table('tax_rates');

		return $builder->countAllResults();
	}

	/**
	 * Gets list of tax rates that are assigned to a particular tax category
	 */
	public function get_tax_category_usage(int $tax_category_id): int
	{
		$builder = $this->db->table('tax_rates');
		$builder->where('rate_tax_category_id', $tax_category_id);

		return $builder->countAllResults();
	}

	/**
	 * Gets the row for a particular id
	 */
	public function get_info(int $tax_rate_id): object
	{
		$builder = $this->db->table('tax_rates');
		$builder->select('tax_rate_id');
		$builder->select('rate_tax_code_id');
		$builder->select('tax_code');
		$builder->select('tax_code_name');
		$builder->select('rate_jurisdiction_id');
		$builder->select('jurisdiction_name');
		$builder->select('rate_tax_category_id');
		$builder->select('tax_category');
		$builder->select('tax_rate');
		$builder->select('tax_rounding_code');

		$builder->join('tax_codes',
			'rate_tax_code_id = tax_code_id', 'LEFT');
		$builder->join('tax_categories',
			'rate_tax_category_id = tax_category_id', 'LEFT');
		$builder->join('tax_jurisdictions',
			'rate_jurisdiction_id = jurisdiction_id', 'LEFT');
		$builder->where('tax_rate_id', $tax_rate_id);

		$query = $builder->get();

		if($query->getNumRows() == 1)	//TODO: probably should use === here since getNumRows() returns an int.
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object
			$tax_rate_obj = new stdClass();
			$tax_rate_obj->tax_rate_id = null;
			$tax_rate_obj->rate_tax_code_id = null;
			$tax_rate_obj->tax_code = '';
			$tax_rate_obj->tax_code_name = '';
			$tax_rate_obj->rate_tax_category_id = null;
			$tax_rate_obj->tax_category = '';
			$tax_rate_obj->tax_rate = 0.0;
			$tax_rate_obj->tax_rounding_code = '0';
			$tax_rate_obj->rate_jurisdiction_id = null;
			$tax_rate_obj->jurisdiction_name = '';

			return $tax_rate_obj;
		}
	}

	/**
	 * Get taxes to be collected for a given tax code
	 */
	 public function get_taxes(int $tax_code_id, int $tax_category_id): array
	 {
		 $sql = 'select tax_rate_id, rate_tax_code_id, tax_code, tax_code_name, tax_type, cascade_sequence, rate_tax_category_id, tax_category, 
			rate_jurisdiction_id, jurisdiction_name, tax_group, tax_rate, tax_rounding_code,tax_categories.tax_group_sequence + tax_jurisdictions.tax_group_sequence as tax_group_sequence 
			from ' . $this->db->prefixTable('tax_rates') . ' 
			left outer join ' . $this->db->prefixTable('tax_codes') . ' on rate_tax_code_id = tax_code_id 
			left outer join ' . $this->db->prefixTable('tax_categories') . ' as tax_categories on rate_tax_category_id = tax_category_id 
			left outer join ' . $this->db->prefixTable('tax_jurisdictions') . ' as tax_jurisdictions on rate_jurisdiction_id = jurisdiction_id 
			where rate_tax_code_id = ' . $this->db->escape($tax_code_id) . ' and rate_tax_category_id = ' . $this->db->escape($tax_category_id) . '
			order by cascade_sequence, tax_group, jurisdiction_name, tax_jurisdictions.tax_group_sequence + tax_categories.tax_group_sequence';

		$query = $this->db->query($sql);

		return $query->getResultArray();
	}

	/**
	 * Gets information about a particular tax_code
	 */
	public function get_rate_info(int $tax_code_id, int $tax_category_id): object
	{
		$builder = $this->db->table('tax_rates');
		$builder->join('tax_categories', 'rate_tax_category_id = tax_category_id');
		$builder->where('rate_tax_code_id', $tax_code_id);
		$builder->where('rate_tax_category_id', $tax_category_id);

		$query = $builder->get();

		if($query->getNumRows() == 1)	//TODO: this should probably be ===
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object
			$tax_rate_obj = new stdClass();

			//Get all the fields from tax_codes table
			foreach($this->db->getFieldNames('tax_rates') as $field)
			{
				$tax_rate_obj->$field = '';
			}
			//Get all the fields from tax_rates table
			foreach($this->db->getFieldNames('tax_categories') as $field)
			{
				$tax_rate_obj->$field = '';
			}

			return $tax_rate_obj;
		}
	}

	/**
	Inserts or updates a tax_rates entry
	*/
	public function save_value(array &$tax_rate_data, int $tax_rate_id = NEW_ENTRY): bool
	{
		$builder = $this->db->table('tax_rates');
		if($tax_rate_id == NEW_ENTRY || !$this->exists($tax_rate_id))
		{
			if($builder->insert($tax_rate_data))
			{
				$tax_rate_data['tax_rate_id'] = $this->db->insertID();

				return true;
			}
		}
		else
		{
			$builder->where('tax_rate_id', $tax_rate_id);

			if($builder->update($tax_rate_data))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Deletes a single tax rate entry
	 */
	public function delete($tax_rate_id = null, bool $purge = false): bool
	{
		$builder = $this->db->table('tax_rates');

		return $builder->delete(['tax_rate_id' => $tax_rate_id]);
	}

	/**
	 * Deletes a list of tax rates
	 */
	public function delete_list(array $tax_rate_ids): bool
	{
		$builder = $this->db->table('tax_rates');
		$builder->whereIn('tax_rate_id', $tax_rate_ids);

		return $builder->delete();
	}

	/**
	 * Gets tax_codes
	 */
	public function get_found_rows(string $search): int
	{
		return $this->search($search, 0, 0, 'tax_code_name', 'asc', true);
	}

	/**
	 * Performs a search on tax_rates
	 */
	public function search(string $search, ?int $rows = 0, ?int $limit_from = 0, ?string $sort = 'tax_code_name', ?string $order = 'asc', ?bool $count_only = false)
	{
		// Set default values
		if($rows == null) $rows = 0;
		if($limit_from == null) $limit_from = 0;
		if($sort == null) $sort = 'tax_code_name';
		if($order == null) $order = 'asc';
		if($count_only == null) $count_only = false;

		$builder = $this->db->table('tax_rates');

		// get_found_rows case
		if($count_only)
		{
			$builder->select('COUNT(tax_rate_id) as count');
		} else
		{
			$builder->select('tax_rate_id');
			$builder->select('tax_code');
			$builder->select('rate_tax_code_id');
			$builder->select('tax_code_name');
			$builder->select('rate_jurisdiction_id');
			$builder->select('jurisdiction_name');
			$builder->select('rate_tax_category_id');
			$builder->select('tax_category');
			$builder->select('tax_rate');
			$builder->select('tax_rounding_code');
		}

		$builder->join('tax_codes', 'rate_tax_code_id = tax_code_id', 'LEFT');
		$builder->join('tax_categories', 'rate_tax_category_id = tax_category_id', 'LEFT');
		$builder->join('tax_jurisdictions', 'rate_jurisdiction_id = jurisdiction_id', 'LEFT');

		if(!empty($search))
		{
			$builder->like('rate_tax_code', $search);
			$builder->orLike('tax_code_name', $search);
		}

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
	 * @param string $tax_code_type
	 * @return string
	 */
	public function get_tax_code_type_name(string $tax_code_type): string	//TODO: if this is being called from the view and passed through GET params then it will come through as a string... better if we can get it as an int though.
	{
		if($tax_code_type == '0')	//TODO: ===.  Also, replace this with ternary notation. The whole function becomes a nice one-liner.
		{
			return lang('Taxes.tax_included');
		}
		else
		{
			return lang('Taxes.tax_excluded');
		}
	}

	/**
	 * @param int $tax_category_id
	 * @return string
	 */
	public function get_tax_category(int $tax_category_id): string
	{
		$builder = $this->db->table('tax_categories');
		$builder->select('tax_category');
		$builder->where('tax_category_id', $tax_category_id);

		return $builder->get()->getRow()->tax_category;
	}

	/**
	 * @return ResultInterface
	 */
	public function get_all_tax_categories(): ResultInterface
	{
		$builder = $this->db->table('tax_categories');
		$builder->orderBy('tax_category_id');

		return $builder->get();
	}

	/**
	 * @param string $tax_category
	 * @return int
	 */
	public function get_tax_category_id(string $tax_category): int	//TODO: $tax_category is not used in this function and get_tax_category_id() is not called in the code.  It may be that this needs to be deprecated and removed.
	{
		$builder = $this->db->table('tax_categories');
		$builder->select('tax_category_id');

		return $builder->get()->getRow()->tax_category_id;
	}
}
