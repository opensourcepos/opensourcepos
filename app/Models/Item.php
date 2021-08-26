<?php

namespace App\Models;

use CodeIgniter\Model;
use stdClass;

/**
 * Item class
 */

class Item extends Model
{
	/*
	* Determines if a given item_id is an item
	*/

	public function exists($item_id, $ignore_deleted = FALSE, $deleted = FALSE): bool
	{
		// check if $item_id is a number and not a string starting with 0
		// because cases like 00012345 will be seen as a number where it is a barcode
		if(ctype_digit($item_id) && substr($item_id, 0, 1) !== '0')
		{
			$builder = $this->db->table('items');
			$builder->where('item_id', intval($item_id));

			if($ignore_deleted === FALSE)
			{
				$builder->where('deleted', $deleted);
			}

			return ($builder->get()->getNumRows() === 1);
		}

		return FALSE;
	}

	/*
	* Determines if a given item_number exists
	*/
	public function item_number_exists($item_number, $item_id = ''): bool
	{
		if($this->config->item('allow_duplicate_barcodes') != FALSE)
		{
			return FALSE;
		}

		$builder = $this->db->table('items');
		$builder->where('item_number', (string) $item_number);
		// check if $item_id is a number and not a string starting with 0
		// because cases like 00012345 will be seen as a number where it is a barcode
		if(ctype_digit($item_id) && substr($item_id, 0, 1) != '0')
		{
			$builder->where('item_id !=', intval($item_id));
		}

		return ($builder->get()->getNumRows() >= 1);
	}

	/*
	* Gets total of rows
	*/
	public function get_total_rows()
	{
		$builder = $this->db->table('items');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	public function get_tax_category_usage($tax_category_id)
	{
		$builder = $this->db->table('items');
		$builder->where('tax_category_id', $tax_category_id);

		return $builder->countAllResults();
	}

	/*
	* Get number of rows
	*/
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters, 0, 0, 'items.name', 'asc', TRUE);
	}

	/*
	* Perform a search on items
	*/
	public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'items.name', $order = 'asc', $count_only = FALSE)
	{
		$builder = $this->db->table('items AS items');	//TODO: I'm not sure if it's needed to write items AS items... I think you can just get away with items

		// get_found_rows case
		if($count_only === TRUE)
		{
			$builder->select('COUNT(DISTINCT items.item_id) AS count');
		}
		else
		{
			$builder->select('MAX(items.item_id) AS item_id');
			$builder->select('MAX(items.name) AS name');
			$builder->select('MAX(items.category) AS category');
			$builder->select('MAX(items.supplier_id) AS supplier_id');
			$builder->select('MAX(items.item_number) AS item_number');
			$builder->select('MAX(items.description) AS description');
			$builder->select('MAX(items.cost_price) AS cost_price');
			$builder->select('MAX(items.unit_price) AS unit_price');
			$builder->select('MAX(items.reorder_level) AS reorder_level');
			$builder->select('MAX(items.receiving_quantity) AS receiving_quantity');
			$builder->select('MAX(items.pic_filename) AS pic_filename');
			$builder->select('MAX(items.allow_alt_description) AS allow_alt_description');
			$builder->select('MAX(items.is_serialized) AS is_serialized');
			$builder->select('MAX(items.pack_name) AS pack_name');
			$builder->select('MAX(items.tax_category_id) AS tax_category_id');
			$builder->select('MAX(items.deleted) AS deleted');

			$builder->select('MAX(suppliers.person_id) AS person_id');
			$builder->select('MAX(suppliers.company_name) AS company_name');
			$builder->select('MAX(suppliers.agency_name) AS agency_name');
			$builder->select('MAX(suppliers.account_number) AS account_number');
			$builder->select('MAX(suppliers.deleted) AS deleted');

			$builder->select('MAX(inventory.trans_id) AS trans_id');
			$builder->select('MAX(inventory.trans_items) AS trans_items');
			$builder->select('MAX(inventory.trans_user) AS trans_user');
			$builder->select('MAX(inventory.trans_date) AS trans_date');
			$builder->select('MAX(inventory.trans_comment) AS trans_comment');
			$builder->select('MAX(inventory.trans_location) AS trans_location');
			$builder->select('MAX(inventory.trans_inventory) AS trans_inventory');

			if($filters['stock_location_id'] > -1)
			{
				$builder->select('MAX(item_quantities.item_id) AS qty_item_id');
				$builder->select('MAX(item_quantities.location_id) AS location_id');
				$builder->select('MAX(item_quantities.quantity) AS quantity');
			}
		}

		$builder->join('suppliers AS suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$builder->join('inventory AS inventory', 'inventory.trans_items = items.item_id');

		if($filters['stock_location_id'] > -1)
		{
			$builder->join('item_quantities AS item_quantities', 'item_quantities.item_id = items.item_id');
			$builder->where('location_id', $filters['stock_location_id']);
		}

		if(empty($this->config->item('date_or_time_format')))
		{
			$builder->where('DATE_FORMAT(trans_date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$builder->where('trans_date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		$attributes_enabled = count($filters['definition_ids']) > 0;

		if(!empty($search))
		{
			if ($attributes_enabled && $filters['search_custom'])
			{
				$builder->having("attribute_values LIKE '%$search%'");
				$builder->orHaving("attribute_dtvalues LIKE '%$search%'");
				$builder->orHaving("attribute_dvalues LIKE '%$search%'");
			}
			else
			{
				$builder->groupStart();
					$builder->like('name', $search);
					$builder->orLike('item_number', $search);
					$builder->orLike('items.item_id', $search);
					$builder->orLike('company_name', $search);
					$builder->orLike('items.category', $search);
				$builder->groupEnd();
			}
		}

		if($attributes_enabled)
		{
			$format = $this->db->escape(dateformat_mysql());
			$this->db->simpleQuery('SET SESSION group_concat_max_len=49152');
			$builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_value) ORDER BY definition_id SEPARATOR \'|\') AS attribute_values');
			$builder->select("GROUP_CONCAT(DISTINCT CONCAT_WS('_', definition_id, DATE_FORMAT(attribute_date, $format)) SEPARATOR '|') AS attribute_dtvalues");
			$builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_decimal) SEPARATOR \'|\') AS attribute_dvalues');
			$builder->join('attribute_links', 'attribute_links.item_id = items.item_id AND attribute_links.receiving_id IS NULL AND attribute_links.sale_id IS NULL AND definition_id IN (' . implode(',', $filters['definition_ids']) . ')', 'left');
			$builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id', 'left');
		}

		$builder->where('items.deleted', $filters['is_deleted']);

		if($filters['empty_upc'] != FALSE)
		{
			$builder->where('item_number', NULL);
		}
		if($filters['low_inventory'] != FALSE)
		{
			$builder->where('quantity <=', 'reorder_level');
		}
		if($filters['is_serialized'] != FALSE)
		{
			$builder->where('is_serialized', 1);
		}
		if($filters['no_description'] != FALSE)
		{
			$builder->where('items.description', '');
		}
		if($filters['temporary'] != FALSE)
		{
			$builder->where('items.item_type', ITEM_TEMP);
		}
		else
		{
			$non_temp = array(ITEM, ITEM_KIT, ITEM_AMOUNT_ENTRY);
			$builder->whereIn('items.item_type', $non_temp);
		}

		// get_found_rows case
		if($count_only === TRUE)
		{
			return $builder->get()->getRow()->count;
		}

		// avoid duplicated entries with same name because of inventory reporting multiple changes on the same item in the same date range
		$builder->groupBy('items.item_id');

		// order by name of item by default
		$builder->orderBy($sort, $order);

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/*
	* Returns all the items
	*/
	public function get_all($stock_location_id = -1, $rows = 0, $limit_from = 0)
	{
		$builder = $this->db->table('items');

		if($stock_location_id > -1)
		{
			$builder->join('item_quantities', 'item_quantities.item_id = items.item_id');
			$builder->where('location_id', $stock_location_id);
		}

		$builder->where('items.deleted', 0);

		// order by name of item
		$builder->orderBy('items.name', 'asc');

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/*
	* Gets information about a particular item
	*/
	public function get_info($item_id)
	{
		$builder = $this->db->table('items');
		$builder->select('items.*');
		$builder->select('GROUP_CONCAT(attribute_value SEPARATOR \'|\') AS attribute_values');
		$builder->select('GROUP_CONCAT(attribute_decimal SEPARATOR \'|\') AS attribute_dvalues');
		$builder->select('GROUP_CONCAT(attribute_date SEPARATOR \'|\') AS attribute_dtvalues');
		$builder->join('attribute_links', 'attribute_links.item_id = items.item_id', 'left');
		$builder->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id', 'left');
		$builder->where('items.item_id', $item_id);
		$builder->groupBy('items.item_id');

		$query = $builder->get();

		if($query->getNumRows() == 1)
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj = new stdClass();

			//Get all the fields from items table
			foreach($this->db->getFieldNames('items') as $field)
			{
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}

	/*
	* Gets information about a particular item by item id or number
	*/
	public function get_info_by_id_or_number($item_id, $include_deleted = TRUE)
	{
		$builder = $this->db->table('items');
		$builder->groupStart();
		$builder->where('items.item_number', $item_id);

		// check if $item_id is a number and not a string starting with 0
		// because cases like 00012345 will be seen as a number where it is a barcode
		if(ctype_digit($item_id) && substr($item_id, 0, 1) != '0')
		{
			$builder->orWhere('items.item_id', intval($item_id));
		}

		$builder->groupEnd();

		if(!$include_deleted)
		{
			$builder->where('items.deleted', 0);
		}

		// limit to only 1 so there is a result in case two are returned
		// due to barcode and item_id clash
		$builder->limit(1);

		$query = $builder->get();

		if($query->getNumRows() == 1)
		{
			return $query->getRow();
		}

		return '';
	}

	/*
	* Get an item id given an item number
	*/
	public function get_item_id($item_number, $ignore_deleted = FALSE, $deleted = FALSE)
	{
		$builder = $this->db->table('items');
		$builder->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$builder->where('item_number', $item_number);
		if($ignore_deleted == FALSE)
		{
			$builder->where('items.deleted', $deleted);
		}

		$query = $builder->get();

		if($query->getNumRows() == 1)
		{
			return $query->getRow()->item_id;
		}

		return FALSE;
	}

	/*
	Gets information about multiple items
	*/
	public function get_multiple_info($item_ids, $location_id)
	{
		$format = $this->db->escape(dateformat_mysql());

		$builder = $this->db->table('items');
		$builder->select('items.*');
		$builder->select('MAX(company_name) AS company_name');
		$builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_value) ORDER BY definition_id SEPARATOR \'|\') AS attribute_values');
		$builder->select("GROUP_CONCAT(DISTINCT CONCAT_WS('_', definition_id, DATE_FORMAT(attribute_date, $format)) ORDER BY definition_id SEPARATOR '|') AS attribute_dtvalues");
		$builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_decimal) ORDER BY definition_id SEPARATOR \'|\') AS attribute_dvalues');
		$builder->select('MAX(quantity) as quantity');

		$builder->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$builder->join('item_quantities', 'item_quantities.item_id = items.item_id', 'left');
		$builder->join('attribute_links', 'attribute_links.item_id = items.item_id AND sale_id IS NULL AND receiving_id IS NULL', 'left');
		$builder->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id', 'left');

		$builder->where('location_id', $location_id);
		$builder->whereIn('items.item_id', $item_ids);

		$builder->groupBy('items.item_id');

		return $builder->get();
	}

	/*
	* Inserts or updates an item
	*/
	public function save(&$item_data, $item_id = FALSE): bool
	{
		$builder = $this->db->table('items');

		if(!$item_id || !$this->exists($item_id, TRUE))
		{
			if($builder->insert('items', $item_data))
			{
				$item_data['item_id'] = $this->db->insertID();
				if($item_data['low_sell_item_id'] == -1)
				{
					$builder = $this->db->table('items');
					$builder->where('item_id', $item_data['item_id']);
					$builder->update(['low_sell_item_id' => $item_data['item_id']]);
				}

				return TRUE;
			}

			return FALSE;
		}
		else
		{
			$item_data['item_id'] = $item_id;
		}

		$builder->where('item_id', $item_id);

		return $builder->update('items', $item_data);
	}

	/*
	* Updates multiple items at once
	*/
	public function update_multiple($item_data, $item_ids): bool
	{
		$builder = $this->db->table('items');
		$builder->whereIn('item_id', explode(':', $item_ids));

		return $builder->update($item_data);
	}

	/*
	* Deletes one item
	*/
	public function delete($item_id): bool
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		// set to 0 quantities
		$this->Item_quantity->reset_quantity($item_id);

		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);
		$success = $builder->update(['deleted' => 1]);

		$success &= $this->Inventory->reset_quantity($item_id);

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	/*
	* Undeletes one item
	*/
	public function undelete($item_id): bool
	{
		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);

		return $builder->update(['deleted' => 0]);
	}

	/*
	* Deletes a list of items
	*/
	public function delete_list($item_ids): bool
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		// set to 0 quantities
		$this->Item_quantity->reset_quantity_list($item_ids);

		$builder = $this->db->table('items');
		$builder->whereIn('item_id', $item_ids);
		$success = $builder->update(['deleted' => 1]);

		foreach($item_ids as $item_id)
		{
			$success &= $this->Inventory->reset_quantity($item_id);
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	function get_search_suggestion_format($seed = NULL): string
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

	function get_search_suggestion_label($result_row): string
	{
		$label = '';
		$label1 = $this->config->item('suggestions_first_column');
		$label2 = $this->config->item('suggestions_second_column');
		$label3 = $this->config->item('suggestions_third_column');

		// If multi_pack enabled then if "name" is part of the search suggestions then append pack
		if($this->config->item('multi_pack_enabled') == '1')
		{
			$this->append_label($label, $label1, $result_row);
			$this->append_label($label, $label2, $result_row);
			$this->append_label($label, $label3, $result_row);
		}
		else
		{
			$label = $result_row->$label1;

			if($label2 !== '')
			{
				$label .= NAME_SEPARATOR . $result_row->$label2;
			}

			if($label3 !== '')
			{
				$label .= NAME_SEPARATOR . $result_row->$label3;
			}
		}

		return $label;
	}

	private function append_label(&$label, $item_field_name, $item_info)
	{
		if($item_field_name !== '')
		{
			if($label == '')
			{
				if($item_field_name == 'name')
				{
					$label .= implode(NAME_SEPARATOR, array($item_info->name, $item_info->pack_name));
				}
				else
				{
					$label .= $item_info->$item_field_name;
				}
			}
			else
			{
				if($item_field_name == 'name')
				{
					$label .= implode(NAME_SEPARATOR, array('', $item_info->name, $item_info->pack_name));
				}
				else
				{
					$label .= NAME_SEPARATOR . $item_info->$item_field_name;
				}
			}
		}
	}

	public function get_search_suggestions($search, $filters = array('is_deleted' => FALSE, 'search_custom' => FALSE), $unique = FALSE, $limit = 25): array
	{
		$suggestions = [];
		$non_kit = array(ITEM, ITEM_AMOUNT_ENTRY);

		$builder = $this->db->table('items');
		$builder->select($this->get_search_suggestion_format('item_id, name, pack_name'));
		$builder->where('deleted', $filters['is_deleted']);
		$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$builder->like('name', $search);//TODO: this and the next 11 lines are duplicated directly below.  We should extract a method here.
		$builder->orderBy('name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
		}

		$builder = $this->db->table('items');
		$builder->select($this->get_search_suggestion_format('item_id, item_number, pack_name'));
		$builder->where('deleted', $filters['is_deleted']);
		$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$builder->like('item_number', $search);
		$builder->orderBy('item_number', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
		}

		if(!$unique)
		{
			//Search by category
			$builder = $this->db->table('items');
			$builder->select('category');
			$builder->where('deleted', $filters['is_deleted']);
			$builder->distinct();	//TODO: duplicate code.  Refactor method.
			$builder->like('category', $search);
			$builder->orderBy('category', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('label' => $row->category);
			}

			$builder = $this->db->table('suppliers');

			//Search by supplier
			$builder->select('company_name');
			$builder->like('company_name', $search);

			// restrict to non deleted companies only if is_deleted is FALSE
			$builder->where('deleted', $filters['is_deleted']);
			$builder->distinct();
			$builder->orderBy('company_name', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('label' => $row->company_name);
			}

			//Search by description
			$builder = $this->db->table('items');
			$builder->select($this->get_search_suggestion_format('item_id, name, pack_name, description'));
			$builder->where('deleted', $filters['is_deleted']);
			$builder->like('description', $search);//TODO: duplicate code, refactor method.
			$builder->orderBy('description', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$entry = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
				if(!array_walk($suggestions, function($value, $label) use ($entry) { return $entry['label'] != $label; } ))
				{
					$suggestions[] = $entry;
				}
			}

			//Search in attributes
			if($filters['search_custom'] !== FALSE)
			{
				$builder = $this->db->table('attribute_links');
				$builder->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id');
				$builder->join('attribute_definitions', 'attribute_definitions.definition_id = attribute_links.definition_id');
				$builder->like('attribute_value', $search);
				$builder->where('definition_type', TEXT);
				$builder->where('deleted', $filters['is_deleted']);
				$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later

				foreach($builder->get()->getResult() as $row)
				{
					$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
				}
			}
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return array_unique($suggestions, SORT_REGULAR);
	}


	public function get_stock_search_suggestions($search, $filters = array('is_deleted' => FALSE, 'search_custom' => FALSE), $unique = FALSE, $limit = 25): array
	{
		$suggestions = [];
		$non_kit = array(ITEM, ITEM_AMOUNT_ENTRY);

		$builder = $this->db->table('items');
		$builder->select($this->get_search_suggestion_format('item_id, name, pack_name'));
		$builder->where('deleted', $filters['is_deleted']);
		$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$builder->where('stock_type', '0'); // stocked items only
		$builder->like('name', $search);
		$builder->orderBy('name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
		}

		$builder = $this->db->table('items');
		$builder->select($this->get_search_suggestion_format('item_id, item_number, pack_name'));
		$builder->where('deleted', $filters['is_deleted']);
		$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$builder->where('stock_type', '0'); // stocked items only
		$builder->like('item_number', $search);
		$builder->orderBy('item_number', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
		}

		if(!$unique)
		{
			//Search by category
			$builder = $this->db->table('items');
			$builder->select('category');
			$builder->where('deleted', $filters['is_deleted']);
			$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
			$builder->where('stock_type', '0'); // stocked items only
			$builder->distinct();
			$builder->like('category', $search);
			$builder->orderBy('category', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('label' => $row->category);
			}

			//Search by supplier
			$builder = $this->db->table('suppliers');
			$builder->select('company_name');
			$builder->like('company_name', $search);

			// restrict to non deleted companies only if is_deleted is FALSE
			$builder->where('deleted', $filters['is_deleted']);
			$builder->distinct();
			$builder->orderBy('company_name', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('label' => $row->company_name);
			}

			//Search by description
			$builder = $this->db->table('items');
			$builder->select($this->get_search_suggestion_format('item_id, name, pack_name, description'));
			$builder->where('deleted', $filters['is_deleted']);
			$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
			$builder->where('stock_type', '0'); // stocked items only
			$builder->like('description', $search);	//TODO: duplicated code, refactor method.
			$builder->orderBy('description', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$entry = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
				if(!array_walk($suggestions, function($value, $label) use ($entry) { return $entry['label'] != $label; } ))
				{
					$suggestions[] = $entry;
				}
			}

			//Search by custom fields
			if($filters['search_custom'] !== FALSE)	//TODO: duplicated code.  We should refactor out a method
			{
				$builder = $this->db->table('attribute_links');
				$builder->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id');
				$builder->join('attribute_definitions', 'attribute_definitions.definition_id = attribute_links.definition_id');
				$builder->like('attribute_value', $search);
				$builder->where('definition_type', TEXT);
				$builder->where('stock_type', '0'); // stocked items only
				$builder->where('deleted', $filters['is_deleted']);

				foreach($builder->get()->getResult() as $row)
				{
					$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
				}
			}
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return array_unique($suggestions, SORT_REGULAR);
	}

	public function get_kit_search_suggestions($search, $filters = array('is_deleted' => FALSE, 'search_custom' => FALSE), $unique = FALSE, $limit = 25): array
	{
		$suggestions = [];
		$non_kit = array(ITEM, ITEM_AMOUNT_ENTRY);

		$builder = $this->db->table('items');
		$builder->select('item_id, name');
		$builder->where('deleted', $filters['is_deleted']);
		$builder->where('item_type', ITEM_KIT);
		$builder->like('name', $search);
		$builder->orderBy('name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->name);
		}

		$builder = $this->db->table('items');
		$builder->select('item_id, item_number');
		$builder->where('deleted', $filters['is_deleted']);
		$builder->like('item_number', $search);
		$builder->where('item_type', ITEM_KIT);
		$builder->orderBy('item_number', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->item_number);
		}

		if(!$unique)
		{
			//Search by category
			$builder = $this->db->table('items');
			$builder->select('category');
			$builder->where('deleted', $filters['is_deleted']);
			$builder->where('item_type', ITEM_KIT);
			$builder->distinct();//TODO: duplicated code, refactor method.
			$builder->like('category', $search);
			$builder->orderBy('category', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('label' => $row->category);
			}

			//Search by supplier
			$builder = $this->db->table('suppliers');
			$builder->select('company_name');
			$builder->like('company_name', $search);

			// restrict to non deleted companies only if is_deleted is FALSE
			$builder->where('deleted', $filters['is_deleted']);
			$builder->distinct();
			$builder->orderBy('company_name', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('label' => $row->company_name);
			}

			//Search by description
			$builder = $this->db->table('items');
			$builder->select('item_id, name, description');
			$builder->where('deleted', $filters['is_deleted']);
			$builder->where('item_type', ITEM_KIT);
			$builder->like('description', $search);
			$builder->orderBy('description', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$entry = array('value' => $row->item_id, 'label' => $row->name);
				if(!array_walk($suggestions, function($value, $label) use ($entry) { return $entry['label'] != $label; } ))
				{
					$suggestions[] = $entry;
				}
			}

			//Search in attributes
			if($filters['search_custom'] !== FALSE)
			{
				$builder = $this->db->table('attribute_links');
				$builder->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id');
				$builder->join('attribute_definitions', 'attribute_definitions.definition_id = attribute_links.definition_id');
				$builder->like('attribute_value', $search);
				$builder->where('definition_type', TEXT);
				$builder->where('stock_type', '0'); // stocked items only
				$builder->where('deleted', $filters['is_deleted']);

				foreach($builder->get()->getResult() as $row)
				{
					$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
				}
			}
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return array_unique($suggestions, SORT_REGULAR);
	}

	public function get_low_sell_suggestions($search): array
	{
		$suggestions = [];

		$builder = $this->db->table('items');
		$builder->select($this->get_search_suggestion_format('item_id, pack_name'));
		$builder->where('deleted', '0');
		$builder->where('stock_type', '0'); // stocked items only
		$builder->like('name', $search);
		$builder->orderBy('name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row));
		}

		return $suggestions;
	}

	public function get_category_suggestions($search): array
	{
		$suggestions = [];

		$builder = $this->db->table('items');
		$builder->distinct();
		$builder->select('category');
		$builder->like('category', $search);
		$builder->where('deleted', 0);
		$builder->orderBy('category', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('label' => $row->category);
		}

		return $suggestions;
	}

	public function get_location_suggestions($search): array
	{
		$suggestions = [];

		$builder = $this->db->table('items');
		$builder->distinct();
		$builder->select('location');
		$builder->like('location', $search);
		$builder->where('deleted', 0);
		$builder->orderBy('location', 'asc');
		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('label' => $row->location);
		}

		return $suggestions;
	}

	public function get_categories()
	{
		$builder = $this->db->table('items');
		$builder->select('category');
		$builder->where('deleted', 0);
		$builder->distinct();
		$builder->orderBy('category', 'asc');

		return $builder->get();
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
	public function change_cost_price($item_id, $items_received, $new_price, $old_price = NULL): bool
	{
		if($old_price === NULL)
		{
			$item_info = $this->get_info($item_id);
			$old_price = $item_info->cost_price;
		}

		$builder = $this->db->table('item_quantities');
		$this->db->select_sum('quantity');
		$builder->where('item_id', $item_id);
		$builder->join('stock_locations', 'stock_locations.location_id=item_quantities.location_id');
		$builder->where('stock_locations.deleted', 0);
		$old_total_quantity = $builder->get()->getRow()->quantity;

		$total_quantity = $old_total_quantity + $items_received;
		$average_price = bcdiv(bcadd(bcmul($items_received, $new_price), bcmul($old_total_quantity, $old_price)), $total_quantity);

		$data = array('cost_price' => $average_price);

		return $this->save($data, $item_id);
	}

	public function update_item_number($item_id, $item_number)
	{
		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);
		$builder->update(['item_number' => $item_number]);	//TODO: this function should probably return the result of update() and add ": bool" to the function signature
	}

	public function update_item_name($item_id, $item_name)	//TODO: this function should probably return the result of update() and add ": bool" to the function signature
	{
		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);
		$builder->update(['name' => $item_name]);
	}

	public function update_item_description($item_id, $item_description)	//TODO: this function should probably return the result of update() and add ": bool" to the function signature
	{
		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);
		$builder->update(['description' => $item_description]);
	}

	/**
	 * Determine the item name to use taking into consideration that
	 * for a multipack environment then the item name should have the
	 * pack appended to it
	 */
	function get_item_name($as_name = NULL): string
	{
		if($as_name == NULL)
		{
			$as_name = '';
		}
		else
		{
			$as_name = ' AS ' . $as_name;
		}

		if($this->config->item('multi_pack_enabled') == '1')
		{
			$item_name = "concat(items.name,'" . NAME_SEPARATOR . '\', items.pack_name)' . $as_name;
		}
		else
		{
			$item_name = 'items.name' . $as_name;
		}
		return $item_name;
	}
}
?>
