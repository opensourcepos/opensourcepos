<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tax Code class
 */

class Tax_code extends CI_Model
{
	/**
	 *  Determines if it exists in the table
	 */
	public function exists($tax_code)
	{
		$this->db->from('tax_codes');
		$this->db->where('tax_code', $tax_code);

		return ($this->db->get()->num_rows() == 1);
	}

	/**
	 *  Gets total of rows
	 */
	public function get_total_rows()
	{
		$this->db->from('tax_codes');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/**
	 * Gets information about the particular record
	 */
	public function get_info($tax_code_id)
	{
		$this->db->from('tax_codes');
		$this->db->where('tax_code_id', $tax_code_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
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
		$this->db->from('tax_codes');
		if($no_deleted == TRUE)
		{
			$this->db->where('deleted', 0);
		}

		$this->db->order_by('tax_code_name', 'asc');

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/**
	 *  Returns multiple rows
	 */
	public function get_multiple_info($tax_codes)
	{
		$this->db->from('tax_codes');
		$this->db->where_in('tax_code', $tax_codes);
		$this->db->order_by('tax_code_name', 'asc');

		return $this->db->get();
	}

	/**
	 *  Inserts or updates a row
	 */
	public function save(&$tax_code_data)
	{
		if(!$this->exists($tax_code_data['tax_code']))
		{
			if($this->db->insert('tax_codes', $tax_code_data))
			{
				return TRUE;
			}
			return FALSE;
		}

		$this->db->where('tax_code', $tax_code_data['tax_code']);

		return $this->db->update('tax_codes', $tax_code_data);
	}

	/**
	 * Deletes a specific tax code
	 */
	public function delete($tax_code)
	{
		$this->db->where('tax_code', $tax_code);

		return $this->db->update('tax_codes', array('deleted' => 1));
	}

	/**
	 * Deletes a list of rows
	 */
	public function delete_list($tax_codes)
	{
		$this->db->where_in('tax_code', $tax_codes);

		return $this->db->update('tax_codes', array('deleted' => 1));
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
			$this->db->select('COUNT(tax_codes.tax_code) as count');
		}

		$this->db->from('tax_codes AS tax_codes');
		$this->db->group_start();
		$this->db->like('tax_code_name', $search);
		$this->db->or_like('tax_code', $search);
		$this->db->group_end();
		$this->db->where('deleted', 0);

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

	/**
	 * Gets the tax code to use for a given customer
	 */
	public function get_sales_tax_code($city = '', $state = '')
	{
		// if tax code using both city and state cannot be found then  try again using just the state
		// if the state tax code cannot be found then try again using blanks for both
		$this->db->from('tax_codes');
		$this->db->where('city', $city);
		$this->db->where('state', $state);
		$this->db->where('deleted', 0);


		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row()->tax_code_id;
		}
		else
		{
			$this->db->from('tax_codes');
			$this->db->where('city', '');
			$this->db->where('state', $state);
			$this->db->where('deleted', 0);

			$query = $this->db->get();

			if($query->num_rows() == 1)
			{
				return $query->row()->tax_code_id;
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

		$this->db->from('tax_codes');
		if(!empty($search))
		{
			$this->db->like('tax_code', $search);
			$this->db->or_like('tax_code_name', $search);
		}
		$this->db->where('deleted', 0);
		$this->db->order_by('tax_code_name', 'asc');

		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->tax_code, 'label' => ($row->tax_code . ' ' . $row->tax_code_name));
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}

		return $suggestions;
	}

}
?>
