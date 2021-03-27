<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Person_attribute class
 */

class Person_attribute extends CI_Model
{
	const SHOW_IN_CUSTOMERS = 1;
	const SHOW_IN_EMPLOYEES = 2;
	const SHOW_IN_SUPPLIERS = 4;

	public static function get_definition_flags()
	{
		$class = new ReflectionClass(__CLASS__);

		return array_flip($class->getConstants());
	}

	/*
	 Determines if a given definition_id is an person_attribute
	 */
	public function exists($definition_id, $deleted = FALSE)
	{
		$this->db->from('person_attribute_definitions');
		$this->db->where('definition_id', $definition_id);
		$this->db->where('deleted', $deleted);

		return ($this->db->get()->num_rows() == 1);
	}

	public function link_exists($person_id, $definition_id = FALSE)
	{
		$this->db->from('person_attribute_links');
		if(empty($definition_id))
		{
			$this->db->where('definition_id <>');
			$this->db->where('person_attribute_id');
		}
		else
		{
			$this->db->where('definition_id', $definition_id);
		}

		$this->db->where('person_id', $person_id);

		return ($this->db->get()->num_rows() > 0);
	}

	/*
	 Determines if a given person_attribute_value exists in the person_attribute_values table and returns the person_attribute_id if it does
	 */
	public function value_exists($person_attribute_value)
	{
		$this->db->distinct('person_attribute_id');
		$this->db->from('person_attribute_values');
		$this->db->where('person_attribute_value', $person_attribute_value);

		$query = $this->db->get();
		if ($query->num_rows() > 0)
		{
			return $query->row()->person_attribute_id;
		}
		return FALSE;
	}

	/*
	 Gets information about a particular person_attribute definition
	 */
	public function get_info($definition_id)
	{
		$this->db->select('parent_definition.definition_name AS definition_group, definition.*');
		$this->db->from('person_attribute_definitions AS definition');
		$this->db->join('person_attribute_definitions AS parent_definition', 'parent_definition.definition_id = definition.definition_fk', 'left');
		$this->db->where('definition.definition_id', $definition_id);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $person_id is NOT a customer
			$item_obj = new stdClass();

			//Get all the fields from customers table
			foreach($this->db->list_fields('person_attribute_definitions') as $field)
			{
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}

	/*
	 Performs a search on person_attribute definitions
	 */
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'definition.definition_name', $order = 'asc')
	{
		$this->db->select('parent_definition.definition_name AS definition_group, definition.*');
		$this->db->from('person_attribute_definitions AS definition');
		$this->db->join('person_attribute_definitions AS parent_definition', 'parent_definition.definition_id = definition.definition_fk', 'left');

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

	public function get_person_attributes_by_person($person_id)
	{
		$this->db->from('person_attribute_definitions');
		$this->db->join('person_attribute_links', 'person_attribute_links.definition_id = person_attribute_definitions.definition_id');
		$this->db->where('person_id', $person_id);
		$this->db->where('deleted', 0);
		$this->db->order_by('definition_name','ASC');

		$results = $this->db->get()->result_array();

		return $this->_to_array($results, 'definition_id');
	}

	public function get_values_by_definitions($definition_ids)
	{
		if(count($definition_ids ? : []))
		{
			$this->db->from('person_attribute_definitions');

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

	public function get_definitions_by_type($person_attribute_type, $definition_id = NO_DEFINITION_ID)
	{
		$this->db->from('person_attribute_definitions');
		$this->db->where('definition_type', $person_attribute_type);
		$this->db->where('deleted', 0);

		if($definition_id != CATEGORY_DEFINITION_ID)
		{
			$this->db->where('definition_id != ', $definition_id);
		}

		$this->db->where('definition_fk');
		$results = $this->db->get()->result_array();

		return $this->_to_array($results, 'definition_id', 'definition_name');
	}

	public function get_definitions_by_flags($definition_flags)
	{
		$this->db->from('person_attribute_definitions');
		$this->db->where('definition_flags &', $definition_flags);
		$this->db->where('deleted', 0);
		$this->db->where('definition_type <>', GROUP);
		$this->db->order_by('definition_id');
		$results = $this->db->get()->result_array();

		return $this->_to_array($results, 'definition_id', 'definition_name');
	}

	/**
	 * Returns an array of person_attribute definition names and IDs
	 *
	 * @param 	boolean		$groups		If FALSE does not return GROUP type person_attributes in the array
	 * @return	array					Array containing definition IDs, person_attribute names and -1 index with the local language '[SELECT]' line.
	 */
	public function get_definition_names($groups = TRUE)
	{
		$this->db->from('person_attribute_definitions');
		$this->db->where('deleted', 0);
		$this->db->order_by('definition_name','ASC');

		if($groups === FALSE)
		{
			$this->db->where_not_in('definition_type',GROUP);
		}

		$results = $this->db->get()->result_array();

		$definition_name = array(-1 => $this->lang->line('common_none_selected_text'));

		return $definition_name + $this->_to_array($results, 'definition_id', 'definition_name');
	}

	public function get_definition_values($definition_id)
	{
		$person_attribute_values = [];

		if($definition_id > 0 || $definition_id == CATEGORY_DEFINITION_ID)
		{
			$this->db->from('person_attribute_links');
			$this->db->join('person_attribute_values', 'person_attribute_values.person_attribute_id = person_attribute_links.person_attribute_id');
			$this->db->where('definition_id', $definition_id);
			$this->db->where('person_id');
			$this->db->order_by('person_attribute_value','ASC');

			$results = $this->db->get()->result_array();

			return $this->_to_array($results, 'person_attribute_id', 'person_attribute_value');
		}

		return $person_attribute_values;
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
		$this->db->from('person_attribute_definitions');
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

	private function check_data_validity($definition, $from, $to)
	{
		$success = FALSE;

		if($from === TEXT)
		{
			$this->db->select('person_id,person_attribute_value');
			$this->db->from('person_attribute_values');
			$this->db->join('person_attribute_links', 'person_attribute_values.person_attribute_id = person_attribute_links.person_attribute_id');
			$this->db->where('definition_id',$definition);
			$success = TRUE;

			if($to === DATE)
			{
				foreach($this->db->get()->result_array() as $row)
				{
					if(valid_date($row['person_attribute_value']) === FALSE)
					{
						log_message('ERROR', 'person_id: ' . $row['person_id'] . ' with person_attribute_value: ' . $row['person_attribute_value'] . ' cannot be converted to datetime');
						$success = FALSE;
					}
				}
			}
			else if($to === DECIMAL)
			{
				foreach($this->db->get()->result_array() as $row)
				{
					if(valid_decimal($row['person_attribute_value']) === FALSE)
					{
						log_message('ERROR', 'person_id: ' . $row['person_id'] . ' with person_attribute_value: ' . $row['person_attribute_value'] . ' cannot be converted to decimal');
						$success = FALSE;
					}
				}
			}
		}
		return $success;
	}

	private function convert_definition_type($definition_id, $from_type, $to_type)
	{
		$success = FALSE;

	//From TEXT
		if($from_type === TEXT)
		{
		//To DATETIME or DECIMAL
			if(in_array($to_type, [DATE, DECIMAL], TRUE))
			{
				$field = ($to_type === DATE ? 'person_attribute_date' : 'person_attribute_decimal');

				if($this->check_data_validity($definition_id, $from_type, $to_type))
				{
					$this->db->trans_start();

					$query = 'UPDATE ospos_person_attribute_values ';
					$query .= 'INNER JOIN ospos_person_attribute_links ';
					$query .= 'ON ospos_person_attribute_values.person_attribute_id = ospos_person_attribute_links.person_attribute_id ';
					$query .= 'SET '. $field .'= person_attribute_value, ';
					$query .= 'person_attribute_value = NULL ';
					$query .= 'WHERE definition_id = ' . $this->db->escape($definition_id);
					$success = $this->db->query($query);

					$this->db->trans_complete();
				}
			}

		//To DROPDOWN or CHECKBOX
			else if($to_type === DROPDOWN)
			{
				$success = TRUE;
			}
			else if($to_type === CHECKBOX)
			{
				$checkbox_person_attribute_values = $this->checkbox_person_attribute_values($definition_id);

				$this->db->trans_start();

				$query = 'UPDATE ospos_person_attribute_values values ';
				$query .= 'INNER JOIN ospos_person_attribute_links links ';
				$query .= 'ON values.person_attribute_id = links.person_attribute_id ';
				$query .= "SET links.person_attribute_id = IF((values.person_attribute_value IN('FALSE','0','') OR (values.person_attribute_value IS NULL)), $checkbox_person_attribute_values[0], $checkbox_person_attribute_values[1]) ";
				$query .= 'WHERE definition_id = ' . $this->db->escape($definition_id);
				$success = $this->db->query($query);

				$this->db->trans_complete();
			}
		}

	//From DROPDOWN
		else if($from_type === DROPDOWN)
		{
			//To TEXT
			if(in_array($to_type, [TEXT, CHECKBOX], TRUE))
			{
				$this->db->trans_start();

				$this->db->from('ospos_person_attribute_links');
				$this->db->where('definition_id',$definition_id);
				$this->db->where('person_id', NULL);
				$success = $this->db->delete();

				$this->db->trans_complete();

				//To CHECKBOX
				if($to_type === CHECKBOX)
				{
					$checkbox_person_attribute_values = $this->checkbox_person_attribute_values($definition_id);

					$this->db->trans_start();

					$query = 'UPDATE ospos_person_attribute_values vals ';
					$query .= 'INNER JOIN ospos_person_attribute_links links ';
					$query .= 'ON vals.person_attribute_id = links.person_attribute_id ';
					$query .= "SET links.person_attribute_id = IF((vals.person_attribute_value IN('FALSE','0','') OR (vals.person_attribute_value IS NULL)), $checkbox_person_attribute_values[0], $checkbox_person_attribute_values[1]) ";
					$query .= 'WHERE links.definition_id = ' . $this->db->escape($definition_id);
					$success = $this->db->query($query);

					$this->db->trans_complete();
				}
			}
		}

		//From any other type
		else
		{
			$success = TRUE;
		}

		return $success;
	}

	private function checkbox_person_attribute_values($definition_id)
	{
		$zero_person_attribute_id = $this->value_exists('0');
		$one_person_attribute_id = $this->value_exists('1');

		if($zero_person_attribute_id === FALSE)
		{
			$zero_person_attribute_id = $this->save_value('0', $definition_id, FALSE, FALSE, CHECKBOX);
		}

		if($one_person_attribute_id === FALSE)
		{
			$one_person_attribute_id = $this->save_value('1', $definition_id, FALSE, FALSE, CHECKBOX);
		}

		return array($zero_person_attribute_id, $one_person_attribute_id);
	}

	/*
	 Inserts or updates a definition
	 */
	public function save_definition(&$definition_data, $definition_id = NO_DEFINITION_ID)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		//Definition doesn't exist
		if($definition_id === NO_DEFINITION_ID || !$this->exists($definition_id))
		{
			if($this->exists($definition_id,TRUE))
			{
				$success = $this->undelete($definition_id);
			}
			else
			{
				$success = $this->db->insert('person_attribute_definitions', $definition_data);
				$definition_data['definition_id'] = $this->db->insert_id();
			}
		}

		//Definition already exists
		else
		{
			$this->db->select('definition_type, definition_name');
			$this->db->from('person_attribute_definitions');
			$this->db->where('definition_id', $definition_id);

			$row = $this->db->get()->row();
			$from_definition_type = $row->definition_type;
			$from_definition_name = $row->definition_name;
			$to_definition_type = $definition_data['definition_type'];

			if($from_definition_type !== $to_definition_type)
			{
				if(!$this->convert_definition_type($definition_id,$from_definition_type,$to_definition_type))
				{
					return FALSE;
				}
			}

			$this->db->where('definition_id', $definition_id);
			$success = $this->db->update('person_attribute_definitions', $definition_data);
			$definition_data['definition_id'] = $definition_id;
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	public function get_definition_by_name($definition_name, $definition_type = FALSE)
	{
		$this->db->from('person_attribute_definitions');
		$this->db->where('definition_name', $definition_name);
		if($definition_type != FALSE)
		{
			$this->db->where('definition_type', $definition_type);
		}

		return $this->db->get()->result_array();
	}

	public function save_link($person_id, $definition_id, $person_attribute_id)
	{
		$this->db->trans_start();

		if($this->link_exists($person_id, $definition_id))
		{
			$this->db->where('definition_id', $definition_id);
			$this->db->where('person_id', $person_id);
			$this->db->update('person_attribute_links', array('person_attribute_id' => $person_attribute_id));
		}
		else
		{
			$this->db->insert('person_attribute_links', array('person_attribute_id' => $person_attribute_id, 'person_id' => $person_id, 'definition_id' => $definition_id));
		}

		$this->db->trans_complete();

		return $this->db->trans_status();
	}


	public function delete_link($person_id)
	{

		return $this->db->delete('person_attribute_links', array('person_id' => $person_id));
	}

	public function get_link_value($person_id, $definition_id)
	{
		$this->db->where('person_id', $person_id);
		$this->db->where('definition_id', $definition_id);
		return $this->db->get('person_attribute_links')->row_object();
	}

	public function get_link_values($person_id, $definition_flags)
	{
		$format = $this->db->escape(dateformat_mysql());
		$this->db->select("GROUP_CONCAT(person_attribute_value SEPARATOR ', ') AS person_attribute_values");
		$this->db->select("GROUP_CONCAT(DATE_FORMAT(person_attribute_date, $format) SEPARATOR ', ') AS person_attribute_dtvalues");
		$this->db->from('person_attribute_links');
		$this->db->join('person_attribute_values', 'person_attribute_values.person_attribute_id = person_attribute_links.person_attribute_id');
		$this->db->join('person_attribute_definitions', 'person_attribute_definitions.definition_id = person_attribute_links.definition_id');
		$this->db->where('definition_type <>', GROUP);
		$this->db->where('deleted', 0);

		// if(!empty($id))
		// {
		// 	$this->db->where($sale_receiving_fk, $id);
		// }
		// else
		// {
		// 	$this->db->where('sale_id');
		// 	$this->db->where('receiving_id');
		// }

		$this->db->where('person_id', (int) $person_id);
		$this->db->where('definition_flags & ', $definition_flags);

		return $this->db->get();
	}

	public function get_person_attribute_value($person_id, $definition_id)
	{
		$this->db->from('person_attribute_values');
		$this->db->join('person_attribute_links', 'person_attribute_links.person_attribute_id = person_attribute_values.person_attribute_id');
		$this->db->where('definition_id', $definition_id);
		$this->db->where('person_id', (int) $person_id);

		return $this->db->get()->row_object();
	}

	public function copy_person_attribute_links($person_id, $id)
	{
		$this->db->query(
			'INSERT INTO ospos_person_attribute_links (person_id, definition_id, person_attribute_id)
			  SELECT ' . $this->db->escape($person_id) . ', definition_id, person_attribute_id, ' . $this->db->escape($id) . '
			  FROM ' . $this->db->dbprefix('person_attribute_links') . '
			  WHERE person_id = ' . $this->db->escape($person_id)
			);
	}

	public function get_suggestions($definition_id, $term)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('person_attribute_value, person_attribute_values.person_attribute_id');
		$this->db->from('person_attribute_definitions AS definition');
		$this->db->join('person_attribute_links', 'person_attribute_links.definition_id = definition.definition_id');
		$this->db->join('person_attribute_values', 'person_attribute_values.person_attribute_id = person_attribute_links.person_attribute_id');
		$this->db->like('person_attribute_value', $term);
		$this->db->where('deleted', 0);
		$this->db->where('definition.definition_id', $definition_id);
		$this->db->order_by('person_attribute_value','ASC');

		foreach($this->db->get()->result() as $row)
		{
			$row_array = (array) $row;
			$suggestions[] = array('value' => $row_array['person_attribute_id'], 'label' => $row_array['person_attribute_value']);
		}

		return $suggestions;
	}

	public function save_value($person_attribute_value, $definition_id, $person_id = FALSE, $person_attribute_id = FALSE, $definition_type = DROPDOWN)
	{
		$this->db->trans_start();

		//New Person_attribute
		if(empty($person_attribute_id) || empty($person_id))
		{
			if(in_array($definition_type, [TEXT, DROPDOWN, CHECKBOX], TRUE))
			{
				$person_attribute_id = $this->value_exists($person_attribute_value);

				if(empty($person_attribute_id))
				{
					$this->db->insert('person_attribute_values', array('person_attribute_value' => $person_attribute_value));
				}
			}
			else if($definition_type == DECIMAL)
			{
				$this->db->insert('person_attribute_values', array('person_attribute_decimal' => $person_attribute_value));
			}
			else
			{
				$this->db->insert('person_attribute_values', array('person_attribute_date' => date('Y-m-d', strtotime($person_attribute_value))));
			}

			$person_attribute_id = $person_attribute_id ? $person_attribute_id : $this->db->insert_id();

			$this->db->insert('person_attribute_links', array(
				'person_attribute_id' => empty($person_attribute_id) ? NULL : $person_attribute_id,
				'person_id' => empty($person_id) ? NULL : $person_id,
				'definition_id' => $definition_id));
		}

		//Existing Person_attribute
		else
		{
			$this->db->where('person_attribute_id', $person_attribute_id);

			if(in_array($definition_type, [TEXT, DROPDOWN], TRUE))
			{
				$this->db->update('person_attribute_values', array('person_attribute_value' => $person_attribute_value));
			}
			else if($definition_type == DECIMAL)
			{
				$this->db->update('person_attribute_values', array('person_attribute_decimal' => $person_attribute_value));
			}
			else
			{
				$this->db->update('person_attribute_values', array('person_attribute_date' => date('Y-m-d', strtotime($person_attribute_value))));
			}
		}

		$this->db->trans_complete();

		return $person_attribute_id;
	}

	public function delete_value($person_attribute_value, $definition_id)
	{
		return $this->db->query("DELETE atrv, atrl FROM " . $this->db->dbprefix('person_attribute_values') . " atrv, " . $this->db->dbprefix('person_attribute_links') .  " atrl " .
			"WHERE atrl.person_attribute_id = atrv.person_attribute_id AND atrv.person_attribute_value = " . $this->db->escape($person_attribute_value) . " AND atrl.definition_id = " . $this->db->escape($definition_id));
	}

	/**
	 * Deletes an Person_attribute definition from the database and associated column in the customer_import.csv
	 *
	 * @param	unknown	$definition_id	Person_attribute definition ID to remove.
	 * @return 	boolean					TRUE if successful and FALSE if there is a failure
	 */
	public function delete_definition($definition_id)
	{
		$this->db->where('definition_id', $definition_id);

		return $this->db->update('person_attribute_definitions', array('deleted' => 1));
	}

	public function delete_definition_list($definition_ids)
	{
		$this->db->where_in('definition_id', $definition_ids);

		return $this->db->update('person_attribute_definitions', array('deleted' => 1));
	}

	/*
	Undeletes one person_attribute definition
	*/
	public function undelete($definition_id)
	{
		$this->db->where('definition_id', $definition_id);

		return $this->db->update('person_attribute_definitions', array('deleted'=>0));
	}
}