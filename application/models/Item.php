<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

define('HAS_STOCK', 0);
define('HAS_NO_STOCK', 1);

define('ITEM', 0);
define('ITEM_KIT', 1);
define('ITEM_AMOUNT_ENTRY', 2);

define('PRINT_ALL', 0);
define('PRINT_PRICED', 1);
define('PRINT_KIT', 2);

define('PRINT_YES', 0);
define('PRINT_NO', 1);

define('PRICE_ALL', 0);
define('PRICE_KIT', 1);
define('PRICE_KIT_ITEMS', 2);

define('PRICE_OPTION_ALL', 0);
define('PRICE_OPTION_KIT', 1);
define('PRICE_OPTION_KIT_STOCK', 2);


/**
 * Item class
 */

class Item extends CI_Model
{
	/*
	Determines if a given item_id is an item
	*/
	public function exists($item_id, $ignore_deleted = FALSE, $deleted = FALSE)
	{
		// check if $item_id is a number and not a string starting with 0
		// because cases like 00012345 will be seen as a number where it is a barcode
		if(ctype_digit($item_id) && substr($item_id, 0, 1) != '0')
		{
			$this->db->from('items');
			$this->db->where('item_id', (int) $item_id);
			if($ignore_deleted == FALSE)
			{
				$this->db->where('deleted', $deleted);
			}

			return ($this->db->get()->num_rows() == 1);
		}

		return FALSE;
	}

	/*
	Determines if a given item_number exists
	*/
	public function item_number_exists($item_number, $item_id = '')
	{
		if($this->config->item('allow_duplicate_barcodes') != FALSE)
		{			
			return FALSE;
		}

		$this->db->from('items');
		$this->db->where('item_number', (string) $item_number);
		// check if $item_id is a number and not a string starting with 0
		// because cases like 00012345 will be seen as a number where it is a barcode
		if(ctype_digit($item_id) && substr($item_id, 0, 1) != '0')
		{
			$this->db->where('item_id !=', (int) $item_id);
		}

		return ($this->db->get()->num_rows() >= 1);
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('items');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	public function get_tax_category_usage($tax_category_id)
	{
		$this->db->from('items');
		$this->db->where('tax_category_id', $tax_category_id);

		return $this->db->count_all_results();
	}

	/*
	Get number of rows
	*/
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters, 0, 0, 'items.name', 'asc', TRUE);
	}

	/*
	Perform a search on items
	*/
	public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'items.name', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(DISTINCT items.item_id) as count');
		}
		else
		{
			$this->db->select('items.item_id as item_id');
			$this->db->select('MAX(items.name) as name');
			$this->db->select('MAX(items.category) as category');
			$this->db->select('MAX(items.supplier_id) as supplier_id');
			$this->db->select('MAX(items.item_number) as item_number');
			$this->db->select('MAX(items.description) as description');
			$this->db->select('MAX(items.cost_price) as cost_price');
			$this->db->select('MAX(items.unit_price) as unit_price');
			$this->db->select('MAX(items.reorder_level) as reorder_level');
			$this->db->select('MAX(items.receiving_quantity) as receiving_quantity');
			$this->db->select('MAX(items.pic_filename) as pic_filename');
			$this->db->select('MAX(items.allow_alt_description) as allow_alt_description');
			$this->db->select('MAX(items.is_serialized) as is_serialized');
			$this->db->select('MAX(items.deleted) as deleted');
			$this->db->select('MAX(items.custom1) as custom1');
			$this->db->select('MAX(items.custom2) as custom2');
			$this->db->select('MAX(items.custom3) as custom3');
			$this->db->select('MAX(items.custom4) as custom4');
			$this->db->select('MAX(items.custom5) as custom5');
			$this->db->select('MAX(items.custom6) as custom6');
			$this->db->select('MAX(items.custom7) as custom7');
			$this->db->select('MAX(items.custom8) as custom8');
			$this->db->select('MAX(items.custom9) as custom9');
			$this->db->select('MAX(items.custom10) as custom10');

			$this->db->select('MAX(suppliers.person_id) as person_id');
			$this->db->select('MAX(suppliers.company_name) as company_name');
			$this->db->select('MAX(suppliers.agency_name) as agency_name');
			$this->db->select('MAX(suppliers.account_number) as account_number');
			$this->db->select('MAX(suppliers.deleted) as deleted');

			$this->db->select('MAX(inventory.trans_id) as trans_id');
			$this->db->select('MAX(inventory.trans_items) as trans_items');
			$this->db->select('MAX(inventory.trans_user) as trans_user');
			$this->db->select('MAX(inventory.trans_date) as trans_date');
			$this->db->select('MAX(inventory.trans_comment) as trans_comment');
			$this->db->select('MAX(inventory.trans_location) as trans_location');
			$this->db->select('MAX(inventory.trans_inventory) as trans_inventory');

			if($filters['stock_location_id'] > -1)
			{
				$this->db->select('MAX(item_quantities.item_id) as qty_item_id');
				$this->db->select('MAX(item_quantities.location_id) as location_id');
				$this->db->select('MAX(item_quantities.quantity) as quantity');
			}
		}

		$this->db->from('items as items');
		$this->db->join('suppliers as suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->join('inventory as inventory', 'inventory.trans_items = items.item_id');

		if($filters['stock_location_id'] > -1)
		{
			$this->db->join('item_quantities as item_quantities', 'item_quantities.item_id = items.item_id');
			$this->db->where('location_id', $filters['stock_location_id']);
		}

		if(empty($this->config->item('date_or_time_format')))
		{
			$this->db->where('DATE_FORMAT(trans_date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$this->db->where('trans_date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if(!empty($search))
		{
			if($filters['search_custom'] == FALSE)
			{
				$this->db->group_start();
					$this->db->like('name', $search);
					$this->db->or_like('item_number', $search);
					$this->db->or_like('items.item_id', $search);
					$this->db->or_like('company_name', $search);
					$this->db->or_like('category', $search);
				$this->db->group_end();
			}
			else
			{
				$this->db->group_start();
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
				$this->db->group_end();
			}
		}

		$this->db->where('items.deleted', $filters['is_deleted']);

		if($filters['empty_upc'] != FALSE)
		{
			$this->db->where('item_number', NULL);
		}
		if($filters['low_inventory'] != FALSE)
		{
			$this->db->where('quantity <=', 'reorder_level');
		}
		if($filters['is_serialized'] != FALSE)
		{
			$this->db->where('is_serialized', 1);
		}
		if($filters['no_description'] != FALSE)
		{
			$this->db->where('items.description', '');
		}

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $this->db->get()->row()->count;
		}

		// avoid duplicated entries with same name because of inventory reporting multiple changes on the same item in the same date range
		$this->db->group_by('items.item_id');

		// order by name of item by default
		$this->db->order_by($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/*
	Returns all the items
	*/
	public function get_all($stock_location_id = -1, $rows = 0, $limit_from = 0)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');

		if($stock_location_id > -1)
		{
			$this->db->join('item_quantities', 'item_quantities.item_id = items.item_id');
			$this->db->where('location_id', $stock_location_id);
		}

		$this->db->where('items.deleted', 0);

		// order by name of item
		$this->db->order_by('items.name', 'asc');

		if($rows > 0)
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
			$item_obj = new stdClass();

			//Get all the fields from items table
			foreach($this->db->list_fields('items') as $field)
			{
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}

	/*
	Gets information about a particular item by item id or number
	*/
	public function get_info_by_id_or_number($item_id)
	{
		$this->db->from('items');

		$this->db->group_start();

		$this->db->where('items.item_number', $item_id);

		// check if $item_id is a number and not a string starting with 0
		// because cases like 00012345 will be seen as a number where it is a barcode
		if(ctype_digit($item_id) && substr($item_id, 0, 1) != '0')
		{
			$this->db->or_where('items.item_id', (int) $item_id);
		}

		$this->db->group_end();

		$this->db->where('items.deleted', 0);

		// limit to only 1 so there is a result in case two are returned 
		// due to barcode and item_id clash
		$this->db->limit(1);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}

		return '';
	}

	/*
	Get an item id given an item number
	*/
	public function get_item_id($item_number, $ignore_deleted = FALSE, $deleted = FALSE)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->where('item_number', $item_number);
		if($ignore_deleted == FALSE)
		{
			$this->db->where('items.deleted', $deleted);
		}

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row()->item_id;
		}

		return FALSE;
	}

	/*
	Gets information about multiple items
	*/
	public function get_multiple_info($item_ids, $location_id)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->join('item_quantities', 'item_quantities.item_id = items.item_id', 'left');
		$this->db->where('location_id', $location_id);
		$this->db->where_in('items.item_id', $item_ids);

		return $this->db->get();
	}

	/*
	Inserts or updates a item
	*/
	public function save(&$item_data, $item_id = FALSE)
	{
		if(!$item_id || !$this->exists($item_id, TRUE))
		{
			if($this->db->insert('items', $item_data))
			{
				$item_data['item_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('item_id', $item_id);

		return $this->db->update('items', $item_data);
	}

	/*
	Updates multiple items at once
	*/
	public function update_multiple($item_data, $item_ids)
	{
		$this->db->where_in('item_id', explode(':', $item_ids));

		return $this->db->update('items', $item_data);
	}

	/*
	Deletes one item
	*/
	public function delete($item_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		// set to 0 quantities
		$this->Item_quantity->reset_quantity($item_id);
		$this->db->where('item_id', $item_id);
		$success = $this->db->update('items', array('deleted'=>1));
		$success &= $this->Inventory->reset_quantity($item_id);

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	/*
	Undeletes one item
	*/
	public function undelete($item_id)
	{
		$this->db->where('item_id', $item_id);

		return $this->db->update('items', array('deleted'=>0));
	}

	/*
	Deletes a list of items
	*/
	public function delete_list($item_ids)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		// set to 0 quantities
		$this->Item_quantity->reset_quantity_list($item_ids);
		$this->db->where_in('item_id', $item_ids);
		$success = $this->db->update('items', array('deleted'=>1));

		foreach($item_ids as $item_id)
		{
			$success &= $this->Inventory->reset_quantity($item_id);
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	function get_search_suggestion_format($seed = NULL)
	{
		$seed .= ',' . $this->config->item('suggestions_first_column');
		
		if($this->config->item('suggestions_second_column') !== '')
		{
			$seed .= ',' . $this->config->item('suggestions_second_column');
		}
			
		if($this->config->item('suggestions_third_column') !== '')
		{
			$seed .= ',' . $this->config->item('suggestions_third_column');
		}
		
		return $seed;
	}
	
	function get_search_suggestion_label($result_row)
	{
		$label1 = $this->config->item('suggestions_first_column');
		$label2 = $this->config->item('suggestions_second_column');
		$label3 = $this->config->item('suggestions_third_column');

		$label = $result_row->$label1;
		
		if($label2 !== '')
		{
			$label .= ' | '. $result_row->$label2;
		}
		
		if($label3 !== '')
		{
			$label .= ' | '. $result_row->$label3;
		}	
		
		return $label;
	}	
	
	public function get_search_suggestions($search, $filters = array('is_deleted' => FALSE, 'search_custom' => FALSE), $unique = FALSE, $limit = 25)
	{
		$suggestions = array();
		$non_kit = array(ITEM, ITEM_AMOUNT_ENTRY);

		$this->db->select($this->get_search_suggestion_format('item_id, name'));
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$this->db->like('name', $search);
		$this->db->order_by('name', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
		}

		$this->db->select($this->get_search_suggestion_format('item_id, item_number'));
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$this->db->like('item_number', $search);
		$this->db->order_by('item_number', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
		}

		if(!$unique)
		{
			//Search by category
			$this->db->select('category');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
			$this->db->distinct();
			$this->db->like('category', $search);
			$this->db->order_by('category', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('label' => $row->category);
			}

			//Search by supplier
			$this->db->select('company_name');
			$this->db->from('suppliers');
			$this->db->like('company_name', $search);
			// restrict to non deleted companies only if is_deleted is FALSE
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
			$this->db->distinct();
			$this->db->order_by('company_name', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('label' => $row->company_name);
			}

			//Search by description
			$this->db->select($this->get_search_suggestion_format('item_id, name, description'));
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
			$this->db->like('description', $search);
			$this->db->order_by('description', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$entry = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
				if(!array_walk($suggestions, function($value, $label) use ($entry) { return $entry['label'] != $label; } ))
				{
					$suggestions[] = $entry;
				}
			}

			//Search by custom fields
			if($filters['search_custom'] != FALSE)
			{
				$this->db->from('items');
				$this->db->group_start();
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
				$this->db->group_end();
				$this->db->where('deleted', $filters['is_deleted']);
				$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
				foreach($this->db->get()->result() as $row)
				{
					$suggestions[] = array('value' => $row->item_id, 'label' => get_search_suggestion_label($row));
				}
			}
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}


	public function get_stock_search_suggestions($search, $filters = array('is_deleted' => FALSE, 'search_custom' => FALSE), $unique = FALSE, $limit = 25)
	{
		$suggestions = array();
		$non_kit = array(ITEM, ITEM_AMOUNT_ENTRY);

		$this->db->select($this->get_search_suggestion_format('item_id, name'));
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$this->db->where("stock_type = '0'"); // stocked items only
		$this->db->like('name', $search);
		$this->db->order_by('name', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
		}

		$this->db->select($this->get_search_suggestion_format('item_id, item_number'));
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$this->db->where("stock_type = '0'"); // stocked items only
		$this->db->like('item_number', $search);
		$this->db->order_by('item_number', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
		}

		if(!$unique)
		{
			//Search by category
			$this->db->select('category');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
			$this->db->where("stock_type = '0'"); // stocked items only
			$this->db->distinct();
			$this->db->like('category', $search);
			$this->db->order_by('category', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('label' => $row->category);
			}

			//Search by supplier
			$this->db->select('company_name');
			$this->db->from('suppliers');
			$this->db->like('company_name', $search);
			// restrict to non deleted companies only if is_deleted is FALSE
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->distinct();
			$this->db->order_by('company_name', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('label' => $row->company_name);
			}

			//Search by description
			$this->db->select($this->get_search_suggestion_format('item_id, name, description'));
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
			$this->db->where("stock_type = '0'"); // stocked items only
			$this->db->like('description', $search);
			$this->db->order_by('description', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$entry = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
				if(!array_walk($suggestions, function($value, $label) use ($entry) { return $entry['label'] != $label; } ))
				{
					$suggestions[] = $entry;
				}
			}

			//Search by custom fields
			if($filters['search_custom'] != FALSE)
			{
				$this->db->from('items');
				$this->db->group_start();
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
				$this->db->group_end();
				$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
				$this->db->where("stock_type = '0'"); // stocked items only
				$this->db->where('deleted', $filters['is_deleted']);
				foreach($this->db->get()->result() as $row)
				{
					$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
				}
			}
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

	public function get_kit_search_suggestions($search, $filters = array('is_deleted' => FALSE, 'search_custom' => FALSE), $unique = FALSE, $limit = 25)
	{
		$suggestions = array();
		$non_kit = array(ITEM, ITEM_AMOUNT_ENTRY);

		$this->db->select('item_id, name');
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where_in('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$this->db->like('name', $search);
		$this->db->order_by('name', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->name);
		}

		$this->db->select('item_id, item_number');
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->like('item_number', $search);
		$this->db->order_by('item_number', 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->item_number);
		}

		if(!$unique)
		{
			//Search by category
			$this->db->select('category');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->distinct();
			$this->db->like('category', $search);
			$this->db->order_by('category', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('label' => $row->category);
			}

			//Search by supplier
			$this->db->select('company_name');
			$this->db->from('suppliers');
			$this->db->like('company_name', $search);
			// restrict to non deleted companies only if is_deleted is FALSE
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->distinct();
			$this->db->order_by('company_name', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$suggestions[] = array('label' => $row->company_name);
			}

			//Search by description
			$this->db->select('item_id, name, description');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->like('description', $search);
			$this->db->order_by('description', 'asc');
			foreach($this->db->get()->result() as $row)
			{
				$entry = array('value' => $row->item_id, 'label' => $row->name);
				if(!array_walk($suggestions, function($value, $label) use ($entry) { return $entry['label'] != $label; } ))
				{
					$suggestions[] = $entry;
				}
			}

			//Search by custom fields
			if($filters['search_custom'] != FALSE)
			{
				$this->db->from('items');
				$this->db->group_start();
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
				$this->db->group_end();
				$this->db->where('deleted', $filters['is_deleted']);
				foreach($this->db->get()->result() as $row)
				{
					$suggestions[] = array('value' => $row->item_id, 'label' => $row->name);
				}
			}
		}

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0, $limit);
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
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('label' => $row->category);
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
		foreach($this->db->get()->result() as $row)
		{
			$suggestions[] = array('label' => $row->location);
		}

		return $suggestions;
	}

	public function get_custom_suggestions($search, $field_no)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom'.$field_no);
		$this->db->from('items');
		$this->db->like('custom'.$field_no, $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom'.$field_no, 'asc');
		foreach($this->db->get()->result() as $row)
		{
			$row_array = (array) $row;
			$suggestions[] = array('label' => $row_array['custom'.$field_no]);
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
	 * caution: must be used before item_quantities gets updated, otherwise the average price is wrong!
	 *
	 */
	public function change_cost_price($item_id, $items_received, $new_price, $old_price = NULL)
	{
		if($old_price === NULL)
		{
			$item_info = $this->get_info($item_id);
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
