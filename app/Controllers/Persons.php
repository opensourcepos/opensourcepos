<?php

namespace App\Controllers;

use App\Models\Attribute;
use App\Models\Person;
use CodeIgniter\HTTP\ResponseInterface;
use Config\OSPOS;
use Config\Services;
use function Tamtamchik\NameCase\str_name_case;

abstract class Persons extends Secure_Controller
{
    protected Person $person;
    protected Attribute $attribute;
    protected array $appConfig;

    public function __construct(?string $moduleId = null)
    {
        parent::__construct($moduleId);

        $this->person = model(Person::class);
        $this->attribute = model(Attribute::class);
        $this->appConfig = config(OSPOS::class)->settings;
    }

    public function getIndex(): string
    {
        $data['table_headers'] = get_people_manage_table_headers();

        return view('people/manage', $data);
    }

    public function getSuggest(): ResponseInterface
    {
        $search = $this->request->getGet('term');
        $suggestions = $this->person->get_search_suggestions($search);

        return $this->response->setJSON($suggestions);
    }

    public function getRow(int $rowId): ResponseInterface
    {
        $dataRow = get_person_data_row($this->person->get_info($rowId));

        return $this->response->setJSON($dataRow);
    }

    protected function getPersonAttributes(int $personId, int $definitionFlags): string
    {
        $data['person_id'] = $personId;
        $data['config'] = $this->appConfig;
        $definitionIds = json_decode($this->request->getGet('definition_ids') ?? '', true);
        $data['definition_values'] = $this->attribute->getAttributesByPerson($personId) + $this->attribute->get_values_by_definitions($definitionIds);
        $data['definition_names'] = $this->attribute->getDefinitionsByType(true, $definitionFlags);

        foreach ($data['definition_values'] as $definitionId => $definitionValue) {
            $attributeValue = $this->attribute->getPersonAttributeValue($personId, $definitionId);
            $attributeId = (empty($attributeValue) || empty($attributeValue->attribute_id)) ? null : $attributeValue->attribute_id;
            $values = &$data['definition_values'][$definitionId];
            $values['attribute_id'] = $attributeId;
            $values['attribute_value'] = $attributeValue;
            $values['selected_value'] = '';

            if ($definitionValue['definition_type'] === DROPDOWN) {
                $values['values'] = $this->attribute->get_definition_values($definitionId);
                $linkValue = $this->getPersonLinkValue($personId, $definitionId);
                $values['selected_value'] = (empty($linkValue)) ? '' : $linkValue->attribute_id;
            }

            if (!empty($definitionIds[$definitionId])) {
                $values['selected_value'] = $definitionIds[$definitionId];
            }

            unset($data['definition_names'][$definitionId]);
        }

        return view('attributes/person', $data);
    }

    private function getPersonLinkValue(int $personId, int $definitionId): ?object
    {
        $builder = $this->db->table('attribute_links');
        $builder->where('person_id', $personId);
        $builder->where('item_id', null);
        $builder->where('sale_id', null);
        $builder->where('receiving_id', null);
        $builder->where('definition_id', $definitionId);

        return $builder->get()->getRowObject();
    }

    protected function savePersonAttributes(int $personId, int $definitionFlags): void
    {
        $attributeLinks = $this->request->getPost('attribute_links') ?? [];
        $attributeIds = $this->request->getPost('attribute_ids') ?? [];

        $this->attribute->deletePersonAttributeLinks($personId);

        foreach ($attributeLinks as $definitionId => $attributeId) {
            $definitionInfo = $this->attribute->getAttributeInfo((int)$definitionId);
            $definitionType = $definitionInfo->definition_type;

            if ($definitionType !== DROPDOWN) {
                $attributeId = $this->attribute->savePersonAttributeValue(
                    $attributeId,
                    (int)$definitionId,
                    $personId,
                    $attributeIds[$definitionId] ?? false,
                    $definitionType
                );
            }

            $this->attribute->savePersonAttributeLink($personId, (int)$definitionId, (int)$attributeId);
        }
    }

    protected function nameize(string $input): string
    {
        $adjustedName = str_name_case($input);

        return preg_replace_callback('/&[a-zA-Z0-9#]+;/', function ($matches) {
            return strtolower($matches[0]);
        }, $adjustedName);
    }
}