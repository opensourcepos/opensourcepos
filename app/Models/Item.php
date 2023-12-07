<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use Config\OSPOS;
use ReflectionException;
use stdClass;

/**
 * Item class
 *
 * @property inventory inventory
 * @property item_quantity item_quantity
 */
class Item extends Model
{
	protected $table = 'items';
	protected $primaryKey = 'item_id';
	protected $useAutoIncrement = true;
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'name',
		'category',
		'supplier_id',
		'item_number',
		'description',
		'cost_price',
		'unit_price',
		'reorder_level',
		'allow_alt_description',
		'is_serialized',
		'deleted',
		'stock_type',
		'item_type',
		'tax_category_id',
		'receiving_quantity',
		'pic_filename',
		'qty_per_pack',
		'pack_name',
		'low_sell_item_id',
		'hsn_code'
	];


	/**
	 * Determines if a given item_id is an item
	 */
	public function exists(int $item_id, bool $ignore_deleted = false, bool $deleted = false): bool
	{
		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);

		if(!$ignore_deleted)
		{
			$builder->where('deleted', $deleted);
		}

		return ($builder->get()->getNumRows() === 1);
	}

	/**
	 * Determines if a given item_number exists
	 */
	public function item_number_exists(string $item_number, string $item_id = ''): bool
	{
		$config = config(OSPOS::class)->settings;

		if($config['allow_duplicate_barcodes'])
		{
			return false;
		}

		$builder = $this->db->table('items');
		$builder->where('item_number', $item_number);
		$builder->where('deleted !=', 1);
		$builder->where('item_id !=', intval($item_id));

//		// check if $item_id is a number and not a string starting with 0
//		// because cases like 00012345 will be seen as a number where it is a barcode
		if(ctype_digit($item_id) && substr($item_id, 0, 1) != '0')    //TODO: !==
		{
			$builder->where('item_id !=', intval($item_id));
		}
		return ($builder->get()->getNumRows()) >= 1;
	}

	/**
	 * Gets total of rows
	 */
	public function get_total_rows(): int
	{
		$builder = $this->db->table('items');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	/**
	 * @param int $tax_category_id
	 * @return int
	 */
	public function get_tax_category_usage(int $tax_category_id): int    //TODO: This function is never called in the code.
	{
		$builder = $this->db->table('items');
		$builder->where('tax_category_id', $tax_category_id);

		return $builder->countAllResults();
	}

	/**
	 * Get number of rows
	 */
	public function get_found_rows(string $search, array $filters): int
	{
		return $this->search($search, $filters, 0, 0, 'items.name', 'asc', true);
	}

	/**
	 * Perform a search on items
	 */
	public function search(string $search, array $filters, ?int $rows = 0, ?int $limit_from = 0, ?string $sort = 'items.name', ?string $order = 'asc', ?bool $count_only = false)
	{
		// Set default values
		if($rows == null)
		{
			$rows = 0;
		}
		if($limit_from == null)
		{
			$limit_from = 0;
		}
		if($sort == null)
		{
			$sort = 'items.name';
		}
		if($order == null)
		{
			$order = 'asc';
		}
		if($count_only == null)
		{
			$count_only = false;
		}

		$config = config(OSPOS::class)->settings;
		$builder = $this->db->table('items AS items');    //TODO: I'm not sure if it's needed to write items AS items... I think you can just get away with items

		// get_found_rows case
		if($count_only)
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

		if(empty($config['date_or_time_format']))    //TODO: This needs to be replaced with Ternary notation
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
			if($attributes_enabled && $filters['search_custom'])
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

		if($filters['empty_upc'])
		{
			$builder->where('item_number', null);
		}
		if($filters['low_inventory'])
		{
			$builder->where('quantity <=', 'reorder_level');
		}
		if($filters['is_serialized'])
		{
			$builder->where('is_serialized', 1);
		}
		if($filters['no_description'])
		{
			$builder->where('items.description', '');
		}
		if($filters['temporary'])
		{
			$builder->where('items.item_type', ITEM_TEMP);
		}
		else
		{
			$non_temp = [ITEM, ITEM_KIT, ITEM_AMOUNT_ENTRY];
			$builder->whereIn('items.item_type', $non_temp);
		}

		// get_found_rows case
		if($count_only)
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

	/**
	 * Returns all the items
	 */
	public function get_all(int $stock_location_id = NEW_ENTRY, int $rows = 0, int $limit_from = 0): ResultInterface
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

	/**
	 * Gets information about a particular item
	 */
	public function get_info(int $item_id): object
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

		return $this->getEmptyObject('items');
	}

	/**
	 * Initializes an empty object based on database definitions
	 * @param string $table_name
	 * @return object
	 */
	private function getEmptyObject(string $table_name): object
	{
		// Return an empty base parent object, as $item_id is NOT an item
		$empty_obj = new stdClass();

		// Iterate through field definitions to determine how the fields should be initialized

		foreach($this->db->getFieldData($table_name) as $field)
		{

			$field_name = $field->name;

			if(in_array($field->type, array('int', 'tinyint', 'decimal')))
			{
				$empty_obj->$field_name = ($field->primary_key == 1) ? NEW_ENTRY : 0;
			}
			else
			{
				$empty_obj->$field_name = null;
			}
		}

		return $empty_obj;
	}

	/**
	 * Gets information about a particular item by item id or number
	 */
	public function get_info_by_id_or_number(int $item_id, bool $include_deleted = true)
	{
		$builder = $this->db->table('items');
		$builder->groupStart();
		$builder->where('items.item_number', $item_id);

		// check if $item_id is a number and not a string starting with 0
		// because cases like 00012345 will be seen as a number where it is a barcode
		if(ctype_digit(strval($item_id)) && substr($item_id, 0, 1) != '0')
		{
			$builder->orWhere('items.item_id', $item_id);
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

	/**
	 * Get an item id given an item number
	 */
	public function get_item_id(string $item_number, bool $ignore_deleted = false, bool $deleted = false): bool
	{
		$builder = $this->db->table('items');
		$builder->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$builder->where('item_number', $item_number);

		if(!$ignore_deleted)
		{
			$builder->where('items.deleted', $deleted);
		}

		$query = $builder->get();

		if($query->getNumRows() == 1)    //TODO: ===
		{
			return $query->getRow()->item_id;
		}

		return false;
	}

	/**
	 * Gets information about multiple items
	 */
	public function get_multiple_info(array $item_ids, int $location_id): ResultInterface
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

	/**
	 * Inserts or updates an item
	 */
	public function save_value(array &$item_data, int $item_id = NEW_ENTRY): bool    //TODO: need to bring this in line with parent or change the name
	{
		$builder = $this->db->table('items');

		if($item_id == NEW_ENTRY || !$this->exists($item_id, true))
		{
			if($builder->insert($item_data))
			{
				$item_data['item_id'] = $this->db->insertID();
				if($item_data['low_sell_item_id'] == NEW_ENTRY)
				{
					$builder = $this->db->table('items');
					$builder->where('item_id', $item_data['item_id']);
					$builder->update(['low_sell_item_id' => $item_data['item_id']]);
				}

				return true;
			}

			return false;
		}
		else
		{
			$item_data['item_id'] = $item_id;
		}

		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);

		return $builder->update($item_data);
	}

	/**
	 * Updates multiple items at once
	 */
	public function update_multiple(array $item_data, string $item_ids): bool
	{
		$builder = $this->db->table('items');
		$builder->whereIn('item_id', explode(':', $item_ids));

		return $builder->update($item_data);
	}

	/**
	 * Deletes one item
	 */
	public function delete($item_id = null, bool $purge = false)
	{
		$this->db->transStart();

		// set to 0 quantities
		$item_quantity = model(Item_quantity::class);
		$item_quantity->reset_quantity($item_id);

		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);
		$success = $builder->update(['deleted' => 1]);

		$inventory = model(Inventory::class);
		$success &= $inventory->reset_quantity($item_id);

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	/**
	 * Undeletes one item
	 */
	public function undelete(int $item_id): bool
	{
		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);

		return $builder->update(['deleted' => 0]);
	}

	/**
	 * Deletes a list of items
	 */
	public function delete_list(array $item_ids): bool
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		// set to 0 quantities
		$item_quantity = model(Item_quantity::class);
		$item_quantity->reset_quantity_list($item_ids);

		$builder = $this->db->table('items');
		$builder->whereIn('item_id', $item_ids);
		$success = $builder->update(['deleted' => 1]);

		$inventory = model(Inventory::class);

		foreach($item_ids as $item_id)
		{
			$success &= $inventory->reset_quantity($item_id);
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	/**
	 * @param string|null $seed
	 * @return string
	 */
	public function get_search_suggestion_format(string $seed = null): string
	{
		$config = config(OSPOS::class)->settings;
		$seed .= ',' . $config['suggestions_first_column'];

		if($config['suggestions_second_column'] !== '')
		{
			$seed .= ',' . $config['suggestions_second_column'];
		}

		if($config['suggestions_third_column'] !== '')
		{
			$seed .= ',' . $config['suggestions_third_column'];
		}

		return $seed;
	}

	/**
	 * @param $result_row
	 * @return string
	 */
	public function get_search_suggestion_label($result_row): string
	{
		$config = config(OSPOS::class)->settings;
		$label = '';
		$label1 = $config['suggestions_first_column'];
		$label2 = $config['suggestions_second_column'];
		$label3 = $config['suggestions_third_column'];

		// If multi_pack enabled then if "name" is part of the search suggestions then append pack
		if($config['multi_pack_enabled'])
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

	/**
	 * @param string $label
	 * @param string $item_field_name
	 * @param object $item_info
	 * @return void
	 */
	private function append_label(string &$label, string $item_field_name, object $item_info): void
	{
		if($item_field_name !== '')
		{
			if($label == '')
			{
				if($item_field_name == 'name')    //TODO: This needs to be replaced with Ternary notation if possible
				{
					$label .= implode(NAME_SEPARATOR, [$item_info->name, $item_info->pack_name]);    //TODO: no need for .= operator.  If it gets here then that means label is an empty string.
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
					$label .= implode(NAME_SEPARATOR, ['', $item_info->name, $item_info->pack_name]);
				}
				else
				{
					$label .= NAME_SEPARATOR . $item_info->$item_field_name;
				}
			}
		}
	}

	/**
	 * @param string $search
	 * @param array $filters
	 * @param bool $unique
	 * @param int $limit
	 * @return array
	 */
	public function get_search_suggestions(string $search, array $filters = ['is_deleted' => false, 'search_custom' => false], bool $unique = false, int $limit = 25): array
	{
		$suggestions = [];
		$non_kit = [ITEM, ITEM_AMOUNT_ENTRY];

		$builder = $this->db->table('items');
		$builder->select($this->get_search_suggestion_format('item_id, name, pack_name'));
		$builder->where('deleted', $filters['is_deleted']);
		$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$builder->like('name', $search);//TODO: this and the next 11 lines are duplicated directly below.  We should extract a method here.
		$builder->orderBy('name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
		}

		$builder = $this->db->table('items');
		$builder->select($this->get_search_suggestion_format('item_id, item_number, pack_name'));
		$builder->where('deleted', $filters['is_deleted']);
		$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$builder->like('item_number', $search);
		$builder->orderBy('item_number', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
		}

		if(!$unique)
		{
			//Search by category
			$builder = $this->db->table('items');
			$builder->select('category');
			$builder->where('deleted', $filters['is_deleted']);
			$builder->distinct();    //TODO: duplicate code.  Refactor method.
			$builder->like('category', $search);
			$builder->orderBy('category', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = ['label' => $row->category];
			}

			$builder = $this->db->table('suppliers');

			//Search by supplier
			$builder->select('company_name');
			$builder->like('company_name', $search);

			// restrict to non deleted companies only if is_deleted is false
			$builder->where('deleted', $filters['is_deleted']);
			$builder->distinct();
			$builder->orderBy('company_name', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = ['label' => $row->company_name];
			}

			//Search by description
			$builder = $this->db->table('items');
			$builder->select($this->get_search_suggestion_format('item_id, name, pack_name, description'));
			$builder->where('deleted', $filters['is_deleted']);
			$builder->like('description', $search);    //TODO: duplicate code, refactor method.
			$builder->orderBy('description', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$entry = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];

				if(!array_walk($suggestions, function ($value, $label) use ($entry)
				{
					return $entry['label'] != $label;
				}))
				{
					$suggestions[] = $entry;
				}
			}

			//Search in attributes
			if($filters['search_custom'] !== false)
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
					$suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
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


	/**
	 * @param string $search
	 * @param array $filters
	 * @param bool $unique
	 * @param int $limit
	 * @return array
	 */
	public function get_stock_search_suggestions(string $search, array $filters = ['is_deleted' => false, 'search_custom' => false], bool $unique = false, int $limit = 25): array
	{
		$suggestions = [];
		$non_kit = [ITEM, ITEM_AMOUNT_ENTRY];

		$builder = $this->db->table('items');
		$builder->select($this->get_search_suggestion_format('item_id, name, pack_name'));
		$builder->where('deleted', $filters['is_deleted']);
		$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
		$builder->where('stock_type', '0'); // stocked items only
		$builder->like('name', $search);
		$builder->orderBy('name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
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
			$suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
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
				$suggestions[] = ['label' => $row->category];
			}

			//Search by supplier
			$builder = $this->db->table('suppliers');
			$builder->select('company_name');
			$builder->like('company_name', $search);

			// restrict to non deleted companies only if is_deleted is false
			$builder->where('deleted', $filters['is_deleted']);
			$builder->distinct();
			$builder->orderBy('company_name', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = ['label' => $row->company_name];
			}

			//Search by description
			$builder = $this->db->table('items');
			$builder->select($this->get_search_suggestion_format('item_id, name, pack_name, description'));
			$builder->where('deleted', $filters['is_deleted']);
			$builder->whereIn('item_type', $non_kit); // standard, exclude kit items since kits will be picked up later
			$builder->where('stock_type', '0'); // stocked items only
			$builder->like('description', $search);    //TODO: duplicated code, refactor method.
			$builder->orderBy('description', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$entry = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
				if(!array_walk($suggestions, function ($value, $label) use ($entry)
				{
					return $entry['label'] != $label;
				}))
				{
					$suggestions[] = $entry;
				}
			}

			//Search by custom fields
			if($filters['search_custom'] !== false)    //TODO: duplicated code.  We should refactor out a method... this can be replaced with `if($filters['search_custom']`... no need for the double negative
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
					$suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
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

	/**
	 * @param string $search
	 * @param array $filters
	 * @param bool $unique
	 * @param int $limit
	 * @return array
	 */
	public function get_kit_search_suggestions(string $search, array $filters = ['is_deleted' => false, 'search_custom' => false], bool $unique = false, int $limit = 25): array
	{
		$suggestions = [];
		$non_kit = [ITEM, ITEM_AMOUNT_ENTRY];    //TODO: This variable is never used.

		$builder = $this->db->table('items');
		$builder->select('item_id, name');
		$builder->where('deleted', $filters['is_deleted']);
		$builder->where('item_type', ITEM_KIT);
		$builder->like('name', $search);
		$builder->orderBy('name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = ['value' => $row->item_id, 'label' => $row->name];
		}

		$builder = $this->db->table('items');
		$builder->select('item_id, item_number');
		$builder->where('deleted', $filters['is_deleted']);
		$builder->like('item_number', $search);
		$builder->where('item_type', ITEM_KIT);
		$builder->orderBy('item_number', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = ['value' => $row->item_id, 'label' => $row->item_number];
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
				$suggestions[] = ['label' => $row->category];
			}

			//Search by supplier
			$builder = $this->db->table('suppliers');
			$builder->select('company_name');
			$builder->like('company_name', $search);

			// restrict to non deleted companies only if is_deleted is false
			$builder->where('deleted', $filters['is_deleted']);
			$builder->distinct();
			$builder->orderBy('company_name', 'asc');

			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = ['label' => $row->company_name];
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
				$entry = ['value' => $row->item_id, 'label' => $row->name];
				if(!array_walk($suggestions, function ($value, $label) use ($entry)
				{
					return $entry['label'] != $label;
				}))
				{
					$suggestions[] = $entry;
				}
			}

			//Search in attributes
			if($filters['search_custom'] !== false)    //TODO: Duplicate code... same as above... no double negatives
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
					$suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
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

	/**
	 * @param string $search
	 * @return array
	 */
	public function get_low_sell_suggestions(string $search): array
	{
		$suggestions = [];

		$builder = $this->db->table('items');
		$builder->select($this->get_search_suggestion_format('item_id, pack_name'));
		$builder->where('deleted', '0');
		$builder->where('stock_type', '0'); // stocked items only	//TODO: '0' should be replaced with a constant.
		$builder->like('name', $search);
		$builder->orderBy('name', 'asc');

		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
		}

		return $suggestions;
	}

	/**
	 * @param string $search
	 * @return array
	 */
	public function get_category_suggestions(string $search): array
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
			$suggestions[] = ['label' => $row->category];
		}

		return $suggestions;
	}

	/**
	 * @param string $search
	 * @return array
	 */
	public function get_location_suggestions(string $search): array
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
			$suggestions[] = ['label' => $row->location];
		}

		return $suggestions;
	}

	/**
	 * @return ResultInterface|false|string
	 */
	public function get_categories()    //TODO: This function is never called in the code.
	{
		$builder = $this->db->table('items');
		$builder->select('category');
		$builder->where('deleted', 0);
		$builder->distinct();
		$builder->orderBy('category', 'asc');

		return $builder->get();
	}

	/**
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
	public function change_cost_price(int $item_id, float $items_received, float $new_price, float $old_price = null): bool
	{
		if($old_price === null)
		{
			$item_info = $this->get_info($item_id);
			$old_price = $item_info->cost_price;
		}

		$builder = $this->db->table('item_quantities');
		$builder->selectSum('quantity');
		$builder->where('item_id', $item_id);
		$builder->join('stock_locations', 'stock_locations.location_id=item_quantities.location_id');
		$builder->where('stock_locations.deleted', 0);
		$old_total_quantity = $builder->get()->getRow()->quantity;

		$total_quantity = $old_total_quantity + $items_received;
		$average_price = bcdiv(bcadd(bcmul((string)$items_received, (string)$new_price), bcmul((string)$old_total_quantity, (string)$old_price)), (string)$total_quantity);

		$data = ['cost_price' => $average_price];

		return $this->save_value($data, $item_id);
	}

	/**
	 * @param int $item_id
	 * @param string $item_number
	 * @return void
	 */
	public function update_item_number(int $item_id, string $item_number): void
	{
		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);
		$builder->update(['item_number' => $item_number]);    //TODO: this function should probably return the result of update() and add ": bool" to the function signature
	}

	/**
	 * @param int $item_id
	 * @param string $item_name
	 * @return void
	 */
	public function update_item_name(int $item_id, string $item_name): void    //TODO: this function should probably return the result of update() and add ": bool" to the function signature
	{
		$builder = $this->db->table('items');
		$builder->where('item_id', $item_id);
		$builder->update(['name' => $item_name]);
	}

	/**
	 * @param int $item_id
	 * @param string $item_description
	 * @return void
	 */
	public function update_item_description(int $item_id, string $item_description): void    //TODO: this function should probably return the result of update() and add ": bool" to the function signature
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
	public function get_item_name(string $as_name = null): string
	{
		$config = config(OSPOS::class)->settings;

		if($as_name == null)    //TODO: Replace with ternary notation
		{
			$as_name = '';
		}
		else
		{
			$as_name = ' AS ' . $as_name;
		}

		if($config['multi_pack_enabled'])    //TODO: Replace with ternary notation
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
