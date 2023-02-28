<?php

namespace App\Controllers;

use App\Models\Attribute;

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

	public function getIndex(): void
	{
		$data['table_headers'] = get_attribute_definition_manage_table_headers();

		echo view('attributes/manage', $data);
	}

	/**
	 * Returns customer table data rows. This will be called with AJAX.
	 */
	public function search(): void
	{
		$search = $this->request->getGet('search', FILTER_SANITIZE_STRING);
		$limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getGet('sort', FILTER_SANITIZE_STRING);
		$order  = $this->request->getGet('order', FILTER_SANITIZE_STRING);

		$attributes = $this->attribute->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->attribute->get_found_rows($search);

		$data_rows = [];
		foreach($attributes->getResult() as $attribute)
		{
			$attribute->definition_flags = $this->get_attributes($attribute->definition_flags);
			$data_rows[] = get_attribute_definition_data_row($attribute);
		}

		echo json_encode(['total' => $total_rows, 'rows' => $data_rows]);
	}

	/**
	 * @return void
	 */
	public function save_attribute_value(): void
	{
		$success = $this->attribute->save_value(
			$this->request->getPost('attribute_value', FILTER_SANITIZE_STRING),
			$this->request->getPost('definition_id', FILTER_SANITIZE_NUMBER_INT),
			$this->request->getPost('item_id', FILTER_SANITIZE_NUMBER_INT),
			$this->request->getPost('attribute_id', FILTER_SANITIZE_NUMBER_INT)
		);

		echo json_encode(['success' => $success != 0]);
	}

	/**
	 * @return void
	 */
	public function delete_attribute_value(): void
	{
		$success = $this->attribute->delete_value(
			$this->request->getPost('attribute_value', FILTER_SANITIZE_STRING),
			$this->request->getPost('definition_id', FILTER_SANITIZE_NUMBER_INT)
		);

		echo json_encode(['success' => $success]);
	}

	/**
	 * @param int $definition_id
	 * @return void
	 */
	public function save_definition(int $definition_id = NO_DEFINITION_ID): void
	{
		$definition_flags = 0;

		$flags = (empty($this->request->getPost('definition_flags'))) ? [] : $this->request->getPost('definition_flags', FILTER_SANITIZE_STRING);

		foreach($flags as $flag)
		{
			$definition_flags |= $flag;
		}

	//Save definition data
		$definition_data = [
			'definition_name' => $this->request->getPost('definition_name', FILTER_SANITIZE_STRING),
			'definition_unit' => $this->request->getPost('definition_unit') != '' ? $this->request->getPost('definition_unit', FILTER_SANITIZE_STRING) : NULL,
			'definition_flags' => $definition_flags,
			'definition_fk' => $this->request->getPost('definition_group') != '' ? $this->request->getPost('definition_group', FILTER_SANITIZE_STRING) : NULL
		];

		if ($this->request->getPost('definition_type') != NULL)
		{
			$definition_data['definition_type'] = DEFINITION_TYPES[$this->request->getPost('definition_type', FILTER_SANITIZE_STRING)];
		}

		$definition_name = $definition_data['definition_name'];

		if($this->attribute->save_definition($definition_data, $definition_id))
		{
		//New definition
			if($definition_id == 0)
			{
				$definition_values = json_decode($this->request->getPost('definition_values', FILTER_SANITIZE_STRING));

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
				'message' => lang('Attributes.definition_error_adding_updating', ['definition_name' => $definition_name]),
				'id' => -1
			]);
		}
	}

	/**
	 * @param int $definition_id
	 * @return void
	 */
	public function suggest_attribute(int $definition_id): void
	{
		$suggestions = $this->attribute->get_suggestions($definition_id, $this->request->getGet('term', FILTER_SANITIZE_STRING));

		echo json_encode($suggestions);
	}

	public function get_row(int $row_id): void
	{
		$attribute_definition_info = $this->attribute->get_info($row_id);
		$attribute_definition_info->definition_flags = $this->get_attributes($attribute_definition_info->definition_flags);
		$data_row = get_attribute_definition_data_row($attribute_definition_info);

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
			$info->$property = $value;
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
		return $this->attribute->delete_value($attribute_id, NO_DEFINITION_ID);
	}

	public function delete(): void
	{
		$attributes_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_STRING);

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
