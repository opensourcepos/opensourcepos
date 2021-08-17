<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Tax Category class
 */

class Tax_category extends Model
{
	/**
	 *  Determines if it exists in the table
	 */
	public function exists($tax_category_id)
	{
		$builder = $this->db->table('tax_categories');
		$builder->where('tax_category_id', $tax_category_id);

		return ($builder->get()->getNumRows() == 1);
	}

	/**
	 *  Gets total of rows
	 */
	public function get_total_rows()
	{
		$builder = $this->db->table('tax_categories');
		$builder->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/**
	 * Gets information about the particular record
	 */
	public function get_info($tax_category_id)
	{
		$builder = $this->db->table('tax_categories');
		$builder->where('tax_category_id', $tax_category_id);
		$builder->where('deleted', 0);
		$query = $builder->get();

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
		$builder = $this->db->table('tax_categories');
		if($no_deleted == TRUE)
		{
			$builder->where('deleted', 0);
		}

		$builder->orderBy('tax_group_sequence', 'asc');
		$builder->orderBy('tax_category', 'asc');

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/**
	 *  Returns multiple rows
	 */
	public function get_multiple_info($tax_category_ids)
	{
		$builder = $this->db->table('tax_categories');
		$this->db->where_in('tax_category_id', $tax_category_ids);
		$builder->orderBy('tax_category', 'asc');

		return $builder->get();
	}

	/**
	 *  Inserts or updates a row
	 */
	public function save(&$tax_category_data, $tax_category_id = FALSE)
	{
		if(!$tax_category_id || !$this->exists($tax_category_id))
		{
			if($builder->insert('tax_categories', $tax_category_data))
			{
				$tax_category_data['tax_category_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$builder->where('tax_category_id', $tax_category_id);

		return $builder->update('tax_categories', $tax_category_data);
	}

	/**
	 * Saves changes to the tax categories table
	 */
	public function save_categories($array_save)
	{
		$this->db->transStart();

		$not_to_delete = array();

		foreach($array_save as $key => $value)
		{
			// save or update
			$tax_category_data = array('tax_category' => $value['tax_category'], 'tax_group_sequence' => $value['tax_group_sequence'], 'deleted' => '0');
			$this->save($tax_category_data, $value['tax_category_id']);
			if($value['tax_category_id'] == -1)
			{
				$not_to_delete[] = $tax_category_data['tax_category_id'];
			}
			else
			{
				$not_to_delete[] = $value['tax_category_id'];
			}
		}

		// all entries not available in post will be deleted now
		$deleted_tax_categories = $this->get_all()->result_array();

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
	public function delete($tax_category_id)
	{
		$builder->where('tax_category_id', $tax_category_id);

		return $builder->update('tax_categories', array('deleted' => 1));
	}

	/**
	 * Deletes a list of rows
	 */
	public function delete_list($tax_category_ids)
	{
		$this->db->where_in('tax_category_id', $tax_category_ids);

		return $builder->update('tax_categories', array('deleted' => 1));
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

		$builder = $this->db->table('tax_categories AS tax_categories');
		$this->db->like('tax_category', $search);
		$builder->where('deleted', 0);

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $builder->get()->row()->count;
		}

		$builder->orderBy($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	public function get_tax_category_suggestions($search)
	{
		$suggestions = array();

		$builder = $this->db->table('tax_categories');
		$builder->where('deleted', 0);
		if(!empty($search))
		{
			$this->db->like('tax_category', '%'.$search.'%');
		}
		$builder->orderBy('tax_category', 'asc');

		foreach($builder->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->tax_category_id, 'label' => $row->tax_category);
		}

		return $suggestions;
	}

	public function get_empty_row()
	{
		return array('0' => array(
			'tax_category_id' => -1,
			'tax_category' => '',
			'tax_group_sequence' => '',
			'deleted' => ''));
	}

}
?>
