<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tax Jurisdiction class
 */

class Tax_jurisdiction extends CI_Model
{
	/**
	 *  Determines if it exists in the table
	 */
	public function exists($jurisdiction_id)
	{
		$this->db->from('tax_jurisdictions');
		$this->db->where('jurisdiction_id', $jurisdiction_id);

		return ($this->db->get()->num_rows() == 1);
	}

	/**
	 *  Gets total of rows
	 */
	public function get_total_rows()
	{
		$this->db->from('tax_jurisdictions');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/**
	 * Gets information about the particular record
	 */
	public function get_info($jurisdiction_id)
	{
		$this->db->from('tax_jurisdictions');
		$this->db->where('jurisdiction_id', $jurisdiction_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object
			$tax_jurisdiction_obj = new stdClass();

			//Get all the fields from the table
			foreach($this->db->list_fields('tax_jurisdictions') as $field)
			{
				$tax_jurisdiction_obj->$field = '';
			}
			return $tax_jurisdiction_obj;
		}
	}

	/**
	 *  Returns all rows from the table
	 */
	public function get_all($rows = 0, $limit_from = 0, $no_deleted = TRUE)
	{
		$this->db->from('tax_jurisdictions');
		if($no_deleted == TRUE)
		{
			$this->db->where('deleted', 0);
		}

		$this->db->order_by('jurisdiction_name', 'asc');

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/**
	 *  Returns multiple rows
	 */
	public function get_multiple_info($jurisdiction_ids)
	{
		$this->db->from('tax_jurisdictions');
		$this->db->where_in('jurisdiction_id', $jurisdiction_ids);
		$this->db->order_by('jurisdiction_name', 'asc');

		return $this->db->get();
	}

	/**
	 *  Inserts or updates a row
	 */
	public function save(&$jurisdiction_data, $jurisdiction_id = FALSE)
	{
		if(!$jurisdiction_id || !$this->exists($jurisdiction_id))
		{
			if($this->db->insert('tax_jurisdictions', $jurisdiction_data))
			{
				$jurisdiction_data['jurisdiction_id'] = $this->db->insert_id();
				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('jurisdiction_id', $jurisdiction_id);

		return $this->db->update('tax_jurisdictions', $jurisdiction_data);
	}

	/**
	 * Saves changes to the tax jurisdictions table
	 */
	public function save_jurisdictions($array_save)
	{
		$this->db->trans_start();

		$not_to_delete = array();

		foreach($array_save as $key => $value)
		{
			// save or update
			$tax_jurisdiction_data = array('jurisdiction_name' => $value['jurisdiction_name'], 'tax_group' => $value['tax_group'], 'tax_type' => $value['tax_type'], 'reporting_authority' => $value['reporting_authority'], 'tax_group_sequence' => $value['tax_group_sequence'], 'cascade_sequence' => $value['cascade_sequence'], 'deleted' => '0');
			$this->save($tax_jurisdiction_data, $value['jurisdiction_id']);
			if($value['jurisdiction_id'] == -1)
			{
				$not_to_delete[] = $tax_jurisdiction_data['jurisdiction_id'];
			}
			else
			{
				$not_to_delete[] = $value['jurisdiction_id'];
			}
		}

		// all entries not available in post will be deleted now
		$deleted_tax_jurisdictions = $this->get_all()->result_array();

		foreach($deleted_tax_jurisdictions as $key => $tax_jurisdiction_data)
		{
			if(!in_array($tax_jurisdiction_data['jurisdiction_id'], $not_to_delete))
			{
				$this->delete($tax_jurisdiction_data['jurisdiction_id']);
			}
		}

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/**
	 * Soft deletes a specific tax jurisdiction
	 */
	public function delete($jurisdiction_id)
	{
		$this->db->where('jurisdiction_id', $jurisdiction_id);

		return $this->db->update('tax_jurisdictions', array('deleted' => 1));
	}

	/**
	 * Soft deletes a list of rows
	 */
	public function delete_list($jurisdiction_ids)
	{
		$this->db->where_in('jurisdiction_id', $jurisdiction_ids);

		return $this->db->update('tax_jurisdictions', array('deleted' => 1));
 	}

	/**
	 * Gets rows
	 */
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'jurisdiction_name', 'asc', TRUE);
	}

	/**
	 *  Perform a search for a set of rows
	 */
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'jurisdiction_name', $order='asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(tax_jurisdictions.jurisdiction_id) as count');
		}

		$this->db->from('tax_jurisdictions AS tax_jurisdictions');
		$this->db->group_start();
			$this->db->like('jurisdiction_name', $search);
			$this->db->or_like('reporting_authority', $search);
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

	public function get_empty_row()
	{
		return array('0' => array(
			'jurisdiction_id' => -1,
			'jurisdiction_name' => '',
			'tax_group' => '',
			'tax_type' => '1',
			'reporting_authority' => '',
			'tax_group_sequence' => '',
			'cascade_sequence' => '',
			'deleted' => ''));
	}

}
?>
