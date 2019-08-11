<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Attributes extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('attributes');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_attribute_definition_manage_table_headers());

		$this->load->view('attributes/manage', $data);
	}

	/*
	Returns customer table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$attributes = $this->Attribute->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Attribute->get_found_rows($search);

		$data_rows = array();
		foreach($attributes->result() as $attribute)
		{
			$attribute->definition_flags = $this->_get_attributes($attribute->definition_flags);
			$data_rows[] = get_attribute_definition_data_row($attribute, $this);
		}

		$data_rows = $this->xss_clean($data_rows);

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function save_attribute_value($attribute_value)
	{
 		$success = $this->Attribute->save_value(urldecode($attribute_value), $this->input->post('definition_id'), $this->input->post('item_id'), $this->input->post('attribute_id'));

		echo json_encode(array('success' => $success != 0));
	}

	public function delete_attribute_value($attribute_value)
	{
		$success = $this->Attribute->delete_value($attribute_value, $this->input->post('definition_id'));

		echo json_encode(array('success' => $success));
	}

	public function save_definition($definition_id = -1)
	{

		$definition_flags = 0;

		$flags = (empty($this->input->post('definition_flags'))) ? array() : $this->input->post('definition_flags');

		foreach($flags as $flag)
		{
			$definition_flags |= $flag;
		}

		//Save definition data
		$definition_data = array(
			'definition_name' => $this->input->post('definition_name'),
			'definition_unit' => $this->input->post('definition_unit') != '' ? $this->input->post('definition_unit') : NULL,
			'definition_flags' => $definition_flags,
			'definition_fk' => $this->input->post('definition_group') != '' ? $this->input->post('definition_group') : NULL
		);

		if ($this->input->post('definition_type') != null)
		{
			$definition_data['definition_type'] = DEFINITION_TYPES[$this->input->post('definition_type')];
		}

		$definition_name = $this->xss_clean($definition_data['definition_name']);

		if($this->Attribute->save_definition($definition_data, $definition_id))
		{
			//New definition
			if($definition_id == -1)
			{
				$definition_values = json_decode($this->input->post('definition_values'));

				foreach($definition_values as $definition_value)
				{
					$this->Attribute->save_value($definition_value, $definition_data['definition_id']);
				}

				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('attributes_definition_successful_adding').' '.
                    $definition_name, 'id' => $definition_data['definition_id']));
			}
			else //Existing definition
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('attributes_definition_successful_updating').' '.
                    $definition_name, 'id' => $definition_id));
			}
		}
		else//failure
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('attributes_definition_error_adding_updating', $definition_name), 'id' => -1));
		}
	}

	public function suggest_attribute($definition_id)
	{
		$suggestions = $this->xss_clean($this->Attribute->get_suggestions($definition_id, $this->input->get('term')));

		echo json_encode($suggestions);
	}

	public function get_row($row_id)
	{
		$attribute_definition_info = $this->Attribute->get_info($row_id);
		$attribute_definition_info->definition_flags = $this->_get_attributes($attribute_definition_info->definition_flags);
		$data_row = $this->xss_clean(get_attribute_definition_data_row($attribute_definition_info));

		echo json_encode($data_row);
	}

	private function _get_attributes($definition_flags = 0)
	{
		$definition_flag_names = array();
		foreach (Attribute::get_definition_flags() as $id => $term)
		{
			if ($id & $definition_flags)
			{
				$definition_flag_names[$id] = $this->lang->line('attributes_' . strtolower($term) . '_visibility');
			}
		}
		return $definition_flag_names;
	}

	public function view($definition_id = -1)
	{
		$info = $this->Attribute->get_info($definition_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}

		$data['definition_id'] = $definition_id;
		$data['definition_values'] = $this->Attribute->get_definition_values($definition_id);
		$data['definition_group'] = $this->Attribute->get_definitions_by_type(GROUP, $definition_id);
		$data['definition_group'][''] = $this->lang->line('common_none_selected_text');
		$data['definition_info'] = $info;

		$show_all = Attribute::SHOW_IN_ITEMS | Attribute::SHOW_IN_RECEIVINGS | Attribute::SHOW_IN_SALES;
		$data['definition_flags'] = $this->_get_attributes($show_all);
		$selected_flags = $info->definition_flags === '' ? $show_all : $info->definition_flags;
		$data['selected_definition_flags'] = $this->_get_attributes($selected_flags);

		$this->load->view("attributes/form", $data);
	}

	public function delete_value($attribute_id)
	{
		return $this->Attribute->delete_value($attribute_id);
	}

	public function delete()
	{
		$attributes_to_delete = $this->input->post('ids');

		if($this->Attribute->delete_definition_list($attributes_to_delete))
		{
			$message = $this->lang->line('attributes_definition_successful_deleted') . ' ' . count($attributes_to_delete) . ' ' . $this->lang->line('attributes_definition_one_or_multiple');
			echo json_encode(array('success' => TRUE, 'message' => $message));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('attributes_definition_cannot_be_deleted')));
		}
	}

}
