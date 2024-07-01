<?php

namespace App\Controllers;

use App\Models\Module;
use Config\Services;

/**
 *
 *
 * @property module module
 *
 */
class Employees extends Persons
{
	public function __construct()
	{
		parent::__construct('employees');

		$this->module = model('Module');
	}

	/**
	 * Returns employee table data rows. This will be called with AJAX.
	 *
	 * @return void
	 */
	public function getSearch(): void
	{
		$search = $this->request->getGet('search');
		$limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order  = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$employees = $this->employee->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->employee->get_found_rows($search);

		$data_rows = [];
		foreach($employees->getResult() as $person)
		{
			$data_rows[] = get_person_data_row($person);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	/**
	 * AJAX called function gives search suggestions based on what is being searched for.
	 *
	 * @return void
	 */
	public function getSuggest(): void
	{
		$search = $this->request->getPost('term');
		$suggestions = $this->employee->get_search_suggestions($search, 25, true);

		echo json_encode($suggestions);
	}

	/**
	 * @return void
	 */
	public function suggest_search(): void
	{
		$search = $this->request->getPost('term');
		$suggestions = $this->employee->get_search_suggestions($search);

		echo json_encode($suggestions);
	}

	/**
	 * Loads the employee edit form
	 */
	public function getView(int $employee_id = NEW_ENTRY): void
	{
		$person_info = $this->employee->get_info($employee_id);
		foreach(get_object_vars($person_info) as $property => $value)
		{
			$person_info->$property = $value;
		}
		$data['person_info'] = $person_info;
		$data['employee_id'] = $employee_id;

		$modules = [];
		foreach($this->module->get_all_modules()->getResult() as $module)
		{
			$module->grant = $this->employee->has_grant($module->module_id, $person_info->person_id);
			$module->menu_group = $this->employee->get_menu_group($module->module_id, $person_info->person_id);

			$modules[] = $module;
		}
		$data['all_modules'] = $modules;

		$permissions = [];
		foreach($this->module->get_all_subpermissions()->getResult() as $permission)	//TODO: subpermissions does not follow naming standards.
		{
			$permission->permission_id = str_replace(' ', '_', $permission->permission_id);
			$permission->grant = $this->employee->has_grant($permission->permission_id, $person_info->person_id);

			$permissions[] = $permission;
		}
		$data['all_subpermissions'] = $permissions;

		echo view('employees/form', $data);
	}

	/**
	 * Inserts/updates an employee
	 */
	public function postSave(int $employee_id = NEW_ENTRY): void
	{
		$first_name = $this->request->getPost('first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);	//TODO: duplicated code
		$last_name = $this->request->getPost('last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$email = strtolower($this->request->getPost('email', FILTER_SANITIZE_EMAIL));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = [
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->request->getPost('gender', FILTER_SANITIZE_NUMBER_INT),
			'email' => $email,
			'phone_number' => $this->request->getPost('phone_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'address_1' => $this->request->getPost('address_1', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'address_2' => $this->request->getPost('address_2', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'city' => $this->request->getPost('city', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'state' => $this->request->getPost('state', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'zip' => $this->request->getPost('zip', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'country' => $this->request->getPost('country', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'comments' => $this->request->getPost('comments', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
		];

		$grants_array = [];
		foreach($this->module->get_all_permissions()->getResult() as $permission)
		{
			$grants = [];
			$grant = $this->request->getPost('grant_'.$permission->permission_id) != null ? $this->request->getPost('grant_' . $permission->permission_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

			if($grant == $permission->permission_id)
			{
				$grants['permission_id'] = $permission->permission_id;
				$grants['menu_group'] = $this->request->getPost('menu_group_'.$permission->permission_id) != null ? $this->request->getPost('menu_group_' . $permission->permission_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '--';
				$grants_array[] = $grants;
			}
		}

		//Password has been changed OR first time password set
		if(!empty($this->request->getPost('password')) && ENVIRONMENT != 'testing')
		{
			$exploded = explode(":", $this->request->getPost('language', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
			$employee_data = [
				'username' 	=> $this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
				'password' 	=> password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
				'hash_version' 	=> 2,
				'language_code' => $exploded[0],
				'language' 	=> $exploded[1]
			];
		}
		else //Password not changed
		{
			$exploded = explode(":", $this->request->getPost('language', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
			$employee_data = [
				'username' 	=> $this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
				'language_code'	=> $exploded[0],
				'language' 	=> $exploded[1]
			];
		}

		if($this->employee->save_employee($person_data, $employee_data, $grants_array, $employee_id))
		{
			// New employee
			if($employee_id == NEW_ENTRY)
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Employees.successful_adding') . ' ' . $first_name . ' ' . $last_name,
					'id' => $employee_data['person_id']
				]);
			}
			else // Existing employee
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Employees.successful_updating') . ' ' . $first_name . ' ' . $last_name,
					'id' => $employee_id
				]);
			}
		}
		else // Failure
		{
			echo json_encode ([
				'success' => false,
				'message' => lang('Employees.error_adding_updating') . ' ' . $first_name . ' ' . $last_name,
				'id' => NEW_ENTRY
			]);
		}
	}

	/**
	 * This deletes employees from the employees table
	 */
	public function postDelete(): void
	{
		$employees_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if($this->employee->delete_list($employees_to_delete))	//TODO: this is passing a string, but delete_list expects an array
		{
			echo json_encode ([
				'success' => true,
				'message' => lang('Employees.successful_deleted') . ' ' . count($employees_to_delete) . ' ' . lang('Employees.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => false, 'message' => lang('Employees.cannot_be_deleted')]);
		}
	}

	/**
	 * Checks an employee username against the database. Used in app\Views\employees\form.php
	 *
	 * @param $employee_id
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function getCheckUsername($employee_id): void
	{
		$exists = $this->employee->username_exists($employee_id, $this->request->getGet('username'));
		echo !$exists ? 'true' : 'false';
	}
}
