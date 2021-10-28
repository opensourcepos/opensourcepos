<?php

namespace App\Controllers;

use app\Models\Attribute;

require_once('Secure_Controller.php');

/**
 * Attributes controls the custom attributes assigned to items
 * 
 * @property attribute attribute
 * 
 */
class Attributes extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('attributes');

		$this->attribute = model('Attribute');
	}

	public function index(): void
	{
		$data['table_headers'] = $this->xss_clean(get_attribute_definition_manage_table_headers());

		echo view('attributes/manage', $data);
	}

	/**
	 * Returns customer table data rows. This will be called with AJAX.
	 */
	public function search(): void
	{
		$search = $this->request->getGet('search');
		$limit  = $this->request->getGet('limit');
		$offset = $this->request->getGet('offset');
		$sort   = $this->request->getGet('sort');
		$order  = $this->request->getGet('order');

		$attributes = $this->attribute->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->attribute->get_found_rows($search);

		$data_rows = [];
		foreach($attributes->getResult() as $attribute)
		{
			$attribute->definition_flags = $this->get_attributes($attribute->definition_flags);
			$data_rows[] = get_attribute_definition_data_row($attribute, $this);
		}

		$data_rows = $this->xss_clean($data_rows);

		echo json_encode(['total' => $total_rows, 'rows' => $data_rows]);
	}

	public function save_attribute_value(): void
	{
		$success = $this->attribute->save_value(
			$this->request->getPost('attribute_value'),
			$this->request->getPost('definition_id'),
			$this->request->getPost('item_id'),
			$this->request->getPost('attribute_id')
		);

		echo json_encode(['success' => $success != 0]);
	}

	public function delete_attribute_value(): void
	{
		$success = $this->attribute->delete_value(
			$this->request->getPost('attribute_value'),
			$this->request->getPost('definition_id')
		);

		echo json_encode(['success' => $success]);
	}

	public function save_definition(int $definition_id = NO_DEFINITION_ID): void
	{
		$definition_flags = 0;

		$flags = (empty($this->request->getPost('definition_flags'))) ? [] : $this->request->getPost('definition_flags');

		foreach($flags as $flag)
		{
			$definition_flags |= $flag;
		}

	//Save definition data
		$definition_data = [
			'definition_name' => $this->request->getPost('definition_name'),
			'definition_unit' => $this->request->getPost('definition_unit') != '' ? $this->request->getPost('definition_unit') : NULL,
			'definition_flags' => $definition_flags,
			'definition_fk' => $this->request->getPost('definition_group') != '' ? $this->request->getPost('definition_group') : NULL
		];

		if ($this->request->getPost('definition_type') != null)
		{
			$definition_data['definition_type'] = DEFINITION_TYPES[$this->request->getPost('definition_type')];
		}

		$definition_name = $this->xss_clean($definition_data['definition_name']);

		if($this->attribute->save_definition($definition_data, $definition_id))
		{
		//New definition
			if($definition_id == 0)
			{
				$definition_values = json_decode($this->request->getPost('definition_values'));

				foreach($definition_values as $definition_value)
				{
					$this->attribute->save_value($definition_value, $definition_data['definition_id']);
				}

				echo json_encode([
					'success' => TRUE,
					'message' => lang('Attributes.definition_successful_adding') . ' ' . $definition_name,
					'id' => $definition_data['definition_id']
				]);
			}
		//Existing definition
			else
			{
				echo json_encode([
					'success' => TRUE,
					'message' => lang('Attributes.definition_successful_updating') . ' ' . $definition_name,
					'id' => $definition_id
				]);
			}
		}
	//Failure
		else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => lang('Attributes.definition_error_adding_updating', $definition_name),
				'id' => -1
			]);
		}
	}

	public function suggest_attribute(int $definition_id): void
	{
		$suggestions = $this->xss_clean($this->attribute->get_suggestions($definition_id, $this->request->getGet('term')));

		echo json_encode($suggestions);
	}

	public function get_row(int $row_id): void
	{
		$attribute_definition_info = $this->attribute->get_info($row_id);
		$attribute_definition_info->definition_flags = $this->get_attributes($attribute_definition_info->definition_flags);
		$data_row = $this->xss_clean(get_attribute_definition_data_row($attribute_definition_info));

		echo json_encode($data_row);
	}

	private function get_attributes(int $definition_flags = 0): array
	{
		$definition_flag_names = [];
		foreach (Attribute::get_definition_flags() as $id => $term)
		{
			if ($id & $definition_flags)
			{
				$definition_flag_names[$id] = lang('Attributes.' . strtolower($term) . '_visibility');
			}
		}
		return $definition_flag_names;
	}

	public function view(int $definition_id = NO_DEFINITION_ID): void
	{
		$info = $this->attribute->get_info($definition_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}

		$data['definition_id'] = $definition_id;
		$data['definition_values'] = $this->attribute->get_definition_values($definition_id);
		$data['definition_group'] = $this->attribute->get_definitions_by_type(GROUP, $definition_id);
		$data['definition_group'][''] = lang('Common.none_selected_text');
		$data['definition_info'] = $info;

		$show_all = Attribute::SHOW_IN_ITEMS | Attribute::SHOW_IN_RECEIVINGS | Attribute::SHOW_IN_SALES;
		$data['definition_flags'] = $this->get_attributes($show_all);
		$selected_flags = $info->definition_flags === '' ? $show_all : $info->definition_flags;
		$data['selected_definition_flags'] = $this->get_attributes($selected_flags);

		echo view('attributes/form', $data);
	}

	public function delete_value(int $attribute_id): bool	//TODO: This function appears to never be used in the codebase.  Is it needed?
	{
		return $this->attribute->delete_value($attribute_id);	//TODO: It wants the required definition_id here... maybe making the definition_id default to NO_DEFINITION_ID when not provided?
	}

	public function delete(): void
	{
		$attributes_to_delete = $this->request->getPost('ids');

		if($this->attribute->delete_definition_list($attributes_to_delete))
		{
			$message = lang('Attributes.definition_successful_deleted') . ' ' . count($attributes_to_delete) . ' ' . lang('Attributes.definition_one_or_multiple');
			echo json_encode(['success' => TRUE, 'message' => $message]);
		}
		else
		{
			echo json_encode(['success' => FALSE, 'message' => lang('Attributes.definition_cannot_be_deleted')]);
		}
	}
}