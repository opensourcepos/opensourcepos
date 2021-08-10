<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Supplier class
 */

class Supplier extends Person
{
	const GOODS_SUPPLIER = 0;
	const COST_SUPPLIER = 1;

	/*
	Determines if a given person_id is a customer
	*/
	public function exists($person_id)
	{
		$this->db->from('suppliers');	
		$this->db->join('people', 'people.person_id = suppliers.person_id');
		$this->db->where('suppliers.person_id', $person_id);
		
		return ($this->db->get()->num_rows() == 1);
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('suppliers');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}
	
	/*
	Returns all the suppliers
	*/
	public function get_all($category = self::GOODS_SUPPLIER, $limit_from = 0, $rows = 0)
	{
		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
		$this->db->where('category', $category);
		$this->db->where('deleted', 0);
		$this->db->order_by('company_name', 'asc');
		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();		
	}
	
	/*
	Gets information about a particular supplier
	*/
	public function get_info($supplier_id)
	{
		$this->db->from('suppliers');	
		$this->db->join('people', 'people.person_id = suppliers.person_id');
		$this->db->where('suppliers.person_id', $supplier_id);
		$query = $this->db->get();
		
		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $supplier_id is NOT an supplier
			$person_obj = parent::get_info(-1);
			
			//Get all the fields from supplier table		
			//append those fields to base parent object, we we have a complete empty object
			foreach($this->db->list_fields('suppliers') as $field)
			{
				$person_obj->$field = '';
			}
			
			return $person_obj;
		}
	}
	
	/*
	Gets information about multiple suppliers
	*/
	public function get_multiple_info($suppliers_ids)
	{
		$this->db->from('suppliers');
		$this->db->join('people', 'people.person_id = suppliers.person_id');		
		$this->db->where_in('suppliers.person_id', $suppliers_ids);
		$this->db->order_by('last_name', 'asc');

		return $this->db->get();
	}
	
	/*
	Inserts or updates a suppliers
	*/
	public function save_supplier(&$person_data, &$supplier_data, $supplier_id = FALSE)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		
		if(parent::save($person_data,$supplier_id))
		{
			if(!$supplier_id || !$this->exists($supplier_id))
			{
				$supplier_data['person_id'] = $person_data['person_id'];
				$success = $this->db->insert('suppliers', $supplier_data);
			}
			else
			{
				$this->db->where('person_id', $supplier_id);
				$success = $this->db->update('suppliers', $supplier_data);
			}
		}
		
		$this->db->trans_complete();
		
		$success &= $this->db->trans_status();

		return $success;
	}
	
	/*
	Deletes one supplier
	*/
	public function delete($supplier_id)
	{
		$this->db->where('person_id', $supplier_id);

		return $this->db->update('suppliers', array('deleted' => 1));
	}
	
	/*
	Deletes a list of suppliers
	*/
	public function delete_list($supplier_ids)
	{
		$this->db->where_in('person_id', $supplier_ids);

		return $this->db->update('suppliers', array('deleted' => 1));
 	}
 	
 	/*
	Get search suggestions to find suppliers
	*/
	public function get_search_suggestions($search, $unique = FALSE, $limit = 25)
	{
		$suggestions = array();

		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like('company_name', $search);
		$this->db->order_by('company_name', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->company_name);
		}

		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
		$this->db->where('deleted', 0);
		$this->db->distinct();
		$this->db->like('agency_name', $search);
		$this->db->where('agency_name IS NOT NULL');
		$this->db->order_by('agency_name', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->agency_name);
		}

		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
		$this->db->group_start();
			$this->db->like('first_name', $search);
			$this->db->or_like('last_name', $search); 
			$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
		$this->db->group_end();
		$this->db->where('deleted', 0);
		$this->db->order_by('last_name', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->first_name . ' ' . $row->last_name);
		}

		if(!$unique)
		{
			$this->db->from('suppliers');
			$this->db->join('people', 'suppliers.person_id = people.person_id');
			$this->db->where('deleted', 0);
			$this->db->like('email', $search);
			$this->db->order_by('email', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('value' => $row->person_id, 'label' => $row->email);
			}

			$this->db->from('suppliers');
			$this->db->join('people', 'suppliers.person_id = people.person_id');
			$this->db->where('deleted', 0);
			$this->db->like('phone_number', $search);
			$this->db->order_by('phone_number', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('value' => $row->person_id, 'label' => $row->phone_number);
			}

			$this->db->from('suppliers');
			$this->db->join('people', 'suppliers.person_id = people.person_id');
			$this->db->where('deleted', 0);
			$this->db->like('account_number', $search);
			$this->db->order_by('account_number', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('value' => $row->person_id, 'label' => $row->account_number);
			}
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

 	/*
	Gets rows
	*/
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'last_name', 'asc', TRUE);
	}
	
	/*
	Perform a search on suppliers
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'last_name', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(suppliers.person_id) as count');
		}

		$this->db->from('suppliers AS suppliers');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
		$this->db->group_start();
			$this->db->like('first_name', $search);
			$this->db->or_like('last_name', $search);
			$this->db->or_like('company_name', $search);
			$this->db->or_like('agency_name', $search);
			$this->db->or_like('email', $search);
			$this->db->or_like('phone_number', $search);
			$this->db->or_like('account_number', $search);
			$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
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

	/*
	Return supplier categories
	*/
	public function get_categories()
	{
		return array(
			self::GOODS_SUPPLIER => $this->lang->line('suppliers_goods'),
			self::COST_SUPPLIER => $this->lang->line('suppliers_cost')
		);
	}

	/*
	Return a category name given its id
	*/
	public function get_category_name($id)
	{
		if($id == self::GOODS_SUPPLIER)
		{
			return $this->lang->line('suppliers_goods');
		}
		elseif($id == self::COST_SUPPLIER)
		{
			return $this->lang->line('suppliers_cost');
		}
	}
}
?>
