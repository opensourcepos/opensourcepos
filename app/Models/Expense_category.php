<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Expense_category class
 */

class Expense_category extends Model
{
	/*
	Determines if a given Expense_id is an Expense category
	*/
	public function exists($expense_category_id)
	{
		$builder = $this->db->table('expense_categories');
		$builder->where('expense_category_id', $expense_category_id);

		return ($builder->get()->getNumRows() == 1);
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$builder = $this->db->table('expense_categories');
		$builder->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/*
	Gets information about a particular category
	*/
	public function get_info($expense_category_id)
	{
		$builder = $this->db->table('expense_categories');
		$builder->where('expense_category_id', $expense_category_id);
		$builder->where('deleted', 0);
		$query = $builder->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $item_kit_id is NOT an item kit
			$expense_obj = new stdClass();

			//Get all the fields from items table
			foreach($this->db->list_fields('expense_categories') as $field)
			{
				$expense_obj->$field = '';
			}

			return $expense_obj;
		}
	}

	/*
	Returns all the expense_categories
	*/
	public function get_all($rows = 0, $limit_from = 0, $no_deleted = FALSE)
	{
		$builder = $this->db->table('expense_categories');
		if($no_deleted == TRUE)
		{
			$builder->where('deleted', 0);
		}

		$builder->orderBy('category_name', 'asc');

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/*
	Gets information about multiple expense_category_id
	*/
	public function get_multiple_info($expense_category_ids)
	{
		$builder = $this->db->table('expense_categories');
		$this->db->where_in('expense_category_id', $expense_category_ids);
		$builder->orderBy('category_name', 'asc');

		return $builder->get();
	}

	/*
	Inserts or updates an expense_category
	*/
	public function save(&$expense_category_data, $expense_category_id = FALSE)
	{
		if(!$expense_category_id || !$this->exists($expense_category_id))
		{
			if($builder->insert('expense_categories', $expense_category_data))
			{
				$expense_category_data['expense_category_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$builder->where('expense_category_id', $expense_category_id);

		return $builder->update('expense_categories', $expense_category_data);
	}

	/*
	Deletes a list of expense_category
	*/
	public function delete_list($expense_category_ids)
	{
		$this->db->where_in('expense_category_id', $expense_category_ids);

		return $builder->update('expense_categories', array('deleted' => 1));
 	}

	/*
	Gets rows
	*/
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'category_name', 'asc', TRUE);
	}

	/*
	Perform a search on expense_category
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'category_name', $order='asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(expense_categories.expense_category_id) as count');
		}

		$builder = $this->db->table('expense_categories AS expense_categories');
		$this->db->group_start();
			$this->db->like('category_name', $search);
			$this->db->or_like('category_description', $search);
		$this->db->group_end();
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
}
?>
