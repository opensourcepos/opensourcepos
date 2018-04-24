<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tax class
 */

class Tax extends CI_Model
{
	/**
	Determines if a given tax_code is on file
	*/
	public function exists($tax_code)
	{
		$this->db->from('tax_codes');
		$this->db->where('tax_code', $tax_code);

		return ($this->db->get()->num_rows() == 1);
	}

	public function tax_rate_exists($tax_code, $tax_category_id)
	{
		$this->db->from('tax_code_rates');
		$this->db->where('rate_tax_code', $tax_code);
		$this->db->where('rate_tax_category_id', $tax_category_id);

		return ($this->db->get()->num_rows() == 1);
	}

	public function tax_category_exists($tax_category_id)
	{
		$this->db->from('tax_categories');
		$this->db->where('tax_category_id', $tax_category_id);

		return ($this->db->get()->num_rows() == 1);
	}

	/**
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('tax_codes');

		return $this->db->count_all_results();
	}

	public function get_tax_category_usage($tax_category_id)
	{
		$this->db->from('tax_code_rates');
		$this->db->where('rate_tax_category_id', $tax_category_id);

		return $this->db->count_all_results();
	}

	/**
	Gets information about a particular tax_code
	*/
	public function get_info($tax_code)
	{
		$this->db->from('tax_codes');
		$this->db->join('tax_code_rates',
			'tax_code = rate_tax_code and rate_tax_category_id = 1', 'LEFT');
		$this->db->join('tax_categories',
			'rate_tax_category_id = tax_category_id');
		$this->db->where('tax_code', $tax_code);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object
			$tax_code_obj = new stdClass();

			//Get all the fields from tax_codes table
			foreach($this->db->list_fields('tax_codes') as $field)
			{
				$tax_code_obj->$field = '';
			}
			foreach($this->db->list_fields('tax_code_rates') as $field)
			{
				$tax_code_obj->$field = '';
			}

			return $tax_code_obj;
		}
	}

	/**
	Gets information about a particular tax_code
	*/
	public function get_rate_info($tax_code, $tax_category_id)
	{
		$this->db->from('tax_code_rates');
		$this->db->join('tax_categories', 'rate_tax_category_id = tax_category_id');
		$this->db->where('rate_tax_code', $tax_code);
		$this->db->where('rate_tax_category_id', $tax_category_id);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object
			$tax_rate_obj = new stdClass();

			//Get all the fields from tax_codes table
			foreach($this->db->list_fields('tax_code_rates') as $field)
			{
				$tax_rate_obj->$field = '';
			}
			//Get all the fields from tax_code_rates table
			foreach($this->db->list_fields('tax_categories') as $field)
			{
				$tax_rate_obj->$field = '';
			}

			return $tax_rate_obj;
		}
	}

	/**
	Gets the tax code to use for a given customer
	*/
	public function get_sales_tax_code($city = '', $state = '')
	{
		// if tax code using both city and state cannot be found then  try again using just the state
		// if the state tax code cannot be found then try again using blanks for both
		$this->db->from('tax_codes');
		$this->db->where('city', $city);
		$this->db->where('state', $state);
		$this->db->where('tax_code_type', '0'); // sales tax

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row()->tax_code;
		}
		else
		{
			$this->db->from('tax_codes');
			$this->db->where('city', '');
			$this->db->where('state', $state);
			$this->db->where('tax_code_type', '0'); // sales tax

			$query = $this->db->get();

			if($query->num_rows() == 1)
			{
				return $query->row()->tax_code;
			}
			else
			{
				return $this->config->item('default_origin_tax_code');
			}
		}

		return FALSE;
	}

	/**
	Inserts or updates a tax_codes entry
	*/
	public function save(&$tax_code_data, $tax_rate_data, $tax_code = -1)
	{
		if(!$this->exists($tax_code))
		{
			if($this->db->insert('tax_codes', $tax_code_data))
			{
				$this->save_tax_rates($tax_rate_data, $tax_code);

				return TRUE;
			}
		}
		else
		{
			$this->db->where('tax_code', $tax_code);

			if($this->db->update('tax_codes', $tax_code_data))
			{
				$this->save_tax_rates($tax_rate_data, $tax_code);

				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Inserts or updates a tax_category
	 */
	public function save_tax_category(&$tax_category_data, &$tax_category_id)
	{
		if(!$this->tax_category_exists($tax_category_id))
		{
			$result = $this->db->insert('tax_categories', $tax_category_data);

			$tax_category_id = $this->db->insert_id();

			return $result;
		}

		$this->db->where('tax_category_id', $tax_category_id);

		return $this->db->update('tax_categories', $tax_category_data);
	}

	/**
	 * Inserts or updates a tax_rate
	 */
	public function save_tax_rates(&$tax_rate_data, $tax_code)
	{
		if(!$this->tax_rate_exists($tax_code, $tax_rate_data['rate_tax_category_id']))
		{
			return $this->db->insert('tax_code_rates', $tax_rate_data);
		}

		$this->db->where('rate_tax_code', $tax_code);
		$this->db->where('rate_tax_category_id', $tax_rate_data['rate_tax_category_id']);

		return $this->db->update('tax_code_rates', $tax_rate_data);
	}

	/**
	Inserts or updates an item kit's items
	*/
	public function save_tax_rate_exceptions(&$tax_rate_data, $tax_code)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing

		$this->db->trans_start();

		// Delete all exceptions for the given tax_code
		$this->delete_tax_rate_exceptions($tax_code);

		if($tax_rate_data != NULL)
		{
			foreach($tax_rate_data as $row)
			{
				$row['rate_tax_code'] = $tax_code;
				$success &= $this->db->insert('tax_code_rates', $row);
			}
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	/**
	Deletes one tax_codes entry
	*/
	public function delete($tax_code)
	{
		return $this->db->delete('tax_codes', array('tax_code' => $tax_code));
	}

	/**
	Deletes one tax_codes entry
	*/
	public function delete_tax_category($tax_category_id)
	{
		return $this->db->delete('tax_categories', array('tax_category_id' => $tax_category_id));
	}

	/**
	Deletes a list of tax codes
	*/
	public function delete_list($tax_codes)
	{
		$this->db->where_in('tax_code', $tax_codes);

		return $this->db->delete('tax_codes');
	}

	/**
	Deletes all tax_rate_exceptions for given tax codes
	*/
	public function delete_tax_rate_exceptions($tax_code)
	{
		$this->db->where('rate_tax_code', $tax_code);
		$this->db->where('rate_tax_category_id !=', 1);

		return $this->db->delete('tax_code_rates');
	}

	/**
	Gets tax_codes
	*/
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'tax_code', 'asc', TRUE);
	}

	/**
	Performs a search on tax_codes
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'tax_code', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(tax_code) as count');
		}

		$this->db->from('tax_codes');
		$this->db->join('tax_code_rates', 'tax_code = rate_tax_code AND rate_tax_category_id = 1', 'LEFT');

		if(!empty($search))
		{
			$this->db->like('tax_code', $search);
			$this->db->or_like('tax_code_name', $search);
		}

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

	public function get_tax_code_type_name($tax_code_type)
	{
		if($tax_code_type == '0')
		{
			return $this->lang->line('taxes_sales_tax');
		}
		else
		{
			return $this->lang->line('taxes_vat_tax');
		}
	}

	public function get_sales_tax_codes_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		$this->db->from('tax_codes');
		if(!empty($search))
		{
			$this->db->like('tax_code', $search);
			$this->db->or_like('tax_code_name', $search);
		}
		$this->db->order_by('tax_code_name', 'asc');

		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->tax_code, 'label' => ($row->tax_code . ' ' . $row->tax_code_name));
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}

		return $suggestions;
	}

	public function get_tax_category_suggestions($search)
	{
		$suggestions = array();

		$this->db->from('tax_categories');
		$this->db->where('tax_category_id !=', 1);
		if(!empty($search))
		{
			$this->db->like('tax_category', '%'.$search.'%');
		}
		$this->db->order_by('tax_category', 'asc');

		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->tax_category_id, 'label' => $row->tax_category);
		}

		return $suggestions;
	}

	public function get_tax_category($tax_category_id)
	{
		$this->db->select('tax_category');
		$this->db->from('tax_categories');
		$this->db->where('tax_category_id', $tax_category_id);

		return $this->db->get()->row()->tax_category;
	}

	public function get_all_tax_categories()
	{
		$this->db->from('tax_categories');
		$this->db->order_by('tax_category_id');

		return $this->db->get();
	}

	public function get_all_tax_codes()
	{
		$this->db->select('tax_code, tax_code_name');
		$this->db->from('tax_codes');
		$this->db->order_by('tax_code_name');

		return $this->db->get();
	}

	public function get_tax_category_id($tax_category)
	{
		$this->db->select('tax_category_id');
		$this->db->from('tax_categories');

		return $this->db->get()->row()->tax_category_id;
	}

	public function get_tax_code_rate_exceptions($tax_code)
	{
		$this->db->from('tax_code_rates');
		$this->db->join('tax_categories', 'rate_tax_category_id = tax_category_id');
		$this->db->where('rate_tax_code', $tax_code);
		$this->db->where('rate_tax_category_id !=', 1);
		$this->db->order_by('tax_category', 'asc');

		return $this->db->get()->result_array();
	}
}
?>
