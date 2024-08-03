<?php

namespace App\Controllers;

use App\Models\Attribute;
use Config\Services;

require_once('Secure_Controller.php');

/**
 * Attributes controls the custom attributes assigned to items
 **/
class Attributes extends Secure_Controller
{
	private Attribute $attribute;

	public function __construct()
	{
		parent::__construct('attributes');

		$this->attribute = model(Attribute::class);
	}

	/**
	 * Gets and sends the main view for Attributes to the browser.
	 *
	 * @return void
	 **/
	public function getIndex(): void
	{
		$data['table_headers'] = get_attribute_definition_manage_table_headers();

		echo view('attributes/manage', $data);
	}

	/**
	 * Returns attribute table data rows. This will be called with AJAX.
	 */
	public function getSearch(): void
	{
		$search = $this->request->getGet('search');
		$limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order  = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$attributes = $this->attribute->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->attribute->get_found_rows($search);

		$data_rows = [];
		foreach($attributes->getResult() as $attribute_row)
		{
			$attribute_row->definition_flags = $this->get_attributes($attribute_row->definition_flags);
			$data_rows[] = get_attribute_definition_data_row($attribute_row);
		}

		echo json_encode(['total' => $total_rows, 'rows' => $data_rows]);
	}

	/**
	 * AJAX called function which saves the attribute value sent via POST by using the model save function.
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postSaveAttributeValue(): void
	{
		$success = $this->attribute->save_value(
			html_entity_decode($this->request->getPost('attribute_value')),
			$this->request->getPost('definition_id', FILTER_SANITIZE_NUMBER_INT),
			$this->request->getPost('item_id', FILTER_SANITIZE_NUMBER_INT),
			$this->request->getPost('attribute_id', FILTER_SANITIZE_NUMBER_INT)
		);

		echo json_encode(['success' => $success != 0]);
	}

	/**
	 * AJAX called function deleting an attribute value using the model delete function.
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postDelete_attribute_value(): void
	{
		$success = $this->attribute->delete_value(
			html_entity_decode($this->request->getPost('attribute_value')),
			$this->request->getPost('definition_id', FILTER_SANITIZE_NUMBER_INT)
		);

		echo json_encode(['success' => $success]);
	}

	/**
	 * AJAX called function which saves the attribute definition.
	 *
	 * @param int $definition_id
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postSaveDefinition(int $definition_id = NO_DEFINITION_ID): void
	{
		$definition_flags = 0;

		$flags = (empty($this->request->getPost('definition_flags'))) ? [] : $this->request->getPost('definition_flags', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		foreach($flags as $flag)
		{
			$definition_flags |= $flag;
		}

		//Save definition data
		$definition_data = [
			'definition_name' => $this->request->getPost('definition_name'),
			'definition_unit' => $this->request->getPost('definition_unit') != '' ? $this->request->getPost('definition_unit') : null,
			'definition_flags' => $definition_flags,
			'definition_fk' => $this->request->getPost('definition_group') != '' ? $this->request->getPost('definition_group') : null
		];

		if ($this->request->getPost('definition_type') != null)
		{
			$definition_data['definition_type'] = DEFINITION_TYPES[$this->request->getPost('definition_type')];
		}

		$definition_name = $definition_data['definition_name'];

		if($this->attribute->save_definition($definition_data, $definition_id))
		{
			//New definition
			if($definition_id == NO_DEFINITION_ID)
			{
				$definition_values = json_decode(html_entity_decode($this->request->getPost('definition_values')));

				foreach($definition_values as $definition_value)
				{
					$this->attribute->save_value($definition_value, $definition_data['definition_id']);
				}

				echo json_encode([
					'success' => true,
					'message' => lang('Attributes.definition_successful_adding') . ' ' . $definition_name,
					'id' => $definition_data['definition_id']
				]);
			}
			//Existing definition
			else
			{
				echo json_encode([
					'success' => true,
					'message' => lang('Attributes.definition_successful_updating') . ' ' . $definition_name,
					'id' => $definition_id
				]);
			}
		}
		//Failure
		else
		{
			echo json_encode([
				'success' => false,
				'message' => lang('Attributes.definition_error_adding_updating', [$definition_name]),
				'id' => NEW_ENTRY
			]);
		}
	}

	/**
	 *
	 * @param int $definition_id
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function getSuggestAttribute(int $definition_id): void
	{
		$suggestions = $this->attribute->get_suggestions($definition_id, html_entity_decode($this->request->getGet('term')));

		echo json_encode($suggestions);
	}

	/**
	 * @param int $row_id
	 * @return void
	 */
	public function getRow(int $row_id): void
	{
		$attribute_definition_info = $this->attribute->get_info($row_id);
		$attribute_definition_info->definition_flags = $this->get_attributes($attribute_definition_info->definition_flags);
		$data_row = get_attribute_definition_data_row($attribute_definition_info);

		echo json_encode($data_row);
	}

	/**
	 * @param int $definition_flags
	 * @return array
	 */
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

	/**
	 * @param int $definition_id
	 * @return void
	 */
	public function getView(int $definition_id = NO_DEFINITION_ID): void
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

	/**
	 * AJAX called function to delete an attribute value. This is never called in the code. Perhaps it was boiler plate code that just isn't needed?
	 * @param int $attribute_id
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function delete_value(int $attribute_id): bool	//TODO: This function appears to never be used in the codebase.  Is it needed?
	{
		return $this->attribute->delete_value($attribute_id, NO_DEFINITION_ID);
	}

	/**
	 * Deletes an attribute definition
	 * @return void
	 */
	public function postDelete(): void
	{
		$attributes_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if($this->attribute->delete_definition_list($attributes_to_delete))
		{
			$message = lang('Attributes.definition_successful_deleted') . ' ' . count($attributes_to_delete) . ' ' . lang('Attributes.definition_one_or_multiple');
			echo json_encode(['success' => true, 'message' => $message]);
		}
		else
		{
			echo json_encode(['success' => false, 'message' => lang('Attributes.definition_cannot_be_deleted')]);
		}
	}
}
