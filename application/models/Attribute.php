<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Attribute class
 */

class Attribute extends CI_Model
{
	const SHOW_IN_ITEMS = 1;
	const SHOW_IN_SALES = 2;
	const SHOW_IN_RECEIVINGS = 4;

	public static function get_definition_flags()
	{
		$class = new ReflectionClass(__CLASS__);

		return array_flip($class->getConstants());
	}

	/*
	 Determines if a given definition_id is an attribute
	 */
	public function exists($definition_id, $deleted = FALSE)
	{
		$this->db->where('definition_id', $definition_id);
		$this->db->where('deleted', $deleted);

		return ($this->db->get('attribute_definitions')->num_rows() == 1);
	}

	/**
	 * Returns whether an attribute_link row exists given an item_id and optionally a definition_id
	 * @param	int		$item_id
	 * @param	boolean	$definition_id
	 * @return	boolean					TRUE if at least one attribute_link exists or FALSE if no attributes exist.
	 */
	public function link_exists($item_id, $definition_id = FALSE)
	{
		$this->db->where('item_id', $item_id);
		$this->db->where('sale_id');
		$this->db->where('receiving_id');

		if(empty($definition_id))
		{
			$this->db->where('definition_id <>');
			$this->db->where('attribute_id');
		}
		else
		{
			$this->db->where('definition_id', $definition_id);
		}

		return ($this->db->get('attribute_links')->num_rows() > 0);
	}

	/*
	 * Determines if a given attribute_value exists in the attribute_values table and returns the attribute_id if it does
	 */
	public function value_exists($attribute_value, $definition_type = TEXT)
	{
		switch($definition_type)
		{
			case DATE:
				$data_type				= 'date';
				$attribute_date_value	= DateTime::createFromFormat($this->Appconfig->get('dateformat'), $attribute_value);
				$attribute_value		= $attribute_date_value->format('Y-m-d');
				break;
			case DECIMAL:
				$data_type = 'decimal';
				break;
			default:
				$data_type = 'value';
				break;
		}

		$this->db->select('attribute_id');
		$this->db->where("attribute_$data_type", $attribute_value);
		$query = $this->db->get('attribute_values');

		if($query->num_rows() > 0)
		{
			return $query->row()->attribute_id;
		}

		return FALSE;
	}

	/*
	 Gets information about a particular attribute definition
	 */
	public function get_info($definition_id)
	{
		$this->db->select('parent_definition.definition_name AS definition_group, definition.*');
		$this->db->from('attribute_definitions AS definition');
		$this->db->join('attribute_definitions AS parent_definition', 'parent_definition.definition_id = definition.definition_fk', 'left');
		$this->db->where('definition.definition_id', $definition_id);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj = new stdClass();

			//Get all the fields from attribute_definitions table
			foreach($this->db->list_fields('attribute_definitions') as $field)
			{
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}

	/*
	 Performs a search on attribute definitions
	 */
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'definition.definition_name', $order = 'asc')
	{
		$this->db->select('parent_definition.definition_name AS definition_group, definition.*');
		$this->db->from('attribute_definitions AS definition');
		$this->db->join('attribute_definitions AS parent_definition', 'parent_definition.definition_id = definition.definition_fk', 'left');

		$this->db->group_start();
			$this->db->like('definition.definition_name', $search);
			$this->db->or_like('definition.definition_type', $search);
		$this->db->group_end();

		$this->db->where('definition.deleted', 0);
		$this->db->order_by($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	public function get_attributes_by_item($item_id)
	{
		$this->db->join('attribute_links', 'attribute_links.definition_id = attribute_definitions.definition_id');
		$this->db->where('item_id', $item_id);
		$this->db->where('sale_id');
		$this->db->where('receiving_id');
		$this->db->where('deleted', 0);
		$this->db->order_by('definition_name', 'ASC');

		$results = $this->db->get('attribute_definitions')->result_array();

		return $this->to_array($results, 'definition_id');
	}

	public function get_values_by_definitions($definition_ids)
	{
		if(count($definition_ids ? : []))
		{
			$this->db->group_start();
				$this->db->where_in('definition_fk', array_keys($definition_ids));
				$this->db->or_where_in('definition_id', array_keys($definition_ids));
				$this->db->where('definition_type !=', GROUP);
			$this->db->group_end();

			$this->db->where('deleted', 0);

			$results = $this->db->get('attribute_definitions')->result_array();

			return $this->to_array($results, 'definition_id');
		}

		return [];
	}

	public function get_definitions_by_type($attribute_type, $definition_id = NO_DEFINITION_ID)
	{
		$this->db->where('definition_type', $attribute_type);
		$this->db->where('deleted', 0);
		$this->db->where('definition_fk');

		if($definition_id != CATEGORY_DEFINITION_ID)
		{
			$this->db->where('definition_id <>', $definition_id);
		}

		$results = $this->db->get('attribute_definitions')->result_array();

		return $this->to_array($results, 'definition_id', 'definition_name');
	}

	public function get_definitions_by_flags($definition_flags)
	{
		$this->db->where('definition_flags &', $definition_flags);
		$this->db->where('deleted', 0);
		$this->db->where('definition_type <>', GROUP);
		$this->db->order_by('definition_id');
		$results = $this->db->get('attribute_definitions')->result_array();

		return $this->to_array($results, 'definition_id', 'definition_name');
	}

	/**
	 * Returns an array of attribute definition names and IDs
	 *
	 * @param 	boolean		$groups		If FALSE does not return GROUP type attributes in the array
	 * @return	array					Array containing definition IDs, attribute names and -1 index with the local language '[SELECT]' line.
	 */
	public function get_definition_names($groups = TRUE)
	{
		$this->db->where('deleted', 0);
		$this->db->order_by('definition_name','ASC');

		if($groups === FALSE)
		{
			$this->db->where_not_in('definition_type',GROUP);
		}

		$results = $this->db->get('attribute_definitions')->result_array();
		$definition_name = array(-1 => $this->lang->line('common_none_selected_text'));

		return $definition_name + $this->to_array($results, 'definition_id', 'definition_name');
	}

	public function get_definition_values($definition_id)
	{
		$attribute_values = [];

		if($definition_id > 0 || $definition_id == CATEGORY_DEFINITION_ID)
		{
			$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
			$this->db->where('item_id');
			$this->db->where('definition_id', $definition_id);
			$this->db->order_by('attribute_value','ASC');

			$results = $this->db->get('attribute_links')->result_array();

			return $this->to_array($results, 'attribute_id', 'attribute_value');
		}

		return $attribute_values;
	}

	private function to_array($results, $key, $value = '')
	{
		return array_column(array_map(function($result) use ($key, $value){
			return [$result[$key], empty($value) ? $result : $result[$value]];
		}, $results), 1, 0);
	}

	/*
	 Gets total of rows
	 */
	public function get_total_rows()
	{
		$this->db->where('deleted', 0);

		return $this->db->count_all_results('attribute_definitions');
	}

	/*
	 Get number of rows
	 */
	public function get_found_rows($search)
	{
		return $this->search($search)->num_rows();
	}

	private function check_data_validity($definition_id, $from, $to)
	{
		$success = FALSE;

		if($from === TEXT)
		{
			$success = TRUE;

			$this->db->distinct()->select('attribute_value');
			$this->db->join('attribute_links', 'attribute_values.attribute_id = attribute_links.attribute_id');
			$this->db->where('definition_id', $definition_id);

			foreach($this->db->get('attribute_values')->result() as $attribute)
			{
				switch($to)
				{
					case DATE:
							$success = valid_date($attribute->attribute_value);
						break;
					case DECIMAL:
							$success = valid_decimal($attribute->attribute_value);
						break;
				}

				if($success === FALSE)
				{
					$affected_items = $this->get_items_by_value($attribute->attribute_value, $definition_id);
					foreach($affected_items as $affected_item)
					{
						$affected_items[] = $affected_item['item_id'];
					}

					log_message('ERROR', "Attribute_value: '$attribute->attribute_value' cannot be converted to $to. Affected Items: ". implode(',', $affected_items));
					unset($affected_items);
				}
			}
		}
		return $success;
	}

	/**
	 * Returns all item_ids with a specific attribute_value and attribute_definition
	 * @param string $attribute_value
	 * @param int $definition_id
	 * @return array
	 */
	private function get_items_by_value($attribute_value, $definition_id)
	{
		$this->db->select('item_id');
		$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
		$this->db->where('definition_id', $definition_id);
		$this->db->where('attribute_value', $attribute_value);
		return $this->db->get('attribute_links')->result_array();
	}


	/**
	 * Converts data in attribute_values and attribute_links tables associated with the conversion of one attribute type to another.
	 * @param int $definition_id
	 * @param 	string	$from_type
	 * @param 	string	$to_type
	 * @return boolean
	 */
	private function convert_definition_data($definition_id, $from_type, $to_type)
	{
		$success = FALSE;

		if($from_type === TEXT)
		{
			if(in_array($to_type, [DATE, DECIMAL], TRUE))
			{
				if($this->check_data_validity($definition_id, $from_type, $to_type))
				{
					$attributes_to_convert = $this->get_attributes_by_definition($definition_id);
					$success = $this->attribute_cleanup($attributes_to_convert, $definition_id, $to_type);
				}
			}
			else if($to_type === DROPDOWN)
			{
				$success = TRUE;
			}
			else if($to_type === CHECKBOX)
			{
				$checkbox_attribute_values = $this->checkbox_attribute_values($definition_id);

				$this->db->trans_start();

				$query = 'UPDATE '. $this->db->dbprefix('attribute_links') .' links ';
				$query .= 'JOIN '. $this->db->dbprefix('attribute_values') .' vals ';
				$query .= 'ON vals.attribute_id = links.attribute_id ';
				$query .= "SET links.attribute_id = IF((attribute_value IN('FALSE','0','') OR (attribute_value IS NULL)), $checkbox_attribute_values[0], $checkbox_attribute_values[1]) ";
				$query .= 'WHERE definition_id = '. $this->db->escape($definition_id);
				$success = $this->db->query($query);

				$this->db->trans_complete();
			}
		}
		else if($from_type === DROPDOWN)
		{
			if(in_array($to_type, [TEXT, CHECKBOX], TRUE))
			{
				if($to_type === CHECKBOX)
				{
					$checkbox_attribute_values = $this->checkbox_attribute_values($definition_id);

					$this->db->trans_start();

					$query = 'UPDATE '. $this->db->dbprefix('attribute_links') .' links ';
					$query .= 'JOIN '. $this->db->dbprefix('attribute_values') .' vals ';
					$query .= 'ON vals.attribute_id = links.attribute_id ';
					$query .= "SET links.attribute_id = IF((attribute_value IN('FALSE','0','') OR (attribute_value IS NULL)), $checkbox_attribute_values[0], $checkbox_attribute_values[1]) ";
					$query .= 'WHERE definition_id = '. $this->db->escape($definition_id);
					$success = $this->db->query($query);

					$this->db->trans_complete();
				}
			}
		}
		else
		{
			$success = TRUE;
		}

		$this->delete_orphaned_links($definition_id);
		$this->delete_orphaned_values();
		return $success;
	}

	private function checkbox_attribute_values($definition_id)
	{
		$zero_attribute_id = $this->value_exists('0');
		$one_attribute_id = $this->value_exists('1');

		if($zero_attribute_id === FALSE)
		{
			$zero_attribute_id = $this->save_value('0', $definition_id, FALSE, FALSE, CHECKBOX);
		}

		if($one_attribute_id === FALSE)
		{
			$one_attribute_id = $this->save_value('1', $definition_id, FALSE, FALSE, CHECKBOX);
		}

		return array($zero_attribute_id, $one_attribute_id);
	}

	/*
	 Inserts or updates a definition
	 */
	public function save_definition(&$definition_data, $definition_id = NO_DEFINITION_ID)
	{
		$this->db->trans_start();

		//Definition doesn't exist
		if($definition_id === NO_DEFINITION_ID || !$this->exists($definition_id))
		{
			if($this->exists($definition_id,TRUE))
			{
				$success = $this->undelete_definition($definition_id);
			}
			else
			{
				$success = $this->db->insert('attribute_definitions', $definition_data);
				$definition_data['definition_id'] = $this->db->insert_id();
			}
		}

		//Definition already exists
		else
		{
			//Get current definition type and name
			$this->db->select('definition_type');
			$this->db->where('definition_id', $definition_id);

			$row = $this->db->get('attribute_definitions')->row();
			$from_definition_type = $row->definition_type;
			$to_definition_type = $definition_data['definition_type'];

			//Update the definition values
			$this->db->where('definition_id', $definition_id);

			$success = $this->db->update('attribute_definitions', $definition_data);
			$definition_data['definition_id'] = $definition_id;

			if($from_definition_type !== $to_definition_type)
			{
				if($this->convert_definition_data($definition_id, $from_definition_type, $to_definition_type) === FALSE)
				{
					return FALSE;
				}
			}
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	public function get_definition_by_name($definition_name, $definition_type = FALSE)
	{
		$this->db->where('definition_name', $definition_name);

		if($definition_type != FALSE)
		{
			$this->db->where('definition_type', $definition_type);
		}

		return $this->db->get('attribute_definitions')->result_array();
	}

	public function save_link($item_id, $definition_id, $attribute_id)
	{
		$this->db->trans_start();

		if($this->link_exists($item_id, $definition_id))
		{
			$this->db->where('definition_id', $definition_id);
			$this->db->where('item_id', $item_id);
			$this->db->where('sale_id');
			$this->db->where('receiving_id');
			$this->db->update('attribute_links', array('attribute_id' => $attribute_id));
		}
		else
		{
			$this->db->insert('attribute_links', array(
					'attribute_id' => $attribute_id,
					'item_id' => $item_id,
					'definition_id' => $definition_id));
		}

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function delete_link($item_id, $definition_id = FALSE)
	{
		$delete_data = array('item_id' => $item_id);

		//Exclude rows where sale_id or receiving_id has a value
		$this->db->where('sale_id');
		$this->db->where('receiving_id');

		if(!empty($definition_id))
		{
			$delete_data += ['definition_id' => $definition_id];
		}

		$success = $this->db->delete('attribute_links', $delete_data);

		return $success;
	}

	public function get_link_value($item_id, $definition_id)
	{
		$this->db->where('item_id', $item_id);
		$this->db->where('sale_id');
		$this->db->where('receiving_id');
		$this->db->where('definition_id', $definition_id);

		return $this->db->get('attribute_links')->row_object();
	}

	public function get_link_values($item_id, $sale_receiving_fk, $id, $definition_flags)
	{
		$format = $this->db->escape(dateformat_mysql());
		$this->db->select("GROUP_CONCAT(attribute_value SEPARATOR ', ') AS attribute_values");
		$this->db->select("GROUP_CONCAT(DATE_FORMAT(attribute_date, $format) SEPARATOR ', ') AS attribute_dtvalues");
		$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
		$this->db->join('attribute_definitions', 'attribute_definitions.definition_id = attribute_links.definition_id');
		$this->db->where('definition_type <>', GROUP);
		$this->db->where('deleted', 0);
		$this->db->where('item_id', intval($item_id));

		if(!empty($id))
		{
			$this->db->where($sale_receiving_fk, $id);
		}
		else
		{
			$this->db->where('sale_id');
			$this->db->where('receiving_id');
		}

		$this->db->where('definition_flags & ', $definition_flags);

		return $this->db->get('attribute_links');
	}

	public function get_attribute_value($item_id, $definition_id)
	{
		$this->db->join('attribute_links', 'attribute_links.attribute_id = attribute_values.attribute_id');
		$this->db->where('item_id', intval($item_id));
		$this->db->where('sale_id');
		$this->db->where('receiving_id');
		$this->db->where('definition_id', $definition_id);

		return $this->db->get('attribute_values')->row_object();
	}

	public function get_attribute_values($item_id)
	{
		$this->db->select('attribute_values.attribute_value, attribute_values.attribute_decimal, attribute_values.attribute_date, attribute_links.definition_id');
		$this->db->join('attribute_values', 'attribute_links.attribute_id = attribute_values.attribute_id');
		$this->db->where('item_id', intval($item_id));

		$results = $this->db->get('attribute_links')->result_array();

		return $this->to_array($results, 'definition_id');
	}


	public function copy_attribute_links($item_id, $sale_receiving_fk, $id)
	{
		$this->db->query(
			'INSERT INTO ' . $this->db->dbprefix('attribute_links') . ' (item_id, definition_id, attribute_id, ' . $sale_receiving_fk . ')
			SELECT ' . $this->db->escape($item_id) . ', definition_id, attribute_id, ' . $this->db->escape($id) . '
			FROM ' . $this->db->dbprefix('attribute_links') . '
			WHERE item_id = ' . $this->db->escape($item_id) . ' AND sale_id IS NULL AND receiving_id IS NULL'
			);
	}

	public function get_suggestions($definition_id, $term)
	{
		$suggestions = [];
		$this->db->distinct();
		$this->db->select('attribute_value, attribute_values.attribute_id');
		$this->db->from('attribute_definitions AS definition');
		$this->db->join('attribute_links', 'attribute_links.definition_id = definition.definition_id');
		$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
		$this->db->like('attribute_value', $term);
		$this->db->where('deleted', 0);
		$this->db->where('definition.definition_id', $definition_id);
		$this->db->order_by('attribute_value','ASC');

		foreach($this->db->get()->result() as $row)
		{
			$row_array = (array) $row;
			$suggestions[] = array('value' => $row_array['attribute_id'], 'label' => $row_array['attribute_value']);
		}

		return $suggestions;
	}

	public function save_value($attribute_value, $definition_id, $item_id = FALSE, $attribute_id = FALSE, $definition_type = DROPDOWN)
	{
		$this->db->trans_start();

		$locale_date_format = $this->Appconfig->get('dateformat');

		//New Attribute
		if(empty($attribute_id) || empty($item_id))
		{
		//Update attribute_value
			$attribute_id = $this->value_exists($attribute_value, $definition_type);

			if($attribute_id === FALSE)
			{
				switch($definition_type)
				{
					case DATE:
						$data_type				= 'date';
						$attribute_date_value	= DateTime::createFromFormat($locale_date_format, $attribute_value);
						$attribute_value		= $attribute_date_value->format('Y-m-d');
						break;
					case DECIMAL:
						$data_type	= 'decimal';
						break;
					default:
						$data_type	= 'value';
						break;
				}

				$this->db->insert('attribute_values', array("attribute_$data_type" => $attribute_value));
			}

			$attribute_id = $attribute_id ? $attribute_id : $this->db->insert_id();

			$this->db->insert('attribute_links', array(
				'attribute_id' => empty($attribute_id) ? NULL : $attribute_id,
				'item_id' => empty($item_id) ? NULL : $item_id,
				'definition_id' => $definition_id));
		}
		//Existing Attribute
		else
		{
			switch($definition_type)
			{
				case DATE:
					$data_type				= 'date';
					$attribute_date_value	= DateTime::createFromFormat($locale_date_format, $attribute_value);
					$attribute_value		= $attribute_date_value->format('Y-m-d');
					break;
				case DECIMAL:
					$data_type	= 'decimal';
					break;
				default:
					$data_type	= 'value';
					break;
			}

				$this->db->where('attribute_id', $attribute_id);
				$this->db->update('attribute_values', array("attribute_$data_type" => $attribute_value));
		}

		$this->db->trans_complete();

		return $attribute_id;
	}

	public function delete_value($attribute_value, $definition_id)
	{
		return $this->db->query('DELETE atrv, atrl FROM ' . $this->db->dbprefix('attribute_values') . ' atrv, ' . $this->db->dbprefix('attribute_links') .  ' atrl ' .
			'WHERE atrl.attribute_id = atrv.attribute_id AND atrv.attribute_value = ' . $this->db->escape($attribute_value) . ' AND atrl.definition_id = ' . $this->db->escape($definition_id));
	}

	/**
	 * Deletes an Attribute definition from the database and associated column in the items_import.csv
	 *
	 * @param	int		$definition_id	Attribute definition ID to remove.
	 * @return 	boolean					TRUE if successful and FALSE if there is a failure
	 */
	public function delete_definition($definition_id)
	{
		$this->db->where('definition_id', $definition_id);

		return $this->db->update('attribute_definitions', array('deleted' => 1));
	}

	public function delete_definition_list($definition_ids)
	{
		$this->db->where_in('definition_id', $definition_ids);

		return $this->db->update('attribute_definitions', array('deleted' => 1));
	}

	/**
	 * Deletes any attribute_links for a specific definition that do not have an item_id associated with them and are not DROPDOWN types
	 *
	 * @param int $definition_id
	 * @return boolean TRUE is returned if the delete was successful or FALSE if there were any failures
	 */
	public function delete_orphaned_links($definition_id)
	{
		$this->db->select('definition_type');
		$this->db->where('definition_id', $definition_id);

		$definition = $this->db->get('attribute_definitions')->row();

		if($definition->definition_type != DROPDOWN)
		{
			$this->db->trans_start();

			$this->db->where('item_id');
			$this->db->where('definition_id', $definition_id);
			$this->db->delete('attribute_links');

			$this->db->trans_complete();

			return $this->db->trans_status();
		}
	}

	/*
	 * Deletes any orphaned values that do not have associated links
	 * @param int $definition_id
	 * @return boolean TRUE is returned if the delete was successful or FALSE if there were any failures
	 */
	public function delete_orphaned_values()
	{
		$this->db->distinct();
		$this->db->select('attribute_id');
		$attribute_ids = $this->db->get_compiled_select('attribute_links');

		$this->db->trans_start();

		$this->db->where_not_in('attribute_id', $attribute_ids, FALSE);
		$this->db->delete('attribute_values');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	/*
	Undeletes one attribute definition
	*/
	public function undelete($definition_id)
	{
		$this->db->where('definition_id', $definition_id);

		return $this->db->update('attribute_definitions', array('deleted' => 0));
	}

	/**
	 *
	 * @param array 	attributes 			attributes that need to be fixed
	 * @param int 		$definition_id
	 * @param string	$definition_type	This dictates what column should be populated in any new attribute_values that are created
	 */
	public function attribute_cleanup($attributes, $definition_id, $definition_type)
	{
		$this->db->trans_begin();

		foreach($attributes as $attribute)
		{
			$new_attribute_id = $this->save_value($attribute['attribute_value'], $definition_id, FALSE, $attribute['attribute_id'], $definition_type);

			if($this->save_link($attribute['item_id'], $definition_id, $new_attribute_id) == FALSE)
			{
				log_message('Error', 'Transaction failed');
				$this->db->trans_rollback();
				return FALSE;
			}
		}
		$success = $this->delete_orphaned_links($definition_id);

		$this->db->trans_commit();
		return $success;
	}

	/**
	 * Returns all attribute_ids and item_ids assigned to that definition_id
	 *
	 * @param int $definition_id
	 * @return array All attribute_id and item_id pairs in the attribute_links table with that attribute definition_id
	 */
	public function get_attributes_by_definition($definition_id)
	{
		$this->db->select('attribute_links.attribute_id, item_id, attribute_value, attribute_decimal, attribute_date');
		$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
		$this->db->where('definition_id', $definition_id);

		return $this->db->get('attribute_links')->result_array();
	}
}