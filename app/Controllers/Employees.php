<?php

namespace App\Controllers;

use App\Models\Attribute;
use App\Models\Module;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Employees extends Persons
{
    protected Module $module;

    public function __construct()
    {
        parent::__construct('employees');

        $this->module = model('Module');
    }

    public function getSearch(): ResponseInterface
    {
        $search = $this->request->getGet('search');
        $limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort = $this->sanitizeSortColumn(person_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'people.person_id');
        $order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $employees = $this->employee->search($search, $limit, $offset, $sort, $order);
        $totalRows = $this->employee->get_found_rows($search);

        $dataRows = [];
        foreach ($employees->getResult() as $person) {
            $dataRows[] = get_person_data_row($person);
        }

        return $this->response->setJSON(['total' => $totalRows, 'rows' => $dataRows]);
    }

    public function getSuggest(): ResponseInterface
    {
        $search = $this->request->getGet('term');
        $suggestions = $this->employee->get_search_suggestions($search, 25, true);

        return $this->response->setJSON($suggestions);
    }

    public function suggestSearch(): ResponseInterface
    {
        $search = $this->request->getPost('term');
        $suggestions = $this->employee->get_search_suggestions($search);

        return $this->response->setJSON($suggestions);
    }

    public function getView(int $employeeId = NEW_ENTRY): string
    {
        $personInfo = $this->employee->get_info($employeeId);
        $currentUser = $this->employee->get_logged_in_employee_info();

        if ($employeeId != NEW_ENTRY && !$this->employee->canModifyEmployee($personInfo->person_id, $currentUser->person_id)) {
            header('Location: ' . base_url('no_access/employees/employees'));
            exit();
        }

        foreach (get_object_vars($personInfo) as $property => $value) {
            $personInfo->$property = $value;
        }
        $data['person_info'] = $personInfo;
        $data['employee_id'] = $employeeId;

        $modules = [];
        foreach ($this->module->get_all_modules()->getResult() as $module) {
            $module->grant = $this->employee->has_grant($module->module_id, $personInfo->person_id);
            $module->menu_group = $this->employee->get_menu_group($module->module_id, $personInfo->person_id);

            $modules[] = $module;
        }
        $data['all_modules'] = $modules;

        $permissions = [];
        foreach ($this->module->get_all_subpermissions()->getResult() as $permission) {
            $permission->permission_id = str_replace(' ', '_', $permission->permission_id);
            $permission->grant = $this->employee->has_grant($permission->permission_id, $personInfo->person_id);

            $permissions[] = $permission;
        }
        $data['all_subpermissions'] = $permissions;

        return view('employees/form', $data);
    }

    public function getAttributes(int $employeeId = NEW_ENTRY): string
    {
        return $this->getPersonAttributes($employeeId, Attribute::SHOW_IN_EMPLOYEES);
    }

    public function postSave(int $employeeId = NEW_ENTRY): ResponseInterface
    {
        $currentUser = $this->employee->get_logged_in_employee_info();

        if ($employeeId != NEW_ENTRY) {
            $targetEmployee = $this->employee->get_info($employeeId);
            if (!$this->employee->canModifyEmployee($targetEmployee->person_id, $currentUser->person_id)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Employees.error_updating_admin'),
                    'id'      => NEW_ENTRY
                ]);
            }
        }

        $firstName = $this->request->getPost('first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $lastName = $this->request->getPost('last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = strtolower($this->request->getPost('email', FILTER_SANITIZE_EMAIL));

        $firstName = $this->nameize($firstName);
        $lastName = $this->nameize($lastName);

        $personData = [
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'gender'       => $this->request->getPost('gender', FILTER_SANITIZE_NUMBER_INT),
            'email'        => $email,
            'phone_number' => $this->request->getPost('phone_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'address_1'    => $this->request->getPost('address_1', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'address_2'    => $this->request->getPost('address_2', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'city'         => $this->request->getPost('city', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'state'        => $this->request->getPost('state', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'zip'          => $this->request->getPost('zip', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'country'      => $this->request->getPost('country', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'comments'     => $this->request->getPost('comments', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        ];

        $grantsArray = [];
        $isAdmin = $this->employee->isAdmin($currentUser->person_id);

        foreach ($this->module->get_all_permissions()->getResult() as $permission) {
            $grants = [];
            $grant = $this->request->getPost('grant_' . $permission->permission_id) != null ? $this->request->getPost('grant_' . $permission->permission_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

            if ($grant == $permission->permission_id) {
                if (!$isAdmin && !$this->employee->has_grant($permission->permission_id, $currentUser->person_id)) {
                    continue;
                }
                $grants['permission_id'] = $permission->permission_id;
                $grants['menu_group'] = $this->request->getPost('menu_group_' . $permission->permission_id) != null ? $this->request->getPost('menu_group_' . $permission->permission_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '--';
                $grantsArray[] = $grants;
            }
        }

        if (!empty($this->request->getPost('password')) && ENVIRONMENT != 'testing') {
            $exploded = explode(":", $this->request->getPost('language', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $employeeData = [
                'username'      => $this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                'password'      => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'hash_version'  => 2,
                'language_code' => $exploded[0],
                'language'      => $exploded[1]
            ];
        } else {
            $exploded = explode(":", $this->request->getPost('language', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $employeeData = [
                'username'      => $this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                'language_code' => $exploded[0],
                'language'      => $exploded[1]
            ];
        }

        if ($this->employee->save_employee($personData, $employeeData, $grantsArray, $employeeId)) {
            $personId = $employeeId == NEW_ENTRY ? $employeeData['person_id'] : $employeeId;
            $this->savePersonAttributes($personId, Attribute::SHOW_IN_EMPLOYEES);

            if ($employeeId == NEW_ENTRY) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Employees.successful_adding') . ' ' . $firstName . ' ' . $lastName,
                    'id'      => $employeeData['person_id']
                ]);
            } else {
                $loggedInEmployeeId = session()->get('person_id');
                if ($employeeId == $loggedInEmployeeId) {
                    session()->set('language_code', $employeeData['language_code']);
                    session()->set('language', $employeeData['language']);
                }
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Employees.successful_updating') . ' ' . $firstName . ' ' . $lastName,
                    'id'      => $employeeId
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Employees.error_adding_updating') . ' ' . $firstName . ' ' . $lastName,
                'id'      => NEW_ENTRY
            ]);
        }
    }

    public function postDelete(): ResponseInterface
    {
        $employeesToDelete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $currentUser = $this->employee->get_logged_in_employee_info();

        if (!$this->employee->isAdmin($currentUser->person_id)) {
            foreach ($employeesToDelete as $empId) {
                if ($this->employee->isAdmin((int)$empId)) {
                    return $this->response->setJSON(['success' => false, 'message' => lang('Employees.error_deleting_admin')]);
                }
            }
        }

        if ($this->employee->delete_list($employeesToDelete)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Employees.successful_deleted') . ' ' . count($employeesToDelete) . ' ' . lang('Employees.one_or_multiple')
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => lang('Employees.cannot_be_deleted')]);
        }
    }

    public function getCheckUsername($employeeId): ResponseInterface
    {
        $exists = $this->employee->username_exists($employeeId, $this->request->getGet('username'));
        return $this->response->setJSON(!$exists ? 'true' : 'false');
    }
}