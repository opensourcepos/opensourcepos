<?php

namespace App\Models;

use CodeIgniter\Database\RawSql;
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

    public const ALLOWED_SUGGESTIONS_COLUMNS = ['name', 'item_number', 'description', 'cost_price', 'unit_price'];
    public const ALLOWED_SUGGESTIONS_COLUMNS_WITH_EMPTY = ['', 'name', 'item_number', 'description', 'cost_price', 'unit_price'];

    /**
     * Sentinel posted by the bulk edit form to clear supplier_id, since an empty
     * value there means "leave the column alone". Non-numeric so it can never
     * collide with a suppliers.person_id.
     */
    public const CLEAR_SUPPLIER_OPTION = 'NONE';

    public const ALLOWED_BULK_EDIT_FIELDS = [
        'name',
        'category',
        'supplier_id',
        'cost_price',
        'unit_price',
        'reorder_level',
        'description',
        'allow_alt_description',
        'is_serialized'
    ];

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
    public function exists(string $item_id, bool $ignore_deleted = false, bool $deleted = false): bool
    {
        $builder = $this->db->table('items');
        $builder->groupStart();
        $builder->where('item_id', $item_id);
        $builder->orWhere('item_number', $item_id);
        $builder->groupEnd();

        if (!$ignore_deleted) {
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

        if ($config['allow_duplicate_barcodes']) {
            return false;
        }

        $builder = $this->db->table('items');
        $builder->where('item_number', $item_number);
        $builder->where('deleted !=', 1);
        $builder->where('item_id !=', intval($item_id));

        // Check if $item_id is a number and not a string starting with 0
        // because cases like 00012345 will be seen as a number where it is a barcode
        if (ctype_digit($item_id) && !str_starts_with($item_id, '0')) {
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
    public function get_tax_category_usage(int $tax_category_id): int    // TODO: This function is never called in the code.
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
     * Parse search string for attribute-specific queries
     * Supports syntax like "color: blue size: large" or "color:blue AND size:large"
     *
     * @param string $search The raw search string
     * @return array{terms: array, attributes: array} Parsed terms and attribute queries
     */
    public function parseAttributeSearch(string $search): array
    {
        $result = [
            'terms' => [],
            'attributes' => []
        ];

        if ($search === '') {
            return $result;
        }

        $pattern = '/([[:alpha:]][[:alnum:] _-]*?)\s*:\s*([^\s,]+)(?:\s+(?:AND|OR)\s+)?/iu';
        $remaining = preg_replace($pattern, '', $search);

        if (preg_match_all($pattern, $search, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attrName = strtolower(trim($match[1]));
                $attrValue = trim($match[2]);
                $result['attributes'][$attrName][] = $attrValue;
            }
        }

        $remaining = trim(preg_replace('/\s+/', ' ', $remaining));
        if ($remaining !== '') {
            $result['terms'][] = $remaining;
        }

        return $result;
    }

    /**
     * Get attribute definition ID from column name for sorting
     *
     * @param string $sortColumn The sort column name
     * @return int|null The definition ID or null if not an attribute column
     */
    private function getAttributeSortDefinitionId(string $sortColumn): ?int
    {
        if (!ctype_digit($sortColumn)) {
            return null;
        }

        return (int) $sortColumn;
    }

    /**
     * Left-joins the attribute tables needed to sort by a given attribute definition,
     * and applies the order-by using the type-appropriate value column.
     */
    private function applyAttributeSort($builder, int $sortDefinitionId, string $order): void
    {
        $sortAlias = "sort_attr_{$sortDefinitionId}";
        $builder->join("attribute_links AS {$sortAlias}", "{$sortAlias}.item_id = items.item_id AND {$sortAlias}.definition_id = {$sortDefinitionId} AND {$sortAlias}.sale_id IS NULL AND {$sortAlias}.receiving_id IS NULL", 'left');
        $builder->join("attribute_values AS {$sortAlias}_val", "{$sortAlias}_val.attribute_id = {$sortAlias}.attribute_id", 'left');

        $attribute = model(Attribute::class);
        $definitionInfo = $attribute->getDefinitionsByFlags(Attribute::SHOW_IN_ITEMS, true);
        $sortColumn = "{$sortAlias}_val.attribute_value";

        if (isset($definitionInfo[$sortDefinitionId])) {
            $defType = is_array($definitionInfo[$sortDefinitionId]) ? ($definitionInfo[$sortDefinitionId]['type'] ?? TEXT) : TEXT;
            if ($defType === DECIMAL) {
                $sortColumn = "{$sortAlias}_val.attribute_decimal";
            } elseif ($defType === DATE) {
                $sortColumn = "{$sortAlias}_val.attribute_date";
            }
        }

        $builder->orderBy($sortColumn, $order);
    }

    /**
     * Perform a search on items
     *
     * Resolves qualifying item_ids first (Phase A), then joins the expensive display-only
     * tables scoped to just those ids (Phase B) instead of the whole matching set.
     */
    public function search(string $search, array $filters, ?int $rows = 0, ?int $limitFrom = 0, ?string $sort = 'items.name', ?string $order = 'asc', ?bool $countOnly = false)
    {
        // Set default values
        if ($rows == null) {
            $rows = 0;
        }
        if ($limitFrom == null) {
            $limitFrom = 0;
        }
        if ($sort == null) {
            $sort = 'items.name';
        }
        if ($order == null) {
            $order = 'asc';
        }
        if ($countOnly == null) {
            $countOnly = false;
        }

        $config = config(OSPOS::class)->settings;

        $dateFormatEnabled = empty($config['date_or_time_format']);
        $hasTransDateRange = !empty($filters['start_date']) && !empty($filters['end_date']);
        $rangeStart = $hasTransDateRange ? ($dateFormatEnabled ? $filters['start_date'] : rawurldecode($filters['start_date'])) : null;
        $rangeEnd = $hasTransDateRange ? ($dateFormatEnabled ? $filters['end_date'] : rawurldecode($filters['end_date'])) : null;
        $applyTransDateRange = function ($builder) use ($hasTransDateRange, $dateFormatEnabled, $rangeStart, $rangeEnd) {
            if (!$hasTransDateRange) {
                return;
            }
            $column = $dateFormatEnabled ? 'DATE_FORMAT(trans_date, "%Y-%m-%d")' : 'trans_date';
            $builder->groupStart();
            $builder->where("$column >=", $rangeStart);
            $builder->where("$column <=", $rangeEnd);
            $builder->groupEnd();
        };

        $definitionIds = array_map('intval', $filters['definition_ids']);
        $attributesEnabled = count($filters['definition_ids']) > 0;
        $customAttributeSearch = $attributesEnabled && $filters['search_custom'] && !empty($search);

        if ($attributesEnabled) {
            $this->db->simpleQuery('SET SESSION group_concat_max_len=49152');
        }

        $idBuilder = $this->db->table('items AS items');

        if ($customAttributeSearch) {
            // Matching is per attribute row (WHERE, pre-GROUP BY), not against a GROUP_CONCAT
            // blob (HAVING, post-GROUP BY), so a match can't bleed across definitions.
            $idBuilder->select('items.item_id');
            $idBuilder->join('attribute_links', 'attribute_links.item_id = items.item_id AND attribute_links.receiving_id IS NULL AND attribute_links.sale_id IS NULL AND definition_id IN (' . implode(',', $definitionIds) . ')', 'left');
            $idBuilder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id', 'left');
        } else {
            $idBuilder->select('items.item_id');
        }

        $idBuilder->join('suppliers AS suppliers', 'suppliers.person_id = items.supplier_id', 'left');
        $idBuilder->join('inventory AS inventory', 'inventory.trans_items = items.item_id');

        if ($filters['stock_location_id'] > -1) {
            $idBuilder->join('item_quantities AS item_quantities', 'item_quantities.item_id = items.item_id');
            $idBuilder->where('location_id', $filters['stock_location_id']);
        }

        $applyTransDateRange($idBuilder);

        if (!empty($search)) {
            if ($customAttributeSearch) {
                $format = $this->db->escape(dateformat_mysql());
                $idBuilder->groupStart();
                $idBuilder->like('attribute_value', $search);
                $idBuilder->orLike(new RawSql("DATE_FORMAT(attribute_date, $format)"), $search);
                $idBuilder->orLike('attribute_decimal', $search);
                $idBuilder->groupEnd();
            } else {
                $idBuilder->groupStart();
                $idBuilder->like('name', $search);
                $idBuilder->orLike('item_number', $search);
                $idBuilder->orLike('items.item_id', $search);
                $idBuilder->orLike('company_name', $search);
                $idBuilder->orLike('items.category', $search);
                $idBuilder->groupEnd();
            }
        }

        $idBuilder->where('items.deleted', $filters['is_deleted']);

        if ($filters['empty_upc']) {
            $idBuilder->where('item_number', null);
        }
        if ($filters['low_inventory'] && $filters['stock_location_id'] > -1) {
            $idBuilder->where('item_quantities.quantity <=', new RawSql('items.reorder_level'));
        }
        if ($filters['is_serialized']) {
            $idBuilder->where('is_serialized', 1);
        }
        if ($filters['no_description']) {
            $idBuilder->where('items.description', '');
        }
        if ($filters['temporary']) {
            $idBuilder->where('items.item_type', ITEM_TEMP);
        } else {
            $nonTemp = [ITEM, ITEM_KIT, ITEM_AMOUNT_ENTRY];
            $idBuilder->whereIn('items.item_type', $nonTemp);
        }

        // Avoid duplicated entries with same name because of inventory reporting multiple changes on the same item in the same date range
        $idBuilder->groupBy('items.item_id');

        // get_found_rows case
        if ($countOnly) {
            return $idBuilder->countAllResults();
        }

        // Order by name of item by default
        $sortDefinitionId = $this->getAttributeSortDefinitionId($sort);
        if ($sort === 'quantity' && $filters['stock_location_id'] <= -1) {
            $itemQuantitiesTable = $this->db->prefixTable('item_quantities');
            $idBuilder->join(
                "(SELECT item_id, SUM(quantity) AS total_quantity FROM $itemQuantitiesTable GROUP BY item_id) AS item_quantity_totals",
                'item_quantity_totals.item_id = items.item_id',
                'left'
            );
            $idBuilder->orderBy('item_quantity_totals.total_quantity', $order);
        } elseif ($sortDefinitionId !== null) {
            $this->applyAttributeSort($idBuilder, $sortDefinitionId, $order);
        } else {
            $idBuilder->orderBy($sort, $order);
        }

        if ($rows > 0) {
            $idBuilder->limit($rows, $limitFrom);
        }

        $itemIds = array_column($idBuilder->get()->getResultArray(), 'item_id');

        if (empty($itemIds)) {
            return $this->db->table('items AS items')->where('1 = 0')->get();
        }

        $builder = $this->db->table('items AS items');

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
        $builder->select('MAX(suppliers.deleted) AS supplier_deleted');

        $builder->select('MAX(inventory.trans_id) AS trans_id');
        $builder->select('MAX(inventory.trans_items) AS trans_items');
        $builder->select('MAX(inventory.trans_user) AS trans_user');
        $builder->select('MAX(inventory.trans_date) AS trans_date');
        $builder->select('MAX(inventory.trans_comment) AS trans_comment');
        $builder->select('MAX(inventory.trans_location) AS trans_location');
        $builder->select('MAX(inventory.trans_inventory) AS trans_inventory');

        $sortByQuantityAllLocations = $sort === 'quantity' && $filters['stock_location_id'] <= -1;

        if ($filters['stock_location_id'] > -1) {
            $builder->select('MAX(item_quantities.item_id) AS qty_item_id');
            $builder->select('MAX(item_quantities.location_id) AS location_id');
            $builder->select('MAX(item_quantities.quantity) AS quantity');
        } elseif ($sortByQuantityAllLocations) {
            $builder->select('item_quantity_totals.total_quantity AS quantity');
        }

        $builder->join('suppliers AS suppliers', 'suppliers.person_id = items.supplier_id', 'left');
        $builder->join('inventory AS inventory', 'inventory.trans_items = items.item_id');

        if ($filters['stock_location_id'] > -1) {
            $builder->join('item_quantities AS item_quantities', 'item_quantities.item_id = items.item_id');
            $builder->where('location_id', $filters['stock_location_id']);
        } elseif ($sortByQuantityAllLocations) {
            $itemQuantitiesTable = $this->db->prefixTable('item_quantities');
            $builder->join(
                "(SELECT item_id, SUM(quantity) AS total_quantity FROM $itemQuantitiesTable GROUP BY item_id) AS item_quantity_totals",
                'item_quantity_totals.item_id = items.item_id',
                'left'
            );
        }

        $applyTransDateRange($builder);

        if ($attributesEnabled) {
            $format = $this->db->escape(dateformat_mysql());
            $builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_value) ORDER BY definition_id SEPARATOR \'|\') AS attribute_values');
            $builder->select("GROUP_CONCAT(DISTINCT CONCAT_WS('_', definition_id, DATE_FORMAT(attribute_date, $format)) SEPARATOR '|') AS attribute_dtvalues");
            $builder->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_decimal) SEPARATOR \'|\') AS attribute_dvalues');
            $builder->join('attribute_links', 'attribute_links.item_id = items.item_id AND attribute_links.receiving_id IS NULL AND attribute_links.sale_id IS NULL AND definition_id IN (' . implode(',', $definitionIds) . ')', 'left');
            $builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id', 'left');
        }

        $builder->whereIn('items.item_id', $itemIds);

        $builder->groupBy('items.item_id');

        // Re-apply order: WHERE...IN + GROUP BY do not preserve Phase A's row order
        if ($sortByQuantityAllLocations) {
            $builder->orderBy('item_quantity_totals.total_quantity', $order);
        } elseif ($sortDefinitionId !== null) {
            $this->applyAttributeSort($builder, $sortDefinitionId, $order);
        } else {
            $builder->orderBy($sort, $order);
        }

        return $builder->get();
    }

    /**
     * Returns all the items
     */
    public function get_all(int $stock_location_id = NEW_ENTRY, int $rows = 0, int $limit_from = 0): ResultInterface
    {
        $builder = $this->db->table('items');

        if ($stock_location_id > -1) {
            $builder->join('item_quantities', 'item_quantities.item_id = items.item_id');
            $builder->where('location_id', $stock_location_id);
        }

        $builder->where('items.deleted', 0);

        // Order by name of item
        $builder->orderBy('items.name', 'asc');

        if ($rows > 0) {
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

        if ($query->getNumRows() == 1) {
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
        foreach ($this->db->getFieldData($table_name) as $field) {
            $field_name = $field->name;

            if (in_array($field->type, ['int', 'tinyint', 'decimal'])) {
                $empty_obj->$field_name = ($field->primary_key == 1) ? NEW_ENTRY : 0;
            } else {
                $empty_obj->$field_name = null;
            }
        }

        return $empty_obj;
    }

    /**
     * Gets information about a particular item by item id or number
     */
    public function get_info_by_id_or_number(string $item_id, bool $include_deleted = true): stdClass|string
    {
        $builder = $this->db->table('items');
        $builder->groupStart();
        $builder->where('items.item_number', $item_id);

        // Check if $item_id is a number and not a string starting with 0
        // because cases like 00012345 will be seen as a number where it is a barcode
        if (ctype_digit(strval($item_id)) && !str_starts_with($item_id, '0')) {
            $builder->orWhere('items.item_id', $item_id);
        }

        $builder->groupEnd();

        if (!$include_deleted) {
            $builder->where('items.deleted', 0);
        }

        // Limit to only 1 so there is a result in case two are returned
        // due to barcode and item_id clash
        $builder->limit(1);

        $query = $builder->get();

        if ($query->getNumRows() == 1) {
            return $query->getRow();
        }

        return '';
    }

    /**
     * Get an item id given an item number
     */
    public function get_item_id(string $item_number, bool $ignore_deleted = false, bool $deleted = false): bool|int
    {
        $builder = $this->db->table('items');
        $builder->groupStart();
        $builder->where('item_number', $item_number);
        $builder->orWhere('item_id', $item_number);
        $builder->groupEnd();

        if (!$ignore_deleted) {
            $builder->where('items.deleted', $deleted);
        }

        $query = $builder->get();

        if ($query->getNumRows() == 1) {    // TODO: ===
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
    public function save_value(array &$item_data, int $item_id = NEW_ENTRY): bool    // TODO: need to bring this in line with parent or change the name
    {
        $builder = $this->db->table('items');

        if ($item_id < 1 || !$this->exists($item_id, true)) {
            if ($builder->insert($item_data)) {
                $item_data['item_id'] = (int)$this->db->insertID();
                if ($item_id < 1) {
                    $builder = $this->db->table('items');
                    $builder->where('item_id', $item_data['item_id']);
                    $builder->update(['low_sell_item_id' => $item_data['item_id']]);
                }

                return true;
            }

            return false;
        } else {
            $item_data['item_id'] = $item_id;
        }

        $builder = $this->db->table('items');
        $builder->where('item_id', $item_id);

        return $builder->update($item_data);
    }

    /**
     * Reduces raw bulk edit input to the columns that may be bulk updated.
     *
     * Keys outside ALLOWED_BULK_EDIT_FIELDS are dropped, and a field that is absent,
     * empty, or invalid for its column is left untouched so it keeps its current
     * value. Prices and quantities are locale-parsed the same way postSave() does,
     * booleans must be 0/1, and supplier_id must be numeric. supplier_id is
     * nullable, so CLEAR_SUPPLIER_OPTION is how the form asks for it to be cleared.
     */
    public static function filterBulkEditFields(array $input): array
    {
        $itemData = [];

        foreach (self::ALLOWED_BULK_EDIT_FIELDS as $field) {
            $value = $input[$field] ?? null;

            if ($value === null || $value === '' || !is_scalar($value)) {
                continue;
            }

            if ($field === 'supplier_id') {
                if ($value === self::CLEAR_SUPPLIER_OPTION) {
                    $itemData[$field] = null;
                } elseif (ctype_digit((string)$value)) {
                    $itemData[$field] = (int)$value;
                }

                continue;
            }

            if ($field === 'cost_price' || $field === 'unit_price') {
                $value = parse_decimals((string)$value);
            } elseif ($field === 'reorder_level') {
                $value = parse_quantity((string)$value);
            } elseif ($field === 'allow_alt_description' || $field === 'is_serialized') {
                if (!in_array((string)$value, ['0', '1'], true)) {
                    continue;
                }
            }

            if ($value === false) {
                continue;
            }

            $itemData[$field] = $value;
        }

        return $itemData;
    }

    /**
     * Updates multiple items at once
     */
    public function updateMultiple(array $itemData, string $itemIds): bool
    {
        // Query Builder bypasses $allowedFields, so the whitelist is enforced here (GHSA-49mq-h2g4-grr9)
        $itemData = array_intersect_key($itemData, array_flip(self::ALLOWED_BULK_EDIT_FIELDS));

        if (empty($itemData)) {
            return false;
        }

        $builder = $this->db->table('items');
        $builder->whereIn('item_id', explode(':', $itemIds));

        return $builder->update($itemData);
    }

    /**
     * Deletes one item
     */
    public function delete($item_id = null, bool $purge = false): bool|int|string
    {
        $this->db->transStart();

        // Set to 0 quantities
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
        // Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->transStart();

        // Set to 0 quantities
        $item_quantity = model(Item_quantity::class);
        $item_quantity->reset_quantity_list($item_ids);

        $builder = $this->db->table('items');
        $builder->whereIn('item_id', $item_ids);
        $success = $builder->update(['deleted' => 1]);

        $inventory = model(Inventory::class);

        foreach ($item_ids as $item_id) {
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
    public function get_search_suggestion_format(?string $seed = null): string
    {
        $config = config(OSPOS::class)->settings;

        $suggestionsFirstColumn = $this->suggestionColumnIsAllowed($config['suggestions_first_column'])
            ? $config['suggestions_first_column']
            : 'name';
        $seed .= ',' . $suggestionsFirstColumn;

        if ($config['suggestions_second_column'] !== '' && $this->suggestionColumnIsAllowed($config['suggestions_second_column'])) {
            $seed .= ',' . $config['suggestions_second_column'];
        }

        if ($config['suggestions_third_column'] !== '' && $this->suggestionColumnIsAllowed($config['suggestions_third_column'])) {
            $seed .= ',' . $config['suggestions_third_column'];
        }

        return $seed;
    }

    /**
     * @param object $result_row
     * @return string
     */
    public function get_search_suggestion_label(object $result_row): string
    {
        $config = config(OSPOS::class)->settings;

        $label = '';
        $label1 = $this->suggestionColumnIsAllowed($config['suggestions_first_column'])
            ? $config['suggestions_first_column']
            : 'name';
        $label2 = $this->suggestionColumnIsAllowed($config['suggestions_second_column'])
            ? $config['suggestions_second_column']
            : '';
        $label3 = $this->suggestionColumnIsAllowed($config['suggestions_third_column'])
            ? $config['suggestions_third_column']
            : '';

        $this->format_result_numbers($result_row);

        // If multi_pack enabled then if "name" is part of the search suggestions then append pack
        if ($config['multi_pack_enabled']) {
            $this->append_label($label, $label1, $result_row);
            $this->append_label($label, $label2, $result_row);
            $this->append_label($label, $label3, $result_row);
        } else {
            $label = $result_row->$label1;

            if ($label2 !== '') {
                $label .= NAME_SEPARATOR . $result_row->$label2;
            }

            if ($label3 !== '') {
                $label .= NAME_SEPARATOR . $result_row->$label3;
            }
        }

        return $label;
    }

    /**
     * Validates if a column name is in the allowed suggestions columns.
     *
     * @param string $columnName
     * @return bool
     */
    private function suggestionColumnIsAllowed(string $columnName): bool
    {
        return in_array($columnName, self::ALLOWED_SUGGESTIONS_COLUMNS, true);
    }

    /**
     * Converts decimal money values to their correct locale format.
     *
     * @param object $result_row
     * @return void
     */
    private function format_result_numbers(object &$result_row): void
    {
        if (isset($result_row->cost_price)) {
            $result_row->cost_price = to_currency_no_money($result_row->cost_price);
        }
        if (isset($result_row->unit_price)) {
            $result_row->unit_price = to_currency_no_money($result_row->unit_price);
        }
    }

    /**
     * @param string $label
     * @param string $item_field_name
     * @param object $item_info
     * @return void
     */
    private function append_label(string &$label, string $item_field_name, object $item_info): void
    {
        if ($item_field_name !== '') {
            if ($label == '') {
                if ($item_field_name == 'name') {    // TODO: This needs to be replaced with Ternary notation if possible
                    $label .= implode(NAME_SEPARATOR, [$item_info->name, $item_info->pack_name]);    // TODO: no need for .= operator.  If it gets here then that means label is an empty string.
                } else {
                    $label .= $item_info->$item_field_name;
                }
            } else {
                if ($item_field_name == 'name') {
                    $label .= implode(NAME_SEPARATOR, ['', $item_info->name, $item_info->pack_name]);
                } else {
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
        $builder->whereIn('item_type', $non_kit); // Standard, exclude kit items since kits will be picked up later
        $builder->like('name', $search);    // TODO: this and the next 11 lines are duplicated directly below.  We should extract a method here.
        $builder->orderBy('name', 'asc');

        foreach ($builder->get()->getResult() as $row) {
            $suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
        }

        $builder = $this->db->table('items');
        $builder->select($this->get_search_suggestion_format('item_id, item_number, pack_name'));
        $builder->where('deleted', $filters['is_deleted']);
        $builder->whereIn('item_type', $non_kit); // Standard, exclude kit items since kits will be picked up later
        $builder->like('item_number', $search);
        $builder->orderBy('item_number', 'asc');

        foreach ($builder->get()->getResult() as $row) {
            $suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
        }

        if (!$unique) {
            // Search by category
            $builder = $this->db->table('items');
            $builder->select('category');
            $builder->where('deleted', $filters['is_deleted']);
            $builder->distinct();    // TODO: duplicate code.  Refactor method.
            $builder->like('category', $search);
            $builder->orderBy('category', 'asc');

            foreach ($builder->get()->getResult() as $row) {
                $suggestions[] = ['label' => $row->category];
            }

            $builder = $this->db->table('suppliers');

            // Search by supplier
            $builder->select('company_name');
            $builder->like('company_name', $search);

            // Restrict to non deleted companies only if is_deleted is false
            $builder->where('deleted', $filters['is_deleted']);
            $builder->distinct();
            $builder->orderBy('company_name', 'asc');

            foreach ($builder->get()->getResult() as $row) {
                $suggestions[] = ['label' => $row->company_name];
            }

            // Search by description
            $builder = $this->db->table('items');
            $builder->select($this->get_search_suggestion_format('item_id, name, pack_name, description'));
            $builder->where('deleted', $filters['is_deleted']);
            $builder->like('description', $search);    // TODO: duplicate code, refactor method.
            $builder->orderBy('description', 'asc');

            foreach ($builder->get()->getResult() as $row) {
                $entry = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];

                if (!array_walk($suggestions, function ($value, $label) use ($entry) {
                    return $entry['label'] != $label;
                })) {
                    $suggestions[] = $entry;
                }
            }

            // Search in attributes
            if ($filters['search_custom'] !== false) {
                $builder = $this->db->table('attribute_links');
                $builder->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id');
                $builder->join('attribute_definitions', 'attribute_definitions.definition_id = attribute_links.definition_id');
                $builder->like('attribute_value', $search);
                $builder->where('definition_type', TEXT);
                $builder->where('deleted', $filters['is_deleted']);
                $builder->whereIn('item_type', $non_kit); // Standard, exclude kit items since kits will be picked up later

                foreach ($builder->get()->getResult() as $row) {
                    $suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
                }
            }
        }

        // Only return $limit suggestions
        if (count($suggestions) > $limit) {
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
        $builder->whereIn('item_type', $non_kit); // Standard, exclude kit items since kits will be picked up later
        $builder->where('stock_type', '0'); // Stocked items only
        $builder->like('name', $search);
        $builder->orderBy('name', 'asc');

        foreach ($builder->get()->getResult() as $row) {
            $suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
        }

        $builder = $this->db->table('items');
        $builder->select($this->get_search_suggestion_format('item_id, item_number, pack_name'));
        $builder->where('deleted', $filters['is_deleted']);
        $builder->whereIn('item_type', $non_kit); // Standard, exclude kit items since kits will be picked up later
        $builder->where('stock_type', '0'); // Stocked items only
        $builder->like('item_number', $search);
        $builder->orderBy('item_number', 'asc');

        foreach ($builder->get()->getResult() as $row) {
            $suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
        }

        if (!$unique) {
            // Search by category
            $builder = $this->db->table('items');
            $builder->select('category');
            $builder->where('deleted', $filters['is_deleted']);
            $builder->whereIn('item_type', $non_kit); // Standard, exclude kit items since kits will be picked up later
            $builder->where('stock_type', '0'); // Stocked items only
            $builder->distinct();
            $builder->like('category', $search);
            $builder->orderBy('category', 'asc');

            foreach ($builder->get()->getResult() as $row) {
                $suggestions[] = ['label' => $row->category];
            }

            // Search by supplier
            $builder = $this->db->table('suppliers');
            $builder->select('company_name');
            $builder->like('company_name', $search);

            // Restrict to non deleted companies only if is_deleted is false
            $builder->where('deleted', $filters['is_deleted']);
            $builder->distinct();
            $builder->orderBy('company_name', 'asc');

            foreach ($builder->get()->getResult() as $row) {
                $suggestions[] = ['label' => $row->company_name];
            }

            // Search by description
            $builder = $this->db->table('items');
            $builder->select($this->get_search_suggestion_format('item_id, name, pack_name, description'));
            $builder->where('deleted', $filters['is_deleted']);
            $builder->whereIn('item_type', $non_kit); // Standard, exclude kit items since kits will be picked up later
            $builder->where('stock_type', '0'); // Stocked items only
            $builder->like('description', $search);    // TODO: duplicated code, refactor method.
            $builder->orderBy('description', 'asc');

            foreach ($builder->get()->getResult() as $row) {
                $entry = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
                if (!array_walk($suggestions, function ($value, $label) use ($entry) {
                    return $entry['label'] != $label;
                })) {
                    $suggestions[] = $entry;
                }
            }

            // Search by custom fields
            if ($filters['search_custom'] !== false) {    // TODO: duplicated code.  We should refactor out a method... this can be replaced with `if ($filters['search_custom']`... no need for the double negative
                $builder = $this->db->table('attribute_links');
                $builder->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id');
                $builder->join('attribute_definitions', 'attribute_definitions.definition_id = attribute_links.definition_id');
                $builder->like('attribute_value', $search);
                $builder->where('definition_type', TEXT);
                $builder->where('stock_type', '0'); // Stocked items only
                $builder->where('deleted', $filters['is_deleted']);

                foreach ($builder->get()->getResult() as $row) {
                    $suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
                }
            }
        }

        // Only return $limit suggestions
        if (count($suggestions) > $limit) {
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
        $non_kit = [ITEM, ITEM_AMOUNT_ENTRY];    // TODO: This variable is never used.

        $builder = $this->db->table('items');
        $builder->select('item_id, name');
        $builder->where('deleted', $filters['is_deleted']);
        $builder->where('item_type', ITEM_KIT);
        $builder->like('name', $search);
        $builder->orderBy('name', 'asc');

        foreach ($builder->get()->getResult() as $row) {
            $suggestions[] = ['value' => $row->item_id, 'label' => $row->name];
        }

        $builder = $this->db->table('items');
        $builder->select('item_id, item_number');
        $builder->where('deleted', $filters['is_deleted']);
        $builder->like('item_number', $search);
        $builder->where('item_type', ITEM_KIT);
        $builder->orderBy('item_number', 'asc');

        foreach ($builder->get()->getResult() as $row) {
            $suggestions[] = ['value' => $row->item_id, 'label' => $row->item_number];
        }

        if (!$unique) {
            // Search by category
            $builder = $this->db->table('items');
            $builder->select('category');
            $builder->where('deleted', $filters['is_deleted']);
            $builder->where('item_type', ITEM_KIT);
            $builder->distinct();    // TODO: duplicated code, refactor method.
            $builder->like('category', $search);
            $builder->orderBy('category', 'asc');

            foreach ($builder->get()->getResult() as $row) {
                $suggestions[] = ['label' => $row->category];
            }

            // Search by supplier
            $builder = $this->db->table('suppliers');
            $builder->select('company_name');
            $builder->like('company_name', $search);

            // Restrict to non deleted companies only if is_deleted is false
            $builder->where('deleted', $filters['is_deleted']);
            $builder->distinct();
            $builder->orderBy('company_name', 'asc');

            foreach ($builder->get()->getResult() as $row) {
                $suggestions[] = ['label' => $row->company_name];
            }

            // Search by description
            $builder = $this->db->table('items');
            $builder->select('item_id, name, description');
            $builder->where('deleted', $filters['is_deleted']);
            $builder->where('item_type', ITEM_KIT);
            $builder->like('description', $search);
            $builder->orderBy('description', 'asc');

            foreach ($builder->get()->getResult() as $row) {
                $entry = ['value' => $row->item_id, 'label' => $row->name];
                if (!array_walk($suggestions, function ($value, $label) use ($entry) {
                    return $entry['label'] != $label;
                })) {
                    $suggestions[] = $entry;
                }
            }

            // Search in attributes
            if ($filters['search_custom'] !== false) {    // TODO: Duplicate code... same as above... no double negatives
                $builder = $this->db->table('attribute_links');
                $builder->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id');
                $builder->join('attribute_definitions', 'attribute_definitions.definition_id = attribute_links.definition_id');
                $builder->like('attribute_value', $search);
                $builder->where('definition_type', TEXT);
                $builder->where('stock_type', '0'); // Stocked items only
                $builder->where('deleted', $filters['is_deleted']);

                foreach ($builder->get()->getResult() as $row) {
                    $suggestions[] = ['value' => $row->item_id, 'label' => $this->get_search_suggestion_label($row)];
                }
            }
        }

        // Only return $limit suggestions
        if (count($suggestions) > $limit) {
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
        $builder->where('stock_type', '0'); // Stocked items only    // TODO: '0' should be replaced with a constant.
        $builder->like('name', $search);
        $builder->orderBy('name', 'asc');

        foreach ($builder->get()->getResult() as $row) {
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

        foreach ($builder->get()->getResult() as $row) {
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
        foreach ($builder->get()->getResult() as $row) {
            $suggestions[] = ['label' => $row->location];
        }

        return $suggestions;
    }

    /**
     * @return ResultInterface|false|string
     */
    public function get_categories(): ResultInterface|bool    // TODO: This function is never called in the code.
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
    public function change_cost_price(int $item_id, float $items_received, float $new_price, ?float $old_price = null): bool
    {
        if ($old_price === null) {
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
        $builder->update(['item_number' => $item_number]);    // TODO: this function should probably return the result of update() and add ": bool" to the function signature
    }

    /**
     * @param int $item_id
     * @param string $item_name
     * @return void
     */
    public function update_item_name(int $item_id, string $item_name): void    // TODO: this function should probably return the result of update() and add ": bool" to the function signature
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
    public function update_item_description(int $item_id, string $item_description): void    // TODO: this function should probably return the result of update() and add ": bool" to the function signature
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
    public function get_item_name(?string $as_name = null): string
    {
        $config = config(OSPOS::class)->settings;

        if ($as_name == null) {    // TODO: Replace with ternary notation
            $as_name = '';
        } else {
            $as_name = ' AS ' . $as_name;
        }

        if ($config['multi_pack_enabled']) {    // TODO: Replace with ternary notation
            $item_name = "concat(items.name,'" . NAME_SEPARATOR . '\', items.pack_name)' . $as_name;
        } else {
            $item_name = 'items.name' . $as_name;
        }

        return $item_name;
    }
}
