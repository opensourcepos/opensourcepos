<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Tag class
 */

class Tag extends CI_Model
{
	const SHOW_IN_CUSTOMERS = 1;

	public static function get_definition_flags()
	{
		$class = new ReflectionClass(__CLASS__);

		return array_flip($class->getConstants());
	}

	/*
	 Determines if a given definition_id is an tag
	 */
	public function exists($definition_id, $deleted = FALSE)
	{
		$this->db->from('tag_definitions');
		$this->db->where('definition_id', $definition_id);
		$this->db->where('deleted', $deleted);

		return ($this->db->get()->num_rows() == 1);
	}

	public function link_exists($customer_id, $definition_id = FALSE)
	{
		$this->db->from('tag_links');
		if(empty($definition_id))
		{
			$this->db->where('definition_id <>');
			$this->db->where('tag_id');
		}
		else
		{
			$this->db->where('definition_id', $definition_id);
		}

		$this->db->where('person_id', $customer_id);

		return ($this->db->get()->num_rows() > 0);
	}

	/*
	 Determines if a given tag_value exists in the tag_values table and returns the tag_id if it does
	 */
	public function value_exists($tag_value)
	{
		$this->db->distinct('tag_id');
		$this->db->from('tag_values');
		$this->db->where('tag_value', $tag_value);

		$query = $this->db->get();
		if ($query->num_rows() > 0)
		{
			return $query->row()->tag_id;
		}
		return FALSE;
	}

	/*
	 Gets information about a particular tag definition
	 */
	public function get_info($definition_id)
	{
		$this->db->select('parent_definition.definition_name AS definition_group, definition.*');
		$this->db->from('tag_definitions AS definition');
		$this->db->join('tag_definitions AS parent_definition', 'parent_definition.definition_id = definition.definition_fk', 'left');
		$this->db->where('definition.definition_id', $definition_id);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $customer_id is NOT a customer
			$item_obj = new stdClass();

			//Get all the fields from customers table
			foreach($this->db->list_fields('tag_definitions') as $field)
			{
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}

	/*
	 Performs a search on tag definitions
	 */
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'definition.definition_name', $order = 'asc')
	{
		$this->db->select('parent_definition.definition_name AS definition_group, definition.*');
		$this->db->from('tag_definitions AS definition');
		$this->db->join('tag_definitions AS parent_definition', 'parent_definition.definition_id = definition.definition_fk', 'left');

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

	public function get_tags_by_customer($customer_id)
	{
		$this->db->from('tag_definitions');
		$this->db->join('tag_links', 'tag_links.definition_id = tag_definitions.definition_id');
		$this->db->where('person_id', $customer_id);
		$this->db->where('deleted', 0);
		$this->db->order_by('definition_name','ASC');

		$results = $this->db->get()->result_array();

		return $this->_to_array($results, 'definition_id');
	}

	public function get_values_by_definitions($definition_ids)
	{
		if(count($definition_ids ? : []))
		{
			$this->db->from('tag_definitions');

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

	public function get_definitions_by_type($tag_type, $definition_id = NO_DEFINITION_ID)
	{
		$this->db->from('tag_definitions');
		$this->db->where('definition_type', $tag_type);
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
		$this->db->from('tag_definitions');
		$this->db->where('definition_flags &', $definition_flags);
		$this->db->where('deleted', 0);
		$this->db->where('definition_type <>', GROUP);
		$this->db->order_by('definition_id');
		$results = $this->db->get()->result_array();

		return $this->_to_array($results, 'definition_id', 'definition_name');
	}

	/**
	 * Returns an array of tag definition names and IDs
	 *
	 * @param 	boolean		$groups		If FALSE does not return GROUP type tags in the array
	 * @return	array					Array containing definition IDs, tag names and -1 index with the local language '[SELECT]' line.
	 */
	public function get_definition_names($groups = TRUE)
	{
		$this->db->from('tag_definitions');
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
		$tag_values = [];

		if($definition_id > 0 || $definition_id == CATEGORY_DEFINITION_ID)
		{
			$this->db->from('tag_links');
			$this->db->join('tag_values', 'tag_values.tag_id = tag_links.tag_id');
			$this->db->where('definition_id', $definition_id);
			$this->db->where('person_id');
			$this->db->order_by('tag_value','ASC');

			$results = $this->db->get()->result_array();

			return $this->_to_array($results, 'tag_id', 'tag_value');
		}

		return $tag_values;
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
		$this->db->from('tag_definitions');
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
			$this->db->select('person_id,tag_value');
			$this->db->from('tag_values');
			$this->db->join('tag_links', 'tag_values.tag_id = tag_links.tag_id');
			$this->db->where('definition_id',$definition);
			$success = TRUE;

			if($to === DATE)
			{
				foreach($this->db->get()->result_array() as $row)
				{
					if(valid_date($row['tag_value']) === FALSE)
					{
						log_message('ERROR', 'person_id: ' . $row['person_id'] . ' with tag_value: ' . $row['tag_value'] . ' cannot be converted to datetime');
						$success = FALSE;
					}
				}
			}
			else if($to === DECIMAL)
			{
				foreach($this->db->get()->result_array() as $row)
				{
					if(valid_decimal($row['tag_value']) === FALSE)
					{
						log_message('ERROR', 'person_id: ' . $row['person_id'] . ' with tag_value: ' . $row['tag_value'] . ' cannot be converted to decimal');
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
				$field = ($to_type === DATE ? 'tag_date' : 'tag_decimal');

				if($this->check_data_validity($definition_id, $from_type, $to_type))
				{
					$this->db->trans_start();

					$query = 'UPDATE ospos_tag_values ';
					$query .= 'INNER JOIN ospos_tag_links ';
					$query .= 'ON ospos_tag_values.tag_id = ospos_tag_links.tag_id ';
					$query .= 'SET '. $field .'= tag_value, ';
					$query .= 'tag_value = NULL ';
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
				$checkbox_tag_values = $this->checkbox_tag_values($definition_id);

				$this->db->trans_start();

				$query = 'UPDATE ospos_tag_values values ';
				$query .= 'INNER JOIN ospos_tag_links links ';
				$query .= 'ON values.tag_id = links.tag_id ';
				$query .= "SET links.tag_id = IF((values.tag_value IN('FALSE','0','') OR (values.tag_value IS NULL)), $checkbox_tag_values[0], $checkbox_tag_values[1]) ";
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

				$this->db->from('ospos_tag_links');
				$this->db->where('definition_id',$definition_id);
				$this->db->where('person_id', NULL);
				$success = $this->db->delete();

				$this->db->trans_complete();

				//To CHECKBOX
				if($to_type === CHECKBOX)
				{
					$checkbox_tag_values = $this->checkbox_tag_values($definition_id);

					$this->db->trans_start();

					$query = 'UPDATE ospos_tag_values vals ';
					$query .= 'INNER JOIN ospos_tag_links links ';
					$query .= 'ON vals.tag_id = links.tag_id ';
					$query .= "SET links.tag_id = IF((vals.tag_value IN('FALSE','0','') OR (vals.tag_value IS NULL)), $checkbox_tag_values[0], $checkbox_tag_values[1]) ";
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

	private function checkbox_tag_values($definition_id)
	{
		$zero_tag_id = $this->value_exists('0');
		$one_tag_id = $this->value_exists('1');

		if($zero_tag_id === FALSE)
		{
			$zero_tag_id = $this->save_value('0', $definition_id, FALSE, FALSE, CHECKBOX);
		}

		if($one_tag_id === FALSE)
		{
			$one_tag_id = $this->save_value('1', $definition_id, FALSE, FALSE, CHECKBOX);
		}

		return array($zero_tag_id, $one_tag_id);
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
				$success = $this->db->insert('tag_definitions', $definition_data);
				$definition_data['definition_id'] = $this->db->insert_id();
			}
		}

		//Definition already exists
		else
		{
			$this->db->select('definition_type, definition_name');
			$this->db->from('tag_definitions');
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
			$success = $this->db->update('tag_definitions', $definition_data);
			$definition_data['definition_id'] = $definition_id;
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	public function get_definition_by_name($definition_name, $definition_type = FALSE)
	{
		$this->db->from('tag_definitions');
		$this->db->where('definition_name', $definition_name);
		if($definition_type != FALSE)
		{
			$this->db->where('definition_type', $definition_type);
		}

		return $this->db->get()->result_array();
	}

	public function save_link($customer_id, $definition_id, $tag_id)
	{
		$this->db->trans_start();

		if($this->link_exists($customer_id, $definition_id))
		{
			$this->db->where('definition_id', $definition_id);
			$this->db->where('person_id', $customer_id);
			$this->db->update('tag_links', array('tag_id' => $tag_id));
		}
		else
		{
			$this->db->insert('tag_links', array('tag_id' => $tag_id, 'person_id' => $customer_id, 'definition_id' => $definition_id));
		}

		$this->db->trans_complete();

		return $this->db->trans_status();
	}


	public function delete_link($customer_id)
	{

		return $this->db->delete('tag_links', array('person_id' => $customer_id));
	}

	public function get_link_value($customer_id, $definition_id)
	{
		$this->db->where('person_id', $customer_id);
		$this->db->where('definition_id', $definition_id);
		return $this->db->get('tag_links')->row_object();
	}

	public function get_link_values($customer_id, $definition_flags)
	{
		$format = $this->db->escape(dateformat_mysql());
		$this->db->select("GROUP_CONCAT(tag_value SEPARATOR ', ') AS tag_values");
		$this->db->select("GROUP_CONCAT(DATE_FORMAT(tag_date, $format) SEPARATOR ', ') AS tag_dtvalues");
		$this->db->from('tag_links');
		$this->db->join('tag_values', 'tag_values.tag_id = tag_links.tag_id');
		$this->db->join('tag_definitions', 'tag_definitions.definition_id = tag_links.definition_id');
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

		$this->db->where('person_id', (int) $customer_id);
		$this->db->where('definition_flags & ', $definition_flags);

		return $this->db->get();
	}

	public function get_tag_value($customer_id, $definition_id)
	{
		$this->db->from('tag_values');
		$this->db->join('tag_links', 'tag_links.tag_id = tag_values.tag_id');
		$this->db->where('definition_id', $definition_id);
		$this->db->where('person_id', (int) $customer_id);

		return $this->db->get()->row_object();
	}

	public function copy_tag_links($customer_id, $id)
	{
		$this->db->query(
			'INSERT INTO ospos_tag_links (person_id, definition_id, tag_id)
			  SELECT ' . $this->db->escape($customer_id) . ', definition_id, tag_id, ' . $this->db->escape($id) . '
			  FROM ' . $this->db->dbprefix('tag_links') . '
			  WHERE person_id = ' . $this->db->escape($customer_id)
			);
	}

	public function get_suggestions($definition_id, $term)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('tag_value, tag_values.tag_id');
		$this->db->from('tag_definitions AS definition');
		$this->db->join('tag_links', 'tag_links.definition_id = definition.definition_id');
		$this->db->join('tag_values', 'tag_values.tag_id = tag_links.tag_id');
		$this->db->like('tag_value', $term);
		$this->db->where('deleted', 0);
		$this->db->where('definition.definition_id', $definition_id);
		$this->db->order_by('tag_value','ASC');

		foreach($this->db->get()->result() as $row)
		{
			$row_array = (array) $row;
			$suggestions[] = array('value' => $row_array['tag_id'], 'label' => $row_array['tag_value']);
		}

		return $suggestions;
	}

	public function save_value($tag_value, $definition_id, $customer_id = FALSE, $tag_id = FALSE, $definition_type = DROPDOWN)
	{
		$this->db->trans_start();

		//New Tag
		if(empty($tag_id) || empty($customer_id))
		{
			if(in_array($definition_type, [TEXT, DROPDOWN, CHECKBOX], TRUE))
			{
				$tag_id = $this->value_exists($tag_value);

				if(empty($tag_id))
				{
					$this->db->insert('tag_values', array('tag_value' => $tag_value));
				}
			}
			else if($definition_type == DECIMAL)
			{
				$this->db->insert('tag_values', array('tag_decimal' => $tag_value));
			}
			else
			{
				$this->db->insert('tag_values', array('tag_date' => date('Y-m-d', strtotime($tag_value))));
			}

			$tag_id = $tag_id ? $tag_id : $this->db->insert_id();

			$this->db->insert('tag_links', array(
				'tag_id' => empty($tag_id) ? NULL : $tag_id,
				'person_id' => empty($customer_id) ? NULL : $customer_id,
				'definition_id' => $definition_id));
		}

		//Existing Tag
		else
		{
			$this->db->where('tag_id', $tag_id);

			if(in_array($definition_type, [TEXT, DROPDOWN], TRUE))
			{
				$this->db->update('tag_values', array('tag_value' => $tag_value));
			}
			else if($definition_type == DECIMAL)
			{
				$this->db->update('tag_values', array('tag_decimal' => $tag_value));
			}
			else
			{
				$this->db->update('tag_values', array('tag_date' => date('Y-m-d', strtotime($tag_value))));
			}
		}

		$this->db->trans_complete();

		return $tag_id;
	}

	public function delete_value($tag_value, $definition_id)
	{
		return $this->db->query("DELETE atrv, atrl FROM " . $this->db->dbprefix('tag_values') . " atrv, " . $this->db->dbprefix('tag_links') .  " atrl " .
			"WHERE atrl.tag_id = atrv.tag_id AND atrv.tag_value = " . $this->db->escape($tag_value) . " AND atrl.definition_id = " . $this->db->escape($definition_id));
	}

	/**
	 * Deletes an Tag definition from the database and associated column in the customer_import.csv
	 *
	 * @param	unknown	$definition_id	Tag definition ID to remove.
	 * @return 	boolean					TRUE if successful and FALSE if there is a failure
	 */
	public function delete_definition($definition_id)
	{
		$this->db->where('definition_id', $definition_id);

		return $this->db->update('tag_definitions', array('deleted' => 1));
	}

	public function delete_definition_list($definition_ids)
	{
		$this->db->where_in('definition_id', $definition_ids);

		return $this->db->update('tag_definitions', array('deleted' => 1));
	}

	/*
	Undeletes one tag definition
	*/
	public function undelete($definition_id)
	{
		$this->db->where('definition_id', $definition_id);

		return $this->db->update('tag_definitions', array('deleted'=>0));
	}
}