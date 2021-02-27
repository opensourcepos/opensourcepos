<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Tags extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('tags');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_tag_definition_manage_table_headers());

		$this->load->view('tags/manage', $data);
	}

	/**
	 * Returns customer table data rows. This will be called with AJAX.
	 */
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$tags = $this->Tag->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Tag->get_found_rows($search);

		$data_rows = array();
		foreach($tags->result() as $tag)
		{
			$tag->definition_flags = $this->_get_tags($tag->definition_flags);
			$data_rows[] = get_tag_definition_data_row($tag, $this);
		}

		$data_rows = $this->xss_clean($data_rows);

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function save_tag_value($tag_value)
	{
		$success = $this->Tag->save_value(urldecode($tag_value), $this->input->post('definition_id'), $this->input->post('person_id'), $this->input->post('tag_id'));

		echo json_encode(array('success' => $success != 0));
	}

	public function delete_tag_value($tag_value)
	{
		$success = $this->Tag->delete_value($tag_value, $this->input->post('definition_id'));

		echo json_encode(array('success' => $success));
	}

	public function save_definition($definition_id = NO_DEFINITION_ID)
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

		if($this->Tag->save_definition($definition_data, $definition_id))
		{
		//New definition
			if($definition_id == 0)
			{
				$definition_values = json_decode($this->input->post('definition_values'));

				foreach($definition_values as $definition_value)
				{
					$this->Tag->save_value($definition_value, $definition_data['definition_id']);
				}

				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('tags_definition_successful_adding').' '.
					$definition_name, 'id' => $definition_data['definition_id']));
			}
		//Existing definition
			else
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('tags_definition_successful_updating').' '.
					$definition_name, 'id' => $definition_id));
			}
		}
	//Failure
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('tags_definition_error_adding_updating', $definition_name), 'id' => -1));
		}
	}

	public function suggest_tag($definition_id)
	{
		$suggestions = $this->xss_clean($this->Tag->get_suggestions($definition_id, $this->input->get('term')));

		echo json_encode($suggestions);
	}

	public function get_row($row_id)
	{
		$tag_definition_info = $this->Tag->get_info($row_id);
		$tag_definition_info->definition_flags = $this->_get_tags($tag_definition_info->definition_flags);
		$data_row = $this->xss_clean(get_tag_definition_data_row($tag_definition_info));

		echo json_encode($data_row);
	}

	private function _get_tags($definition_flags = 0)
	{
		$definition_flag_names = array();
		foreach (Tag::get_definition_flags() as $id => $term)
		{
			if ($id & $definition_flags)
			{
				$definition_flag_names[$id] = $this->lang->line('tags_' . strtolower($term) . '_visibility');
			}
		}
		return $definition_flag_names;
	}

	public function view($definition_id = NO_DEFINITION_ID)
	{
		$info = $this->Tag->get_info($definition_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}

		$data['definition_id'] = $definition_id;
		$data['definition_values'] = $this->Tag->get_definition_values($definition_id);
		$data['definition_group'] = $this->Tag->get_definitions_by_type(GROUP, $definition_id);
		$data['definition_group'][''] = $this->lang->line('common_none_selected_text');
		$data['definition_info'] = $info;

		$show_all = Tag::SHOW_IN_CUSTOMERS;
		$data['definition_flags'] = $this->_get_tags($show_all);
		$selected_flags = $info->definition_flags === '' ? $show_all : $info->definition_flags;
		$data['selected_definition_flags'] = $this->_get_tags($selected_flags);

		$this->load->view("tags/form", $data);
	}

	public function delete_value($tag_id)
	{
		return $this->Tag->delete_value($tag_id);
	}

	public function delete()
	{
		$tags_to_delete = $this->input->post('ids');

		if($this->Tag->delete_definition_list($tags_to_delete))
		{
			$message = $this->lang->line('tags_definition_successful_deleted') . ' ' . count($tags_to_delete) . ' ' . $this->lang->line('tags_definition_one_or_multiple');
			echo json_encode(array('success' => TRUE, 'message' => $message));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('tags_definition_cannot_be_deleted')));
		}
	}
}