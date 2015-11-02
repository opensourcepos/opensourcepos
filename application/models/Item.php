<?php
class Item extends CI_Model
{
	/*
	Determines if a given item_id is an item
	*/
	public function exists($item_id)
	{
		$this->db->from('items');
		$this->db->where('item_id', $item_id);
		$query = $this->db->get();

		return ($query->num_rows() == 1);
	}
	
	public function item_number_exists($item_number, $item_id='')
	{
		$this->db->from('items');
		$this->db->where('item_number', $item_number);
		if (!empty($item_id))
		{
			$this->db->where('item_id !=', $item_id);
		}
		$query=$this->db->get();

		return ($query->num_rows() == 1);
	}
	
	public function get_total_rows()
	{
		$this->db->from('items');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/*
	 Get number of rows
	*/
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters)->num_rows();
	}

	/*
	 Perform a search on items
	*/
	public function search($search, $filters, $rows=0, $limit_from=0)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->join('inventory', 'inventory.trans_items = items.item_id');

		if ($filters['stock_location_id'] > -1)
		{
			$this->db->join('item_quantities', 'item_quantities.item_id = items.item_id');
			$this->db->where('location_id', $filters['stock_location_id']);
		}

		if (empty($search))
		{
			$this->db->where('DATE_FORMAT(trans_date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			if ($filters['search_custom'] == FALSE)
			{
				$this->db->where("(name LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"item_number LIKE '" . $this->db->escape_like_str($search) . "%' OR " .
								$this->db->dbprefix('items').".item_id LIKE '" . $this->db->escape_like_str($search) . "%' OR " .
								"company_name LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"category LIKE '%" . $this->db->escape_like_str($search) . "%')");
			}
			else
			{
				$this->db->where("(custom1 LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"custom2 LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"custom3 LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"custom4 LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"custom5 LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"custom6 LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"custom7 LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"custom8 LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"custom9 LIKE '%" . $this->db->escape_like_str($search) . "%' OR " .
								"custom10 LIKE '%" . $this->db->escape_like_str($search) . "%')");
			}
		}

		$this->db->where('items.deleted', $filters['is_deleted']);

		if ($filters['empty_upc'] != FALSE)
		{
			$this->db->where('item_number', null);
		}
		if ($filters['low_inventory'] != FALSE)
		{
			$this->db->where('quantity <=', 'reorder_level');
		}
		if ($filters['is_serialized'] != FALSE)
		{
			$this->db->where('is_serialized', 1);
		}
		if ($filters['no_description'] != FALSE)
		{
			$this->db->where('items.description', '');
		}

		// avoid duplicate entry with same name because of inventory reporting multiple changes on the same item in the same date range
		$this->db->group_by('items.name');
		
		// order by name of item
		$this->db->order_by('items.name', 'asc');

		if ($rows > 0) 
		{	
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}
	
	/*
	 Returns all the items
	*/
	public function get_all($stock_location_id=-1, $rows=0, $limit_from=0)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');

		if ($stock_location_id > -1)
		{
			$this->db->join('item_quantities', 'item_quantities.item_id=items.item_id');
			$this->db->where('location_id', $stock_location_id);
		}

		$this->db->where('items.deleted', 0);
		
		// order by name of item
		$this->db->order_by('items.name', 'asc');

		if ($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}
	
	/*
	Gets information about a particular item
	*/
	public function get_info($item_id)
	{
		$this->db->select('items.*');
		$this->db->select('suppliers.company_name');
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->where('item_id', $item_id);
		
		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj=new stdClass();

			//Get all the fields from items table
			$fields = $this->db->list_fields('items');

			foreach ($fields as $field)
			{
				$item_obj->$field='';
			}

			return $item_obj;
		}
	}

	/*
	Get an item id given an item number
	*/
	public function get_item_id($item_number)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->where('item_number', $item_number);
		$this->db->where('items.deleted', 0);
        
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row()->item_id;
		}

		return false;
	}

	/*
	Gets information about multiple items
	*/
	public function get_multiple_info($item_ids)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->where_in('item_id', $item_ids);
		$this->db->order_by('item_id', 'asc');

		return $this->db->get();
	}

	/*
	Inserts or updates a item
	*/
	public function save(&$item_data, $item_id=false)
	{
		if (!$item_id or !$this->exists($item_id))
		{
			if($this->db->insert('items',$item_data))
			{
				$item_data['item_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('item_id', $item_id);

		return $this->db->update('items', $item_data);
	}

	/*
	Updates multiple items at once
	*/
	public function update_multiple($item_data, $item_ids)
	{
		$this->db->where_in('item_id',$item_ids);

		return $this->db->update('items',$item_data);
	}

	/*
	Deletes one item
	*/
	public function delete($item_id)
	{
		$this->db->where('item_id', $item_id);

		return $this->db->update('items', array('deleted' => 1));
	}

	/*
	Deletes a list of items
	*/
	public function delete_list($item_ids)
	{
		$this->db->where_in('item_id',$item_ids);

		return $this->db->update('items', array('deleted' => 1));
 	}

 	/*
	Get search suggestions to find items
	*/
	public function get_search_suggestions($search, $limit=25, $search_custom=0, $is_deleted=0)
	{
		$suggestions = array();

		$this->db->select('category');
		$this->db->from('items');
		$this->db->where('deleted', $is_deleted);
		$this->db->distinct();
		$this->db->like('category', $search);
		$this->db->order_by('category', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->category;
		}

		$this->db->select('company_name');
		$this->db->from('suppliers');
		$this->db->like('company_name', $search);
		// restrict to non deleted companies only if is_deleted if false
		if( $is_deleted == 0 )
		{
			$this->db->where('deleted', $is_deleted);
		}
		$this->db->distinct();
		$this->db->order_by('company_name', 'asc');
		$by_company_name = $this->db->get();
		foreach($by_company_name->result() as $row)
		{
			$suggestions[] = $row->company_name;
		}
		
		$this->db->select('name');
		$this->db->from('items');
		$this->db->like('name', $search);
		$this->db->where('deleted', $is_deleted);
		$this->db->order_by('name', 'asc');
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[] = $row->name;
		}

		$this->db->select('item_number');
		$this->db->from('items');
		$this->db->like('item_number', $search);
		$this->db->where('deleted', $is_deleted);
		$this->db->order_by('item_number', 'asc');
		$by_item_number = $this->db->get();
		foreach($by_item_number->result() as $row)
		{
			$suggestions[] = $row->item_number;
		}

		//Search by description
		$this->db->select('name, description');
		$this->db->from('items');
		$this->db->like('description', $search);
		$this->db->where('deleted', $is_deleted);
		$this->db->order_by('description', 'asc');
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			if (!in_array($row->name, $suggestions))
			{
				$suggestions[] = $row->name;
			}
		}

		//Search by custom fields
		if ($search_custom != 0)
		{
			$this->db->from('items');
			$this->db->like('custom1', $search);
			$this->db->or_like('custom2', $search);
			$this->db->or_like('custom3', $search);
			$this->db->or_like('custom4', $search);
			$this->db->or_like('custom5', $search);
			$this->db->or_like('custom6', $search);
			$this->db->or_like('custom7', $search);
			$this->db->or_like('custom8', $search);
			$this->db->or_like('custom9', $search);
			$this->db->or_like('custom10', $search);
			$this->db->where('deleted', $is_deleted);
			$by_name = $this->db->get();
			foreach($by_name->result() as $row)
			{
				$suggestions[] = $row->name;
			}
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

	public function get_item_search_suggestions($search, $limit=25, $search_custom=0, $is_deleted=0)
	{
		$suggestions = array();

		$this->db->select('item_id, name');
		$this->db->from('items');
		$this->db->where('deleted', $is_deleted);
		$this->db->like('name', $search);
		$this->db->order_by('name', 'asc');
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[] = $row->item_id.'|'.$row->name;
		}

		$this->db->select('item_id, item_number');
		$this->db->from('items');
		$this->db->where('deleted', $is_deleted);
		$this->db->like('item_number', $search);
		$this->db->order_by('item_number', 'asc');
		$by_item_number = $this->db->get();
		foreach($by_item_number->result() as $row)
		{
			$suggestions[] = $row->item_id.'|'.$row->item_number;
		}

		//Search by description
		$this->db->select('item_id, name, description');
		$this->db->from('items');
		$this->db->where('deleted', $is_deleted);
		$this->db->like('description', $search);
		$this->db->order_by('description', 'asc');
		$by_description = $this->db->get();
		foreach($by_description->result() as $row)
		{
			$entry = $row->item_id.'|'.$row->name;
			if (!in_array($entry, $suggestions))
			{
				$suggestions[] = $entry;
			}
		}

		//Search by custom fields
		if ($search_custom != 0)
		{
			$this->db->from('items');
			$this->db->where('deleted', $is_deleted);
			$this->db->like('custom1', $search);
			$this->db->or_like('custom2', $search);
			$this->db->or_like('custom3', $search);
			$this->db->or_like('custom4', $search);
			$this->db->or_like('custom5', $search);
			$this->db->or_like('custom6', $search);
			$this->db->or_like('custom7', $search);
			$this->db->or_like('custom8', $search);
			$this->db->or_like('custom9', $search);
			$this->db->or_like('custom10', $search);
			$by_description = $this->db->get();
			foreach($by_description->result() as $row)
			{
				$suggestions[] = $row->item_id.'|'.$row->name;
			}
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}

		return $suggestions;
	}

	public function get_category_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('category');
		$this->db->from('items');
		$this->db->like('category', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('category', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->category;
		}

		return $suggestions;
	}
	
	public function get_location_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('location');
		$this->db->from('items');
		$this->db->like('location', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('location', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->location;
		}
	
		return $suggestions;
	}

	public function get_custom1_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom1');
		$this->db->from('items');
		$this->db->like('custom1', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom1', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom1;
		}
	
		return $suggestions;
	}
	
	public function get_custom2_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom2');
		$this->db->from('items');
		$this->db->like('custom2', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom2', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom2;
		}
	
		return $suggestions;
	}
	
	public function get_custom3_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom3');
		$this->db->from('items');
		$this->db->like('custom3', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom3', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom3;
		}
	
		return $suggestions;
	}
	
	public function get_custom4_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom4');
		$this->db->from('items');
		$this->db->like('custom4', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom4', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom4;
		}
	
		return $suggestions;
	}
	
	public function get_custom5_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom5');
		$this->db->from('items');
		$this->db->like('custom5', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom5', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom5;
		}
	
		return $suggestions;
	}
	
	public function get_custom6_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom6');
		$this->db->from('items');
		$this->db->like('custom6', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom6', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom6;
		}
	
		return $suggestions;
	}
	
	public function get_custom7_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom7');
		$this->db->from('items');
		$this->db->like('custom7', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom7', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom7;
		}
	
		return $suggestions;
	}
	
	public function get_custom8_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom8');
		$this->db->from('items');
		$this->db->like('custom8', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom8', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom8;
		}
	
		return $suggestions;
	}
	
	public function get_custom9_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom9');
		$this->db->from('items');
		$this->db->like('custom9', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom9', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom9;
		}
	
		return $suggestions;
	}
	
	public function get_custom10_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom10');
		$this->db->from('items');
		$this->db->like('custom10', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom10', 'asc');
		$by_category = $this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[] = $row->custom10;
		}
	
		return $suggestions;
	}

	public function get_categories()
	{
		$this->db->select('category');
		$this->db->from('items');
		$this->db->where('deleted', 0);
		$this->db->distinct();
		$this->db->order_by('category', 'asc');

		return $this->db->get();
	}
	
	/*
	 * changes the cost price of a given item
	 * calculates the average price between received items and items on stock
	 * $item_id : the item which price should be changed
	 * $items_received : the amount of new items received
	 * $new_price : the cost-price for the newly received items
	 * $old_price (optional) : the current-cost-price
	 * 
	 * used in receiving-process to update cost-price if changed
	 * caution: must be used there before item_quantities gets updated, otherwise average price is wrong!
	 * 
	 */
	public function change_cost_price($item_id, $items_received, $new_price, $old_price = null)
	{
		if($old_price === null)
		{
			$item_info = $this->get_info($item['item_id']);
			$old_price = $item_info->cost_price;
		}

		$this->db->from('item_quantities');
		$this->db->select_sum('quantity');
        $this->db->where('item_id', $item_id);
		$this->db->join('stock_locations', 'stock_locations.location_id=item_quantities.location_id');
        $this->db->where('stock_locations.deleted', 0);
		$old_total_quantity = $this->db->get()->row()->quantity;

		$total_quantity = $old_total_quantity + $items_received;
		$average_price = bcdiv(bcadd(bcmul($items_received, $new_price), bcmul($old_total_quantity, $old_price)), $total_quantity);

		$data = array('cost_price' => $average_price);
		
		return $this->save($data, $item_id);
	}
}
?>