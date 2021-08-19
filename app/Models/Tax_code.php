<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Tax Code class
 */

class Tax_code extends Model
{
	/**
	 *  Determines if it exists in the table
	 */
	public function exists($tax_code)
	{
		$builder = $this->db->table('tax_codes');
		$builder->where('tax_code', $tax_code);

		return ($builder->get()->getNumRows() == 1);
	}

	/**
	 *  Gets total of rows
	 */
	public function get_total_rows()
	{
		$builder = $this->db->table('tax_codes');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	/**
	 * Gets information about the particular record
	 */
	public function get_info($tax_code_id)
	{
		$builder = $this->db->table('tax_codes');
		$builder->where('tax_code_id', $tax_code_id);
		$builder->where('deleted', 0);
		$query = $builder->get();

		if($query->getNumRows()==1)
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object
			$tax_code_obj = new stdClass();

			//Get all the fields from the table
			foreach($this->db->list_fields('tax_codes') as $field)
			{
				$tax_code_obj->$field = '';
			}
			return $tax_code_obj;
		}
	}

	/**
	 *  Returns all rows from the table
	 */
	public function get_all($rows = 0, $limit_from = 0, $no_deleted = TRUE)
	{
		$builder = $this->db->table('tax_codes');
		if($no_deleted == TRUE)
		{
			$builder->where('deleted', 0);
		}

		$builder->orderBy('tax_code_name', 'asc');

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/**
	 *  Returns multiple rows
	 */
	public function get_multiple_info($tax_codes)
	{
		$builder = $this->db->table('tax_codes');
		$builder->whereIn('tax_code', $tax_codes);
		$builder->orderBy('tax_code_name', 'asc');

		return $builder->get();
	}

	/**
	 *  Inserts or updates a row
	 */
	public function save(&$tax_code_data)
	{
		if(!$this->exists($tax_code_data['tax_code']))
		{
			if($builder->insert('tax_codes', $tax_code_data))
			{
				return TRUE;
			}
			return FALSE;
		}

		$builder->where('tax_code', $tax_code_data['tax_code']);

		return $builder->update('tax_codes', $tax_code_data);
	}

	/**
	 * Saves changes to the tax codes table
	 */
	public function save_tax_codes($array_save)
	{
		$this->db->transStart();

		$not_to_delete = array();

		foreach($array_save as $key => $value)
		{
			// save or update
			$tax_code_data = array('tax_code' => $value['tax_code'], 'tax_code_name' => $value['tax_code_name'], 'city' => $value['city'], 'state' => $value['state'], 'deleted' => '0');
			$this->save($tax_code_data);
			$not_to_delete[] = $tax_code_data['tax_code'];
		}

		// all entries not available in post will be deleted now
		$deleted_tax_codes = $this->get_all()->getResultArray();

		foreach($deleted_tax_codes as $key => $tax_code_data)
		{
			if(!in_array($tax_code_data['tax_code'], $not_to_delete))
			{
				$this->delete($tax_code_data['tax_code']);
			}
		}

		$this->db->transComplete();
		return $this->db->transStatus();
	}

	/**
	 * Deletes a specific tax code
	 */
	public function delete($tax_code)
	{
		$builder->where('tax_code', $tax_code);

		return $builder->update('tax_codes', array('deleted' => 1));
	}

	/**
	 * Deletes a list of rows
	 */
	public function delete_list($tax_codes)
	{
		$builder->whereIn('tax_code', $tax_codes);

		return $builder->update('tax_codes', array('deleted' => 1));
 	}

	/**
	 * Gets rows
	 */
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'tax_code_name', 'asc', TRUE);
	}

	/**
	 *  Perform a search for a set of rows
	 */
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'tax_code_name', $order='asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$builder->select('COUNT(tax_codes.tax_code) as count');
		}

		$builder = $this->db->table('tax_codes AS tax_codes');
		$builder->groupStart();
		$builder->like('tax_code_name', $search);
		$builder->orLike('tax_code', $search);
		$builder->groupEnd();
		$builder->where('deleted', 0);

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

	/**
	 * Gets the tax code to use for a given customer
	 */
	public function get_sales_tax_code($city = '', $state = '')
	{
		// if tax code using both city and state cannot be found then  try again using just the state
		// if the state tax code cannot be found then try again using blanks for both
		$builder = $this->db->table('tax_codes');
		$builder->where('city', $city);
		$builder->where('state', $state);
		$builder->where('deleted', 0);


		$query = $builder->get();

		if($query->getNumRows() == 1)
		{
			return $query->getRow()->tax_code_id;
		}
		else
		{
			$builder = $this->db->table('tax_codes');
			$builder->where('city', '');
			$builder->where('state', $state);
			$builder->where('deleted', 0);

			$query = $builder->get();

			if($query->getNumRows() == 1)
			{
				return $query->getRow()->tax_code_id;
			}
			else
			{
				return $this->config->item('default_tax_code');
			}
		}
		return FALSE;
	}

	public function get_tax_codes_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		$builder = $this->db->table('tax_codes');
		if(!empty($search))
		{
			$builder->like('tax_code', $search);
			$builder->orLike('tax_code_name', $search);
		}
		$builder->where('deleted', 0);
		$builder->orderBy('tax_code_name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->tax_code_id, 'label' => ($row->tax_code . ' ' . $row->tax_code_name));
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}

		return $suggestions;
	}

	public function get_empty_row()
	{
		return array('0' => array(
			'tax_code_id' => -1,
			'tax_code' => '',
			'tax_code_name' => '',
			'city' => '',
			'state' => '',
			'deleted' => 0));
	}
}
?>
