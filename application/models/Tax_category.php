<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tax Category class
 */

class Tax_category extends CI_Model
{
	/**
	 *  Determines if it exists in the table
	 */
	public function exists($tax_category_id)
	{
		$this->db->from('tax_categories');
		$this->db->where('tax_category_id', $tax_category_id);

		return ($this->db->get()->num_rows() == 1);
	}

	/**
	 *  Gets total of rows
	 */
	public function get_total_rows()
	{
		$this->db->from('tax_categories');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/**
	 * Gets information about the particular record
	 */
	public function get_info($tax_category_id)
	{
		$this->db->from('tax_categories');
		$this->db->where('tax_category_id', $tax_category_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object
			$tax_category_obj = new stdClass();

			//Get all the fields from the table
			foreach($this->db->list_fields('tax_categories') as $field)
			{
				$tax_category_obj->$field = '';
			}
			return $tax_category_obj;
		}
	}

	/**
	 *  Returns all rows from the table
	 */
	public function get_all($rows = 0, $limit_from = 0, $no_deleted = TRUE)
	{
		$this->db->from('tax_categories');
		if($no_deleted == TRUE)
		{
			$this->db->where('deleted', 0);
		}

		$this->db->order_by('tax_group_sequence', 'asc');
		$this->db->order_by('tax_category', 'asc');

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/**
	 *  Returns multiple rows
	 */
	public function get_multiple_info($tax_category_ids)
	{
		$this->db->from('tax_categories');
		$this->db->where_in('tax_category_id', $tax_category_ids);
		$this->db->order_by('tax_category', 'asc');

		return $this->db->get();
	}

	/**
	 *  Inserts or updates a row
	 */
	public function save(&$tax_category_data, $tax_category_id = FALSE)
	{
		if(!$tax_category_id || !$this->exists($tax_category_id))
		{
			if($this->db->insert('tax_categories', $tax_category_data))
			{
				$tax_category_data['tax_category_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('tax_category_id', $tax_category_id);

		return $this->db->update('tax_categories', $tax_category_data);
	}

	/**
	 * Soft delete a specific row
	 */
	public function delete($tax_category_id)
	{
		$this->db->where('tax_category_id', $tax_category_id);

		return $this->db->update('tax_categories', array('deleted' => 1));
	}

	/**
	 * Deletes a list of rows
	 */
	public function delete_list($tax_category_ids)
	{
		$this->db->where_in('tax_category_id', $tax_category_ids);

		return $this->db->update('tax_categories', array('deleted' => 1));
 	}

	/**
	 * Gets rows
	 */
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'tax_category', 'asc', TRUE);
	}

	/**
	 *  Perform a search for a set of rows
	 */
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'tax_category', $order='asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(tax_categories.tax_category_id) as count');
		}

		$this->db->from('tax_categories AS tax_categories');
		$this->db->like('tax_category', $search);
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

	public function get_tax_category_suggestions($search)
	{
		$suggestions = array();

		$this->db->from('tax_categories');
		$this->db->where('deleted', 0);
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


}
?>
