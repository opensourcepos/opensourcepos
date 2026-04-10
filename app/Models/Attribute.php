<?php

namespace App\Models;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Query;
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use CodeIgniter\Database\RawSql;
use Config\OSPOS;
use DateTime;
use InvalidArgumentException;
use stdClass;
use ReflectionClass;

/**
 * Attribute class
 */
class Attribute extends Model
{
    protected $table = 'attribute_definitions';
    protected $primaryKey = 'definition_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [    // TODO: This model may not be well designed... The model accesses three different tables (attribute_definitions, attribute_links, attribute_values). Should that be more than one model? According to CodeIgniter, these are meant to model a single table https://codeigniter.com/user_guide/models/model.html#models
        'definition_name',
        'definition_type',
        'definition_unit',
        'definition_flags',
        'deleted',
        'attribute_id',
        'definition_id',
        'item_id',
        'sale_id',
        'receiving_id',
        'attribute_value',
        'attribute_date',
        'attribute_decimal'
    ];

    public const SHOW_IN_ITEMS = 1;    // TODO: These need to be moved to constants.php
    public const SHOW_IN_SALES = 2;
    public const SHOW_IN_RECEIVINGS = 4;
    public function deleteDropdownAttributeValue(string $attribute_value, int $definition_id): bool
    {
        $attribute_id = $this->getAttributeIdByValue($attribute_value);
        $this->deleteAttributeLinksByDefinitionIdAndAttributeId($definition_id, $attribute_id);

        //Delete attribute value if not linked other attributes
        $subQuery = $this->db->table('attribute_links');
        $subQuery->select('attribute_id');

        $builder = $this->db->table('attribute_values');
        $builder->where('attribute_value', $attribute_value);
        $builder->whereNotIn('attribute_id', $subQuery);

        return $builder->delete();
    }

    /**
     * @return array
     */
    public static function get_definition_flags(): array
    {
        $class = new ReflectionClass(__CLASS__);

        return array_flip($class->getConstants());
    }

    /**
     * Determines if a given definition_id is an attribute
     */
    public function exists(int $definition_id, bool $deleted = false): bool
    {
        $builder = $this->db->table('attribute_definitions');
        $builder->where('definition_id', $definition_id);
        $builder->where('deleted', $deleted);

        return ($builder->get()->getNumRows() === 1);
    }

    /**
     * Returns whether an attribute_link row exists given an item_id and optionally a definition_id
     *
     * @param int $item_id ID of the item to check for an associated attribute.
     * @param int|bool $definition_id Attribute definition ID to check.
     * @return bool returns true if at least one attribute_link exists or false if no attributes exist for that item and attribute.
     */
    public function attributeLinkExists(?int $item_id, int|bool $definition_id = false): bool
    {
        $builder = $this->db->table('attribute_links');
        $builder->where('item_id', $item_id);
        $builder->where('sale_id', null);
        $builder->where('receiving_id', null);

        if ($definition_id) {
            $builder->where('definition_id', $definition_id);
        } else {
            $builder->where('definition_id IS NOT NULL');
            $builder->where('attribute_id', null);
        }
        $results = $builder->countAllResults();
        return $results > 0;
    }

    /**
     * Determines if a given attribute_value exists in the attribute_values table and returns the attribute_id if it does
     *
     * @param float|string $attributeValue The value to search for in the attribute values table.
     * @param string $definitionType The definition type which will dictate which column is searched.
     * @return int|bool The attribute ID of the found row or false if no attribute value was found.
     */
    public function attributeValueExists(float|string $attributeValue, string $definitionType = TEXT): bool|int
    {
        $config = config(OSPOS::class)->settings;

        switch ($definitionType) {
            case DATE:
                $dataType = 'date';
                $attributeDateValue = DateTime::createFromFormat($config['dateformat'], $attributeValue);
                $attributeValue = $attributeDateValue ? $attributeDateValue->format('Y-m-d') : $attributeValue;
                break;
            case DECIMAL:
                $dataType = 'decimal';
                break;
            default:
                $dataType = 'value';
                break;
        }

        $builder = $this->db->table('attribute_values');
        $builder->select('attribute_id');
        $builder->where("attribute_$dataType", $attributeValue);
        $query = $builder->get();

        return $query->getNumRows() > 0
            ? $query->getRow()->attribute_id
            : false;
    }

    /**
     * Gets information about a particular attribute definition
     */
    public function getAttributeInfo(int $definition_id): object
    {
        $builder = $this->db->table('attribute_definitions AS definition');
        $builder->select('parent_definition.definition_name AS definition_group, definition.*');
        $builder->join('attribute_definitions AS parent_definition', 'parent_definition.definition_id = definition.definition_fk', 'left');
        $builder->where('definition.definition_id', $definition_id);

        $query = $builder->get();

        if ($query->getNumRows() === 1) {
            return $query->getRow();
        } else {
            // Get empty base parent object, as $item_id is NOT an item
            $item_obj = new stdClass();

            // Get all the fields from attribute_definitions table
            foreach ($this->db->getFieldNames('attribute_definitions') as $field) {
                $item_obj->$field = '';
            }

            return $item_obj;
        }
    }

    /**
     * Performs a search on attribute definitions
     */
    public function search(string $search, ?int $rows = 0, ?int $limit_from = 0, ?string $sort = 'definition.definition_name', ?string $order = 'asc'): ResultInterface
    {
        // Set default values
        if ($rows == null) $rows = 0;
        if ($limit_from == null) $limit_from = 0;
        if ($sort == null) $sort = 'definition.definition_name';
        if ($order == null) $order = 'asc';

        $builder = $this->db->table('attribute_definitions AS definition');
        $builder->select('parent_definition.definition_name AS definition_group, definition.*');
        $builder->join('attribute_definitions AS parent_definition', 'parent_definition.definition_id = definition.definition_fk', 'left');

        $builder->groupStart();
        $builder->like('definition.definition_name', $search);
        $builder->orLike('definition.definition_type', $search);
        $builder->groupEnd();

        $builder->where('definition.deleted', 0);
        $builder->orderBy($sort, $order);

        if ($rows > 0) {
            $builder->limit($rows, $limit_from);
        }

        return $builder->get();
    }

    /**
     * Gets all attributes connected to an item given the item_id
     *
     * @param int $item_id Item to retrieve attributes for.
     * @return array Attributes for the item.
     */
    public function get_attributes_by_item(int $item_id): array
    {
        $builder = $this->db->table('attribute_definitions');
        $builder->join('attribute_links', 'attribute_links.definition_id = attribute_definitions.definition_id');
        $builder->where('item_id', $item_id);
        $builder->where('sale_id', null);
        $builder->where('receiving_id', null);
        $builder->where('deleted', 0);
        $builder->orderBy('definition_name', 'ASC');

        $results = $builder->get()->getResultArray();

        return $this->to_array($results, 'definition_id');
    }

    /**
     * @param array|null $definition_ids
     * @return array
     */
    public function get_values_by_definitions(?array $definition_ids): array
    {
        if (count($definition_ids ?: [])) {
            $builder = $this->db->table('attribute_definitions');
            $builder->groupStart();
            $builder->whereIn('definition_fk', array_keys($definition_ids));
            $builder->orWhereIn('definition_id', array_keys($definition_ids));
            $builder->where('definition_type !=', GROUP);
            $builder->groupEnd();

            $builder->where('deleted', 0);

            $results = $builder->get()->getResultArray();

            return $this->to_array($results, 'definition_id');
        }

        return [];
    }

    /**
     * @param string $attribute_type
     * @param int $definition_id
     * @return array
     */
    public function get_definitions_by_type(string $attribute_type, int $definition_id = NO_DEFINITION_ID): array
    {
        $builder = $this->db->table('attribute_definitions');
        $builder->where('definition_type', $attribute_type);
        $builder->where('deleted', 0);
        $builder->where('definition_fk');

        if ($definition_id != CATEGORY_DEFINITION_ID) {
            $builder->where('definition_id <>', $definition_id);
        }

        $results = $builder->get()->getResultArray();

        return $this->to_array($results, 'definition_id', 'definition_name');
    }

    /**
     * @param int $definition_flags
     * @param bool $include_types If true, returns array with definition_id => ['name' => name, 'type' => type]
     * @return array
     */
    public function get_definitions_by_flags(int $definition_flags, bool $include_types = false): array
    {
        $builder = $this->db->table('attribute_definitions');
        $builder->where(new RawSql("definition_flags & $definition_flags"));    // TODO: we need to heed CI warnings to escape properly
        $builder->where('deleted', 0);
        $builder->where('definition_type <>', GROUP);
        $builder->orderBy('definition_id');

        $results = $builder->get()->getResultArray();

        if ($include_types) {
            $definitions = [];
            foreach ($results as $result) {
                $definitions[$result['definition_id']] = [
                    'name' => $result['definition_name'],
                    'type' => $result['definition_type']
                ];
            }
            return $definitions;
        }

        return $this->to_array($results, 'definition_id', 'definition_name');
    }

    /**
     * Returns an array of attribute definition names and IDs
     *
     * @param     boolean        $groups        If false does not return GROUP type attributes in the array
     * @return    array                    Array containing definition IDs, attribute names and -1 index with the local language '[SELECT]' line.
     */
    public function get_definition_names(bool $groups = true): array
    {
        $builder = $this->db->table('attribute_definitions');
        $builder->where('deleted', 0);
        $builder->orderBy('definition_name', 'ASC');

        if (!$groups) {
            $builder->whereNotIn('definition_type', GROUP);
        }

        $results = $builder->get()->getResultArray();
        $definition_name = [-1 => lang('Common.none_selected_text')];

        return $definition_name + $this->to_array($results, 'definition_id', 'definition_name');
    }

    /**
     * @param int $definition_id
     * @return array
     */
    public function get_definition_values(int $definition_id): array
    {
        $attribute_values = [];

        if ($definition_id > 0 || $definition_id == CATEGORY_DEFINITION_ID) {
            $builder = $this->db->table('attribute_links');
            $builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
            $builder->where('item_id', null);
            $builder->where('definition_id', $definition_id);
            $builder->orderBy('attribute_value', 'ASC');

            $results = $builder->get()->getResultArray();

            return $this->to_array($results, 'attribute_id', 'attribute_value');
        }

        return $attribute_values;
    }

    /**
     * @param array $results
     * @param string $key
     * @param string $value
     * @return array
     */
    private function to_array(array $results, string $key, string $value = ''): array
    {
        return array_column(array_map(function ($result) use ($key, $value) {
            return [$result[$key], empty($value) ? $result : $result[$value]];
        }, $results), 1, 0);
    }

    /**
     * Gets total of rows
     */
    public function get_total_rows(): int
    {
        $builder = $this->db->table('attribute_definitions');
        $builder->where('deleted', 0);

        return $builder->countAllResults();
    }

    /**
     * Get number of rows
     */
    public function get_found_rows(string $search): int
    {
        return $this->search($search)->getNumRows();
    }

    /**
     * @param int $definition_id
     * @param string $from
     * @param string $to
     * @return bool
     */
    private function check_data_validity(int $definition_id, string $from, string $to): bool
    {
        $success = false;

        if ($from === TEXT) {
            $success = true;

            $builder = $this->db->table('attribute_values');
            $builder->distinct()->select('attribute_value');
            $builder->join('attribute_links', 'attribute_values.attribute_id = attribute_links.attribute_id');
            $builder->where('definition_id', $definition_id);

            foreach ($builder->get()->getResult() as $attribute) {
                switch ($to) {
                    case DATE:
                        $success = valid_date($attribute->attribute_value);
                        break;
                    case DECIMAL:
                        $success = valid_decimal($attribute->attribute_value);
                        break;
                }

                if (!$success) {
                    $affected_items = $this->get_items_by_value($attribute->attribute_value, $definition_id);
                    foreach ($affected_items as $affected_item) {
                        $affected_items[] = $affected_item['item_id'];
                    }

                    log_message('error', "Attribute_value: '$attribute->attribute_value' cannot be converted to $to. Affected Items: " . implode(',', $affected_items));
                    unset($affected_items);
                }
            }
        }
        return $success;
    }

    /**
     * Returns all item_ids with a specific attribute_value and attribute_definition
     *
     * @param string $attribute_value Attribute value to be searched
     * @param int $definition_id ID of the specific attribute to return items for.
     * @return array Item_ids matching the given parameters
     */
    private function get_items_by_value(string $attribute_value, int $definition_id): array
    {
        $builder = $this->db->table('attribute_links');
        $builder->select('item_id');
        $builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
        $builder->where('definition_id', $definition_id);
        $builder->where('attribute_value', $attribute_value);

        return $builder->get()->getResultArray();
    }

    /**
     * Converts data in attribute_values and attribute_links tables associated with the conversion of one attribute type to another.
     *
     * @param int $definition_id
     * @param string $from_type
     * @param string $to_type
     * @return boolean
     */
    private function convert_definition_data(int $definition_id, string $from_type, string $to_type): bool
    {
        $success = false;

        if ($from_type === TEXT) {
            if (in_array($to_type, [DATE, DECIMAL], true)) {
                if ($this->check_data_validity($definition_id, $from_type, $to_type)) {
                    $attributes_to_convert = $this->get_attributes_by_definition($definition_id);
                    $success = $this->attribute_cleanup($attributes_to_convert, $definition_id, $to_type);
                }
            } elseif ($to_type === DROPDOWN) {
                $success = true;
            } elseif ($to_type === CHECKBOX) {    // TODO: duplicated code.
                $checkbox_attribute_values = $this->checkbox_attribute_values($definition_id);

                $this->db->transStart();

                $query = 'UPDATE ' . $this->db->prefixTable('attribute_links') . ' links ';
                $query .= 'JOIN ' . $this->db->prefixTable('attribute_values') . ' vals ';
                $query .= 'ON vals.attribute_id = links.attribute_id ';
                $query .= "SET links.attribute_id = IF((attribute_value IN('false','0','') OR (attribute_value IS NULL)), $checkbox_attribute_values[0], $checkbox_attribute_values[1]) ";
                $query .= 'WHERE definition_id = ' . $this->db->escape($definition_id);
                $success = $this->db->query($query);

                // TODO: In order to convert this query to QueryBuilder, CI needs to fix their issue with JOINs being ignored in UPDATE queries and ideally fix their issue with backticks and dbprefix not being prepended when SQL functions are used.
                // Replace the code above with the code below when it's fixed.
                // $db_prefix = $this->db->getPrefix();
                // $builder = $this->db->table('attribute_links');
                // $builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id', 'inner');
                // $builder->set('attribute_links.attribute_id', "IF((`$db_prefix" . "attribute_values`.`attribute_value` IN('false','0','') OR (`". $db_prefix ."attribute_values`.`attribute_value` IS NULL)), $checkbox_attribute_values[0], $checkbox_attribute_values[1])", false);
                // $builder->where('attribute_links.definition_id', $definition_id);
                // $success = $builder->update();

                $this->db->transComplete();
            }
        } elseif ($from_type === DROPDOWN) {
            if (in_array($to_type, [TEXT, CHECKBOX], true)) {
                if ($to_type === CHECKBOX) {    // TODO: Duplicated code.
                    $checkbox_attribute_values = $this->checkbox_attribute_values($definition_id);

                    $this->db->transStart();

                    $query = 'UPDATE ' . $this->db->prefixTable('attribute_links') . ' links ';
                    $query .= 'JOIN ' . $this->db->prefixTable('attribute_values') . ' vals ';
                    $query .= 'ON vals.attribute_id = links.attribute_id ';
                    $query .= "SET links.attribute_id = IF((attribute_value IN('false','0','') OR (attribute_value IS NULL)), $checkbox_attribute_values[0], $checkbox_attribute_values[1]) ";
                    $query .= 'WHERE definition_id = ' . $this->db->escape($definition_id);
                    $success = $this->db->query($query);

                    // TODO: Same issue here. Replace the code above with the code below when it's fixed.
                    // $builder = $this->db->table('attribute_links');
                    // $builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id', 'inner');
                    // $builder->set('attribute_links.attribute_id', "IF((attribute_value IN('false','0','') OR (attribute_value IS NULL)), $checkbox_attribute_values[0], $checkbox_attribute_values[1])", false);
                    // $builder->where('definition_id', $definition_id);
                    // $success = $builder->update();
                    //
                    // $this->db->transComplete();
                }
            }
        } else {
            $success = true;
        }

        $this->delete_orphaned_links($definition_id);
        $this->deleteOrphanedValues();
        return $success;
    }

    /**
     * @param int $definition_id
     * @return array
     */
    private function checkbox_attribute_values(int $definition_id): array
    {
        $zero_attribute_id = $this->attributeValueExists('0');
        $one_attribute_id = $this->attributeValueExists('1');

        if (!$zero_attribute_id) {
            $zero_attribute_id = $this->saveAttributeValue('0', $definition_id, false, false, CHECKBOX);
        }

        if (!$one_attribute_id) {
            $one_attribute_id = $this->saveAttributeValue('1', $definition_id, false, false, CHECKBOX);
        }

        return [$zero_attribute_id, $one_attribute_id];
    }

    /**
     * Inserts or updates a definition
     */
    public function saveDefinition(array &$definitionData, int $definitionId = NO_DEFINITION_ID): bool
    {
        $this->db->transStart();

        // Insert definition
        if ($definitionId === NO_DEFINITION_ID || !$this->exists($definitionId)) {
            if ($this->exists($definitionId, true)) {
                $success = $this->undelete($definitionId);
            } else {
                $builder = $this->db->table('attribute_definitions');
                $success = $builder->insert($definitionData);

                $definitionData['definition_id'] = $definitionId !== CATEGORY_DEFINITION_ID ? $this->db->insertID() : $definitionId;
            }
        }

        // Update definition
        else {
            $builder = $this->db->table('attribute_definitions');
            $builder->select('definition_type');
            $builder->where('definition_id', $definitionId);
            $builder->where('deleted', ACTIVE);
            $query = $builder->get();
            $row = $query->getRow();

            $from_definition_type = $row->definition_type;
            $to_definition_type = $definitionData['definition_type'];

            // Update definition values
            $builder->where('definition_id', $definitionId);
            $success = $builder->update($definitionData);
            $definitionData['definition_id'] = $definitionId;

            if ($from_definition_type !== $to_definition_type
                && !$this->convert_definition_data($definitionId, $from_definition_type, $to_definition_type)) {
                $this->db->transRollback();
                return false;
            }
        }

        $this->db->transComplete();

        $success &= $this->db->transStatus();

        return $success;
    }

    /**
     * @param string $definition_name
     * @param $definition_type
     * @return array
     */
    public function get_definition_by_name(string $definition_name, $definition_type = false): array
    {
        $builder = $this->db->table('attribute_definitions');
        $builder->where('definition_name', $definition_name);
        $builder->where('deleted', 0);

        if ($definition_type) {
            $builder->where('definition_type', $definition_type);
        }

        $row = $builder->get()->getRowArray();
        return $row ?? [];
    }

    /**
     * Inserts or updates an attribute link
     *
     * @param int $itemId
     * @param int $definitionId
     * @param int $attributeId
     * @return bool True if the attribute link was saved successfully, false otherwise.
     */
    public function saveAttributeLink(int $itemId, int $definitionId, int $attributeId): bool
    {
        $normalizedItemId = empty($itemId) ? null : $itemId;
        $normalizedAttributeId = empty($attributeId) ? null : $attributeId;

        $this->db->transStart();

        $builder = $this->db->table('attribute_links');

        if ($this->attributeLinkExists($normalizedItemId, $definitionId)) {
            $builder->set(['attribute_id' => $normalizedAttributeId]);
            $builder->where('definition_id', $definitionId);
            $builder->where('item_id', $normalizedItemId);
            $builder->where('sale_id', null);
            $builder->where('receiving_id', null);
            $builder->update();
        } else {
            $data = [
                'attribute_id'  => $normalizedAttributeId,
                'item_id'       => $normalizedItemId,
                'definition_id' => $definitionId
            ];
            $builder->insert($data);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Deletes attribute links for a given item and optionally a given definition. Does not delete links where sale_id
     * or receiving_id has a value. If a definitionId is not provided, deletes all attribute links for the item that do
     * not have a sale_id or receiving_id value.
     *
     * @param int $itemId The item ID to delete links for.
     * @param int|bool $definitionId The definition ID to delete links for. (optional)
     * @return bool true if successful, false otherwise
     */
    public function deleteAttributeLinks(int $itemId, int|bool $definitionId = false): bool
    {
        $deleteData = ['item_id' => $itemId];

        // Exclude rows where sale_id or receiving_id has a value
        $builder = $this->db->table('attribute_links');
        $builder->where('sale_id', null);
        $builder->where('receiving_id', null);

        if (!empty($definitionId)) {
            $deleteData += ['definition_id' => $definitionId];
        }

        return $builder->delete($deleteData);
    }

    /**
     * @param int $item_id
     * @param int|null $definition_id
     * @return object|null
     */
    public function get_link_value(int $item_id, ?int $definition_id): ?object
    {
        $builder = $this->db->table('attribute_links');
        $builder->where('item_id', $item_id);
        $builder->where('sale_id', null);
        $builder->where('receiving_id', null);
        if ($definition_id != null) {
            $builder->where('definition_id', $definition_id);
        }

        return $builder->get()->getRowObject();
    }

    /**
     * @param int $item_id
     * @param string $sale_receiving_fk
     * @param int|null $id
     * @param int|null $definition_flags
     * @return ResultInterface
     */
    public function get_link_values(int $item_id, string $sale_receiving_fk, ?int $id, ?int $definition_flags): ResultInterface
    {
        $format = $this->db->escape(dateformat_mysql());

        $builder = $this->db->table('attribute_links');
        $builder->select("GROUP_CONCAT(attribute_value SEPARATOR ', ') AS attribute_values");
        $builder->select("GROUP_CONCAT(DATE_FORMAT(attribute_date, $format) SEPARATOR ', ') AS attribute_dtvalues");
        $builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
        $builder->join('attribute_definitions', 'attribute_definitions.definition_id = attribute_links.definition_id');
        $builder->where('definition_type <>', GROUP);
        $builder->where('deleted', ACTIVE);
        $builder->where('item_id', $item_id);

        if (!empty($id)) {
            $builder->where($sale_receiving_fk, $id);
        } else {
            $builder->where('sale_id', null);
            $builder->where('receiving_id', null);
        }

        if (!empty($id)) {
            $builder->where(new RawSql("definition_flags & $definition_flags"));
        }
        return $builder->get();
    }

    /**
     * @param int $item_id
     * @param int $definition_id
     * @return object|null
     */
    public function getAttributeValue(int $item_id, int $definition_id): ?object
    {
        $builder = $this->db->table('attribute_values');
        $builder->join('attribute_links', 'attribute_links.attribute_id = attribute_values.attribute_id');
        $builder->where('item_id', $item_id);
        $builder->where('sale_id', null);
        $builder->where('receiving_id', null);
        $builder->where('definition_id', $definition_id);
        $query = $builder->get();

        if ($query->getNumRows() == 1) {
            return $query->getRow();
        }

        return $this->getEmptyObject('attribute_values');
    }

    /**
     * Gets a single attribute value by attribute ID.
     *
     * @param int $attributeId The attribute ID to look up
     * @param string $dataType The column name to retrieve (attribute_value, attribute_date, or attribute_decimal)
     * @return string|float|null The attribute value. Note: MySQL returns values as follows:
     *                           - attribute_value (TEXT): string
     *                           - attribute_date (DATE): string in 'Y-m-d' format
     *                           - attribute_decimal (DECIMAL): string or float depending on CodeIgniter configuration
     *                           Returns null if the attribute_id is not found.
     */
    public function getAttributeValueByAttributeId(int $attributeId, string $dataType): string|float|null
    {
        helper('attribute');
        validateAttributeValueType($dataType);

        $builder = $this->db->table('attribute_values');
        $builder->select($dataType);
        $builder->where('attribute_id', $attributeId);
        $builder->limit(1);
        $row = $builder->get()->getRow();

        return $row ? $row->$dataType : null;
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
     * @param int $item_id
     * @return array
     */
    public function get_attribute_values(int $item_id): array    // TODO: Is this function used anywhere in the code?
    {
        $builder = $this->db->table('attribute_links');
        $builder->select('attribute_values.attribute_value, attribute_values.attribute_decimal, attribute_values.attribute_date, attribute_links.definition_id');
        $builder->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id');
        $builder->where('item_id', $item_id);

        $results = $builder->get()->getResultArray();

        return $this->to_array($results, 'definition_id');
    }

    /**
     * @param int $item_id
     * @param string $sale_receiving_fk
     * @param int $id
     * @return void
     */
    public function copy_attribute_links(int $item_id, string $sale_receiving_fk, int $id): void
    {
        $query = 'SELECT ' . $this->db->escape($item_id) . ', definition_id, attribute_id, ' . $this->db->escape($id);
        $query .= ' FROM ' . $this->db->prefixTable('attribute_links');
        $query .= ' WHERE item_id = ' . $this->db->escape($item_id);
        $query .= ' AND sale_id IS NULL AND receiving_id IS NULL';

        $builder = $this->db->table('attribute_links');
        $builder->ignore(true)->setQueryAsData(new RawSql($query), null, 'item_id, definition_id, attribute_id, ' . $sale_receiving_fk)->insertBatch();
    }

    /**
     * Gets search suggestions (attribute values) for a specific attribute definition given a search term and definition_id
     *
     * @param int $definition_id
     * @param string $term
     * @return array
     */
    public function get_suggestions(int $definition_id, string $term): array
    {
        $suggestions = [];

        $builder = $this->db->table('attribute_definitions AS definition');
        $builder->distinct()->select('attribute_value, attribute_values.attribute_id');
        $builder->join('attribute_links', 'attribute_links.definition_id = definition.definition_id');
        $builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
        $builder->like('attribute_value', $term);
        $builder->where('deleted', ACTIVE);
        $builder->where('definition.definition_id', $definition_id);
        $builder->orderBy('attribute_value', 'ASC');

        foreach ($builder->get()->getResult('array') as $suggestion) {
            $suggestions[] = ['value' => $suggestion['attribute_id'], 'label' => $suggestion['attribute_value']];
        }

        return $suggestions;
    }

    /**
     * Saves an attribute value and creates an attribute link between the attribute value and item if necessary.
     * If the attribute value already exists, it will simply create a link to the existing attribute value.
     * If the attribute value exists but only has capitalization differences, it will update the existing attribute
     * value to match the new capitalization.
     * @param string $attributeValue The attribute value to be saved.
     * @param int $definitionId The ID of the attribute definition this value is associated with.
     * @param int|bool $itemId The ID of the item to link this attribute value to. If false, NULL will be inserted into
     * the database for that itemId indicating it is a dropdown value and not linked to a specific item.
     * @param int|bool $attributeId The ID of the attribute value if it already exists and is being updated. If false,
     * a new attribute value will be created.
     * @param string $definitionType The type of the attribute definition which will dictate which column the attribute
     * value is saved to.
     * @return int The attribute ID of the saved attribute value.
     */
    public function saveAttributeValue(string $attributeValue, int $definitionId, int|bool $itemId = false, int|bool $attributeId = false, string $definitionType = DROPDOWN): int
    {
        helper('attribute');
        $dataType = getAttributeDataType($definitionType);

        if ($definitionType === DATE) {
            $config = config(OSPOS::class)->settings;
            $date = DateTime::createFromFormat($config['dateformat'], $attributeValue);
            if ($date !== false) {
                $attributeValue = $date->format('Y-m-d');
            }
        }

        $this->db->transStart();

        $existingAttributeId = $this->attributeValueExists($attributeValue, $definitionType);

        // Update
        if ($existingAttributeId) {
            $attributeId = $existingAttributeId;
            $storedValue = $this->getAttributeValueByAttributeId($attributeId, $dataType);

            if ($dataType === 'attribute_value'
                && is_string($storedValue)
                && strcasecmp($storedValue, $attributeValue) === 0
                && $storedValue !== $attributeValue
            ) {
                $this->updateAttributeValue($attributeId, $dataType, $attributeValue);
            }
        } else {
            // Insert
            $builder = $this->db->table('attribute_values');
            $builder->set([$dataType => $attributeValue]);
            $builder->insert();
            $attributeId = $this->db->insertID();
        }

        if (!empty($definitionId)) {
            $success = $this->saveAttributeLink($itemId, $definitionId, $attributeId);
            if (!$success) {
                $this->db->transRollback();
                return 0;
            }
        }

        $this->db->transComplete();

        return $attributeId;
    }

    /**
     * Saves attribute data found in one row of a CSV import file. Loops through all attribute definitions and checks
     * if there is data for that attribute in the row. If there is, it saves the attribute value and link to the item.
     *
     * @param array $attributeValues Attribute name/value pairs from one row of the CSV import file
     * @param array $itemData Contains data for the item being imported/updated from the CSV file.
     * @param array $definitions Contains all attribute definitions in the system.
     * @return bool Returns true if all attribute data saves correctly and false if there is an error saving any of
     * the attribute data.
     */
    public function saveCSVRowAttributeData(array $attributeValues, array $itemData, array $definitions): bool
    {
        helper('attribute');
        foreach ($definitions as $definition) {
            $attributeName = $definition['definition_name'];
            $attributeValue = $attributeValues[$attributeName] ?? null;

            if (isset($attributeValue) && strcasecmp($attributeValue, '_DELETE_') === 0) {
                if (!$this->deleteAttributeLinks($itemData['item_id'], $definition['definition_id'])) {
                    return false;
                }
                continue;
            }

            // Create attribute value
            if (!empty($attributeValue) || $attributeValue === '0') {
                if ($definition['definition_type'] === CHECKBOX) {
                    $checkbox_is_unchecked = (strcasecmp($attributeValue, 'false') === 0 || $attributeValue === '0');
                    $attributeValue = $checkbox_is_unchecked ? '0' : '1';

                    $attribute_id = $this->storeCSVAttributeValue($attributeValue, $definition, $itemData['item_id']);
                } elseif (!empty($attributeValue) || $attributeValue === '0') {
                    $attribute_id = $this->storeCSVAttributeValue($attributeValue, $definition, $itemData['item_id']);
                } else {
                    return false;
                }

                if (!$attribute_id) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Saves the attribute_value and attribute_link in a CSV file if necessary
     *
     * @param string $value
     * @param array $attributeData
     * @param int $itemId
     * @return bool|int
     */
    private function storeCSVAttributeValue(string $value, array $attributeData, int $itemId): bool|int
    {
        $this->db->transStart();
        $attributeId = $this->attributeValueExists($value, $attributeData['definition_type']);

        $this->deleteAttributeLinks($itemId, $attributeData['definition_id']);

        if (!$attributeId) {
            $attributeId = $this->saveAttributeValue($value, $attributeData['definition_id'], $itemId, false, $attributeData['definition_type']);
        } else {
            helper('attribute');
            $dataType = getAttributeDataType($attributeData['definition_type']);
            $storedValue = $this->getAttributeValueByAttributeId($attributeId, $dataType);

            // Update the attribute value if only the case has changed and only for text values.
            if ($dataType === 'attribute_value'
                && is_string($storedValue)
                && strcasecmp($storedValue, $value) === 0
                && $storedValue !== $value) {
                $attributeId = $this->saveAttributeValue($value, $attributeData['definition_id'], $itemId, $attributeId, $attributeData['definition_type']);
            } elseif (!$this->saveAttributeLink($itemId, $attributeData['definition_id'], $attributeId)) {
                return false;
            }
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return false;
        }

        return $attributeId;
    }

    /**
     * Deletes an Attribute definition from the database and associated column in the items_import.csv
     *
     * @param    int        $definition_id    Attribute definition ID to remove.
     * @return     boolean                    true if successful and false if there is a failure
     */
    public function deleteDefinition(int $definition_id): bool
    {
        $this->deleteAttributeLinksByDefinitionId($definition_id);

        $builder = $this->db->table('attribute_definitions');
        $builder->where('definition_id', $definition_id);

        return $builder->update(['deleted' => DELETED]);
    }

    /**
     * @param array $definition_ids
     * @return bool
     */
    public function deleteDefinitionList(array $definition_ids): bool
    {
        $this->deleteAttributeLinksByDefinitionId($definition_ids);

        $builder = $this->db->table('attribute_definitions');
        $builder->whereIn('definition_id', $definition_ids);

        return $builder->update(['deleted' => DELETED]);
    }

    /**
     * Deletes attribute links by definition ID
     *
     * @param int|array $definition_id
     */
    public function deleteAttributeLinksByDefinitionId(int|array $definition_id): void
    {
        if (!is_array($definition_id)) {
            $definition_id = [$definition_id];
        }

        $builder = $this->db->table('attribute_links');
        $builder->whereIn('definition_id', $definition_id);
        $builder->delete();
    }

    /**
     * Deletes any attribute_links for a specific definition that do not have an item_id associated with them and are not DROPDOWN types
     *
     * @param int $definition_id
     * @return boolean true is returned if the delete was successful or false if there were any failures
     */
    public function delete_orphaned_links(int $definition_id): bool
    {
        $builder = $this->db->table('attribute_definitions');
        $builder->select('definition_type');
        $builder->where('definition_id', $definition_id);

        $definition = $builder->get()->getRow();

        if ($definition->definition_type != DROPDOWN) {
            $this->db->transStart();

            $builder = $this->db->table('attribute_links');
            $builder->where('item_id', null);
            $builder->where('definition_id', $definition_id);
            $builder->delete();

            $this->db->transComplete();

            return $this->db->transStatus();
        }

        return true;    // Return true when definition_type is DROPDOWN
    }

    /**
     * Deletes any orphaned values that do not have associated links
     *
     * @return boolean true is returned if the delete was successful or false if there were any failures
     */
    public function deleteOrphanedValues(): bool
    {
        $subquery = $this->db->table('attribute_links')
            ->distinct()
            ->select('attribute_id');

        $this->db->transStart();

        $builder = $this->db->table('attribute_values');
        $builder->whereNotIn('attribute_id', $subquery, false);
        $builder->delete();

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Undeletes one attribute definition
     */
    public function undelete(int $definition_id): bool
    {
        $builder = $this->db->table('attribute_definitions');
        $builder->where('definition_id', $definition_id);

        return $builder->update(['deleted' => ACTIVE]);
    }

    /**
     *
     * @param array $attributes attributes that need to be fixed
     * @param int $definition_id
     * @param string $definition_type This dictates what column should be populated in any new attribute_values that are created
     * @return bool
     */
    public function attribute_cleanup(array $attributes, int $definition_id, string $definition_type): bool
    {
        $this->db->transBegin();

        foreach ($attributes as $attribute) {
            $new_attribute_id = $this->saveAttributeValue($attribute['attribute_value'], $definition_id, false, $attribute['attribute_id'], $definition_type);

            if (!$this->saveAttributeLink($attribute['item_id'], $definition_id, $new_attribute_id)) {
                log_message('error', 'Transaction failed');
                $this->db->transRollback();
                return false;
            }
        }
        $success = $this->delete_orphaned_links($definition_id);

        $this->db->transCommit();
        return $success;
    }

    /**
     * Returns all attribute_ids and item_ids assigned to that definition_id
     *
     * @param int $definition_id
     * @return array All attribute_id and item_id pairs in the attribute_links table with that attribute definition_id
     */
    public function get_attributes_by_definition(int $definition_id): array
    {
        $builder = $this->db->table('attribute_links');
        $builder->select('attribute_links.attribute_id, item_id, attribute_value, attribute_decimal, attribute_date');
        $builder->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
        $builder->where('definition_id', $definition_id);

        return $builder->get()->getResultArray();
    }

    /**
     * @param string $attribute_value
     * @return int
     */
    private function getAttributeIdByValue(string $attribute_value): int
    {
        $builder = $this->db->table('attribute_values');
        $builder->select('attribute_id');
        $builder->where('attribute_value', $attribute_value);
        return $builder->get()->getRow('attribute_id');
    }

    /**
     * Deletes Attribute Links associated with a specific definition ID and attribute ID.
     * Does not delete rows where sale_id or receiving_id has a value to retain records.
     *
     * @param int $definitionId
     * @param int $attributeId
     * @return void
     */
    private function deleteAttributeLinksByDefinitionIdAndAttributeId(int $definitionId, int $attributeId): void
    {
        $builder = $this->db->table('attribute_links');
        $builder->where('sale_id', null);
        $builder->where('receiving_id', null);
        $builder->where('definition_id', $definitionId);
        $builder->where('attribute_id', $attributeId);
        $builder->delete();
    }

    /**
     * Updates the attribute_value, attribute_date, or attribute_decimal column in the attribute_values table based on
     * the provided data type for a specific attribute ID.
     *
     * @param int $attributeId
     * @param string $dataType
     * @param mixed $attributeValue
     * @return void
     */
    private function updateAttributeValue(int $attributeId, string $dataType, mixed $attributeValue): void
    {
        helper('attribute');
        validateAttributeValueType($dataType);

        // Update the attribute_values table
        $builder = $this->db->table('attribute_values');
        $builder->set([$dataType => $attributeValue]);
        $builder->where('attribute_id', $attributeId);
        $builder->update();

        // Check if this attribute_id is linked to definition_id = -1 (category dropdown) using COUNT
        $linkBuilder = $this->db->table('attribute_links');
        $linkBuilder->selectCount('attribute_id', 'cnt');
        $linkBuilder->where('attribute_id', $attributeId);
        $linkBuilder->where('definition_id', CATEGORY_DEFINITION_ID);
        $countRow = $linkBuilder->get()->getRow();
        $isCategoryDropdownAttribute = $countRow && $countRow->cnt > 0;

        // Update the items.category column to match new capitalization.
        if ($isCategoryDropdownAttribute) {
            $itemsBuilder = $this->db->table('items');
            $itemsBuilder->set(['category' => $attributeValue]);
            $itemsBuilder->where('category', $attributeValue);
            $itemsBuilder->update();
        }
    }
}
