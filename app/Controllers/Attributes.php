<?php

namespace App\Controllers;

use App\Models\Attribute;
use CodeIgniter\HTTP\ResponseInterface;
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
     * @return string
     **/
    public function getIndex(): string
    {
        $data['table_headers'] = get_attribute_definition_manage_table_headers();

        return view('attributes/manage', $data);
    }

    /**
     * Returns attribute table data rows. This will be called with AJAX.
     */
    public function getSearch(): ResponseInterface
    {
        $search = $this->request->getGet('search');
        $limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort   = $this->sanitizeSortColumn(attribute_definition_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'definition_id');
        $order  = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $attributes = $this->attribute->search($search, $limit, $offset, $sort, $order);
        $total_rows = $this->attribute->get_found_rows($search);

        $data_rows = [];
        foreach ($attributes->getResult() as $attribute_row) {
            $attribute_row->definition_flags = $this->get_attributes($attribute_row->definition_flags);
            $data_rows[] = get_attribute_definition_data_row($attribute_row);
        }

        return $this->response->setJSON(['total' => $total_rows, 'rows' => $data_rows]);
    }

    /**
     * AJAX called function which saves the attribute value sent via POST by using the model save function.
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postSaveAttributeValue(): ResponseInterface
    {
        $success = $this->attribute->saveAttributeValue(
            html_entity_decode($this->request->getPost('attribute_value')),
            $this->request->getPost('definition_id', FILTER_SANITIZE_NUMBER_INT),
            $this->request->getPost('item_id', FILTER_SANITIZE_NUMBER_INT) ?? false,
            $this->request->getPost('attribute_id', FILTER_SANITIZE_NUMBER_INT) ?? false
        );

        return $this->response->setJSON(['success' => $success != 0]);
    }

    /**
     * AJAX called function deleting an attribute value using the model delete function.
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postDeleteDropdownAttributeValue(): ResponseInterface
    {
        $success = $this->attribute->deleteDropdownAttributeValue(
            html_entity_decode($this->request->getPost('attribute_value')),
            $this->request->getPost('definition_id', FILTER_SANITIZE_NUMBER_INT)
        );

        return $this->response->setJSON(['success' => $success]);
    }

    /**
     * AJAX called function which saves the attribute definition.
     *
     * @param int $definition_id
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postSaveDefinition(int $definition_id = NO_DEFINITION_ID): ResponseInterface
    {
        $definition_flags = 0;

        $flags = (empty($this->request->getPost('definition_flags'))) ? [] : $this->request->getPost('definition_flags', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        foreach ($flags as $flag) {
            $definition_flags |= $flag;
        }

        // Validate definition_group (definition_fk) foreign key
        $definition_group_input = $this->request->getPost('definition_group');
        $definition_fk = $this->validateDefinitionGroup($definition_group_input);

        if ($definition_fk === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Attributes.definition_invalid_group'),
                'id'      => NEW_ENTRY
            ]);
        }

        // Save definition data
        $definition_data = [
            'definition_name'  => $this->request->getPost('definition_name'),
            'definition_unit'  => $this->request->getPost('definition_unit') != '' ? $this->request->getPost('definition_unit') : null,
            'definition_flags' => $definition_flags,
            'definition_fk'    => $definition_fk
        ];

        if ($this->request->getPost('definition_type') != null) {
            $definition_data['definition_type'] = DEFINITION_TYPES[$this->request->getPost('definition_type')];
        }

        $definition_name = $definition_data['definition_name'];

        if ($this->attribute->saveDefinition($definition_data, $definition_id)) {
            // New definition
            if ($definition_id == NO_DEFINITION_ID) {
                $definition_values = json_decode(html_entity_decode($this->request->getPost('definition_values')));

                foreach ($definition_values as $definition_value) {
                    $this->attribute->saveAttributeValue($definition_value, $definition_data['definition_id']);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Attributes.definition_successful_adding') . ' ' . $definition_name,
                    'id'      => $definition_data['definition_id']
                ]);
            } else { // Existing definition
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Attributes.definition_successful_updating') . ' ' . $definition_name,
                    'id'      => $definition_id
                ]);
            }
        } else { // Failure
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Attributes.definition_error_adding_updating', [$definition_name]),
                'id'      => NEW_ENTRY
            ]);
        }
    }

    /**
     * Validates a definition_group foreign key.
     * Returns the validated integer ID, null if empty, or false if invalid.
     *
     * @param mixed $definition_group_input
     * @return int|null|false
     */
    private function validateDefinitionGroup(mixed $definition_group_input): int|null|false
    {
        if ($definition_group_input === '' || $definition_group_input === null) {
            return null;
        }

        $definition_group_id = (int) $definition_group_input;

        // Must be a positive integer, exist in attribute_definitions, and be of type GROUP
        if ($definition_group_id <= 0
            || !$this->attribute->exists($definition_group_id)
            || $this->attribute->getAttributeInfo($definition_group_id)->definition_type !== GROUP
        ) {
            return false;
        }

        return $definition_group_id;
    }

    /**
     *
     * @param int $definition_id
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function getSuggestAttribute(int $definition_id): ResponseInterface
    {
        $suggestions = $this->attribute->get_suggestions($definition_id, html_entity_decode($this->request->getGet('term')));

        return $this->response->setJSON($suggestions);
    }

    /**
     * @param int $row_id
     * @return ResponseInterface
     */
    public function getRow(int $row_id): ResponseInterface
    {
        $attribute_definition_info = $this->attribute->getAttributeInfo($row_id);
        $attribute_definition_info->definition_flags = $this->get_attributes($attribute_definition_info->definition_flags);
        $data_row = get_attribute_definition_data_row($attribute_definition_info);

        return $this->response->setJSON($data_row);
    }

    /**
     * @param int $definition_flags
     * @return array
     */
    private function get_attributes(int $definition_flags = 0): array
    {
        $definition_flag_names = [];
        foreach (Attribute::get_definition_flags() as $id => $term) {
            if ($id & $definition_flags) {
                $definition_flag_names[$id] = lang('Attributes.' . strtolower($term) . '_visibility');
            }
        }
        return $definition_flag_names;
    }

    /**
     * @param int $definition_id
     * @return string
     */
    public function getView(int $definition_id = NO_DEFINITION_ID): string
    {
        $info = $this->attribute->getAttributeInfo($definition_id);
        foreach (get_object_vars($info) as $property => $value) {
            $info->$property = $value;
        }

        $data['definition_id'] = $definition_id;
        $data['definition_values'] = $this->attribute->get_definition_values($definition_id);
        $data['definition_group'] = $this->attribute->get_definitions_by_type(GROUP, $definition_id);
        $data['definition_group'][''] = lang('Common.none_selected_text');
        $data['definition_info'] = $info;

        $show_all = Attribute::SHOW_IN_ITEMS | Attribute::SHOW_IN_RECEIVINGS | Attribute::SHOW_IN_SALES | Attribute::SHOW_IN_SEARCH;
        $data['definition_flags'] = $this->get_attributes($show_all);
        $selected_flags = $info->definition_flags === '' ? $show_all : $info->definition_flags;
        $data['selected_definition_flags'] = $this->get_attributes($selected_flags);

        return view('attributes/form', $data);
    }

    /**
     * Deletes an attribute definition
     * @return ResponseInterface
     */
    public function postDelete(): ResponseInterface
    {
        $attributes_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if($this->attribute->deleteDefinitionList($attributes_to_delete)) {
            $message = lang('Attributes.definition_successful_deleted') . ' ' . count($attributes_to_delete) . ' ' . lang('Attributes.definition_one_or_multiple');
            return $this->response->setJSON(['success' => true, 'message' => $message]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => lang('Attributes.definition_cannot_be_deleted')]);
        }
    }
}
