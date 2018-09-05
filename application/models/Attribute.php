<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

define('GROUP', 'GROUP');
define('DROPDOWN', 'DROPDOWN');
define('DATETIME', 'DATETIME');
define('TEXT', 'TEXT');

const DEFINITION_TYPES = [GROUP, DROPDOWN, TEXT, DATETIME];

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
		$this->db->from('attribute_definitions');
		$this->db->where('definition_id', $definition_id);
		$this->db->where('deleted', $deleted);

		return ($this->db->get()->num_rows() == 1);
	}

	public function link_exists($item_id, $definition_id = FALSE)
	{
		$this->db->where('sale_id');
		$this->db->where('receiving_id');
		$this->db->from('attribute_links');
		if(empty($definition_id))
		{
			$this->db->where('definition_id <>');
			$this->db->where('attribute_id');
		}
		else
		{
			$this->db->where('definition_id', $definition_id);

		}
		$this->db->where('item_id', $item_id);

		return ($this->db->get()->num_rows() > 0);
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

			//Get all the fields from items table
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
		$this->db->select('definition_group.definition_name AS definition_group, definition.*');
		$this->db->from('attribute_definitions AS definition');
		$this->db->join('attribute_definitions AS definition_group', 'definition_group.definition_id = definition.definition_fk', 'left');

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
		$this->db->from('attribute_definitions');
		$this->db->join('attribute_links', 'attribute_links.definition_id = attribute_definitions.definition_id');
		$this->db->where('item_id', $item_id);
		$this->db->where('receiving_id');
		$this->db->where('sale_id');
		$this->db->where('deleted', 0);

		$results = $this->db->get()->result_array();

		return $this->_to_array($results, 'definition_id');
	}

	public function get_values_by_definitions($definition_ids)
	{
		if (count($definition_ids) > 0)
		{
			$this->db->from('attribute_definitions');

			$this->db->group_start();
			$this->db->where_in('definition_fk', array_keys($definition_ids));
			$this->db->or_where_in('definition_id', array_keys($definition_ids));
			$this->db->where('definition_type !=', GROUP);
			$this->db->group_end();

			$this->db->where('deleted', 0);

			$results = $this->db->get()->result_array();

			return $this->_to_array($results, 'definition_id');
		}

		return array();
	}

	public function get_definitions_by_type($attribute_type, $definition_id = -1)
	{
		$this->db->from('attribute_definitions');
		$this->db->where('definition_type', $attribute_type);
		$this->db->where('deleted', 0);

		if($definition_id != -1)
		{
			$this->db->where('definition_id != ', $definition_id);
		}

		$this->db->where('definition_fk');
		$results = $this->db->get()->result_array();

		return $this->_to_array($results, 'definition_id', 'definition_name');
	}

	public function get_definitions_by_flags($definition_flags)
	{
		$this->db->from('attribute_definitions');
		$this->db->where('definition_flags &', $definition_flags);
		$this->db->where('deleted', 0);
		$this->db->where('definition_type <>', GROUP);
		$this->db->order_by('definition_id');
		$results = $this->db->get()->result_array();

		return $this->_to_array($results, 'definition_id', 'definition_name');
	}

	public function get_definition_names()
	{
		$this->db->from('attribute_definitions');
		$this->db->where('deleted', 0);
		$results = $this->db->get()->result_array();

		$definition_name = array(-1 => $this->lang->line('common_none_selected_text'));

		return $definition_name + $this->_to_array($results, 'definition_id', 'definition_name');
	}

	public function get_definition_values($definition_id)
	{
		$attribute_values = [];

		if($definition_id > -1)
		{
			$this->db->from('attribute_links');
			$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
			$this->db->where('definition_id', $definition_id);
			$this->db->where('item_id');

			$results = $this->db->get()->result_array();

			return $this->_to_array($results, 'attribute_id', 'attribute_value');
		}

		return $attribute_values;
	}

	private function _to_array($results, $key, $value = '')
	{
		return array_column(array_map(function($result) use ($key, $value) {
			return [$result[$key], empty($value) ? $result : $result[$value]];
		}, $results), 1, 0);
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('attribute_definitions');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/*
	Get number of rows
	*/
	public function get_found_rows($search)
	{
		return $this->search($search)->num_rows();
	}

	/*
	Inserts or updates a definition
	*/
	public function save_definition(&$definition_data, $definition_id = -1)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		if($definition_id === -1 || !$this->exists($definition_id))
		{
			$success = $this->db->insert('attribute_definitions', $definition_data);
			$definition_data['definition_id'] = $this->db->insert_id();
		}
		else
		{
			$this->db->where('definition_id', $definition_id);
			$success = $this->db->update('attribute_definitions', $definition_data);
			$definition_data['definition_id'] = $definition_id;
		}

		$this->db->trans_complete();

 		$success &= $this->db->trans_status();

		return $success;
	}

	public function get_definition_by_name($definition_name, $definition_type)
	{
		$this->db->from('attribute_definitions');
		$this->db->where('definition_name', $definition_name);
		$this->db->where('definition_type', $definition_type);

		return $this->db->get()->row_object();
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
			$this->db->insert('attribute_links', array('attribute_id' => $attribute_id, 'item_id' => $item_id, 'definition_id' => $definition_id));
		}

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function delete_link($item_id)
	{
		$this->db->where('sale_id');
		$this->db->where('receiving_id');

		return $this->db->delete('attribute_links', array('item_id' => $item_id));
	}

	public function get_link_value($item_id, $definition_id)
	{
		$this->db->where('item_id', $item_id);
		$this->db->where('definition_id', $definition_id);
		$this->db->where('sale_id');
		$this->db->where('receiving_id');

		return $this->db->get('attribute_links')->row_object();
	}

	public function get_link_values($item_id, $sale_receiving_fk, $id, $definition_flags)
	{
			$this->db->select('GROUP_CONCAT(attribute_value SEPARATOR ", ") AS attribute_values, GROUP_CONCAT(attribute_datetime SEPARATOR ", ") AS attribute_datetimevalues');
		$this->db->from('attribute_links');
		$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
		$this->db->join('attribute_definitions', 'attribute_definitions.definition_id = attribute_links.definition_id');
		$this->db->where('definition_type <>', GROUP);
		$this->db->where('deleted', 0);

		if(!empty($id))
		{
			$this->db->where($sale_receiving_fk, $id);
		}
		else
		{
			$this->db->where('sale_id');
			$this->db->where('receiving_id');
		}
 		$this->db->where('item_id', (int) $item_id);
		$this->db->where('definition_flags & ', $definition_flags);

		$results = $this->db->get();

		if ($results->num_rows() > 0)
		{
			$row_object = $results->row_object();

			$datetime_values = explode(', ', $row_object->attribute_datetimevalues);
			$attribute_values = array();

			foreach (array_filter($datetime_values) as $datetime_value)
			{
				$attribute_values[] = to_datetime(strtotime($datetime_value));
			}

			return implode(',', $attribute_values) . $row_object->attribute_values;
		}
		return "";
	}

	public function get_attribute_value($item_id, $definition_id)
	{
		$this->db->from('attribute_values');
		$this->db->join('attribute_links', 'attribute_links.attribute_id = attribute_values.attribute_id');
		$this->db->where('definition_id', $definition_id);
		$this->db->where('sale_id');
		$this->db->where('receiving_id');
		$this->db->where('item_id', (int) $item_id);

		return $this->db->get()->row_object();
	}

	public function copy_attribute_links($item_id, $sale_receiving_fk, $id)
	{
		$this->db->query(
			'INSERT INTO ospos_attribute_links (item_id, definition_id, attribute_id, ' . $sale_receiving_fk . ') 
			  SELECT ' . $this->db->escape($item_id) . ', definition_id, attribute_id, ' . $this->db->escape($id) . '
			  FROM ' . $this->db->dbprefix('attribute_links') . ' 
			  WHERE item_id = ' . $this->db->escape($item_id) . ' AND sale_id IS NULL AND receiving_id IS NULL'
		);
	}

	public function get_suggestions($definition_id, $term)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('attribute_value, attribute_values.attribute_id');
		$this->db->from('attribute_definitions AS definition');
		$this->db->join('attribute_links', 'attribute_links.definition_id = definition.definition_id');
		$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id');
		$this->db->like('attribute_value', $term);
		$this->db->where('deleted', 0);
		$this->db->where('definition.definition_id', $definition_id);
		$this->db->order_by('attribute_value');
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

		if(empty($attribute_id) || empty($item_id))
		{
			if($definition_type != DATETIME)
			{
				$this->db->insert('attribute_values', array('attribute_value' => $attribute_value));
			}
			else
			{
				$this->db->insert('attribute_values', array('attribute_datetime' => date('Y-m-d H:i:s', strtotime($attribute_value))));
			}
			$attribute_id = $this->db->insert_id();

			$this->db->insert('attribute_links', array(
				'attribute_id' => empty($attribute_id) ? NULL : $attribute_id,
				'item_id' => empty($item_id) ? NULL : $item_id,
				'definition_id' => $definition_id));
		}
		else
		{
			$this->db->where('attribute_id', $attribute_id);
			$this->db->update('attribute_values', array('attribute_value' => $attribute_value));
		}

		$this->db->trans_complete();

		return $attribute_id;
	}

	public function delete_value($attribute_value, $definition_id)
	{
		return $this->db->query("DELETE atrv, atrl FROM " . $this->db->dbprefix('attribute_values') . " atrv, " . $this->db->dbprefix('attribute_links') .  " atrl " .
							"WHERE atrl.attribute_id = atrv.attribute_id AND atrv.attribute_value = " . $this->db->escape($attribute_value) . " AND atrl.definition_id = " . $this->db->escape($definition_id));
	}

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
}
