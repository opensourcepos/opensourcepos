<?php

namespace App\Controllers;

use app\Models\Module;

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

	/*
	Returns employee table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->request->getGet('search');
		$limit  = $this->request->getGet('limit');
		$offset = $this->request->getGet('offset');
		$sort   = $this->request->getGet('sort');
		$order  = $this->request->getGet('order');

		$employees = $this->Employee->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Employee->get_found_rows($search);

		$data_rows = [];
		foreach($employees->getResult() as $person)
		{
			$data_rows[] = $this->xss_clean(get_person_data_row($person));
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Employee->get_search_suggestions($this->request->getGet('term'), TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Employee->get_search_suggestions($this->request->getPost('term')));

		echo json_encode($suggestions);
	}

	/*
	Loads the employee edit form
	*/
	public function view(int $employee_id = -1)//TODO: Replace -1 with a constant
	{
		$person_info = $this->Employee->get_info($employee_id);
		foreach(get_object_vars($person_info) as $property => $value)
		{
			$person_info->$property = $this->xss_clean($value);
		}
		$data['person_info'] = $person_info;
		$data['employee_id'] = $employee_id;

		$modules = [];
		foreach($this->module->get_all_modules()->getResult() as $module)
		{
			$module->module_id = $this->xss_clean($module->module_id);
			$module->grant = $this->xss_clean($this->Employee->has_grant($module->module_id, $person_info->person_id));
			$module->menu_group = $this->xss_clean($this->Employee->get_menu_group($module->module_id, $person_info->person_id));

			$modules[] = $module;
		}
		$data['all_modules'] = $modules;

		$permissions = [];
		foreach($this->module->get_all_subpermissions()->getResult() as $permission)	//TODO: subpermissions does not follow naming standards.
		{
			$permission->module_id = $this->xss_clean($permission->module_id);
			$permission->permission_id = str_replace(' ', '_', $this->xss_clean($permission->permission_id));
			$permission->grant = $this->xss_clean($this->Employee->has_grant($permission->permission_id, $person_info->person_id));

			$permissions[] = $permission;
		}
		$data['all_subpermissions'] = $permissions;

		echo view('employees/form', $data);
	}

	/*
	Inserts/updates an employee
	*/
	public function save(int $employee_id = -1)	//TODO: Replace -1 with a constant
	{
		$first_name = $this->xss_clean($this->request->getPost('first_name'));	//TODO: duplicated code
		$last_name = $this->xss_clean($this->request->getPost('last_name'));
		$email = $this->xss_clean(strtolower($this->request->getPost('email')));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = [
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->request->getPost('gender'),
			'email' => $email,
			'phone_number' => $this->request->getPost('phone_number'),
			'address_1' => $this->request->getPost('address_1'),
			'address_2' => $this->request->getPost('address_2'),
			'city' => $this->request->getPost('city'),
			'state' => $this->request->getPost('state'),
			'zip' => $this->request->getPost('zip'),
			'country' => $this->request->getPost('country'),
			'comments' => $this->request->getPost('comments')
		];

		$grants_array = [];
		foreach($this->module->get_all_permissions()->getResult() as $permission)
		{
			$grants = [];
			$grant = $this->request->getPost('grant_'.$permission->permission_id) != NULL ? $this->request->getPost('grant_'.$permission->permission_id) : '';
			if($grant == $permission->permission_id)
			{
				$grants['permission_id'] = $permission->permission_id;
				$grants['menu_group'] = $this->request->getPost('menu_group_'.$permission->permission_id) != NULL ? $this->request->getPost('menu_group_'.$permission->permission_id) : '--';
				$grants_array[] = $grants;
			}
		}

		//Password has been changed OR first time password set
		if($this->request->getPost('password') != '' && ENVIRONMENT != 'testing')
		{
			$exploded = explode(":", $this->request->getPost('language'));
			$employee_data = [
				'username' 	=> $this->request->getPost('username'),
				'password' 	=> password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
				'hash_version' 	=> 2,
				'language_code' => $exploded[0],
				'language' 	=> $exploded[1]
			];
		}
		else //Password not changed
		{
			$exploded = explode(":", $this->request->getPost('language'));
			$employee_data = [
				'username' 	=> $this->request->getPost('username'),
				'language_code'	=> $exploded[0],
				'language' 	=> $exploded[1]
			];
		}

		if($this->Employee->save_employee($person_data, $employee_data, $grants_array, $employee_id))
		{
			// New employee
			if($employee_id == -1)
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Employees.successful_adding') . ' ' . $first_name . ' ' . $last_name,
					'id' => $this->xss_clean($employee_data['person_id'])
				]);
			}
			else // Existing employee
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Employees.successful_updating') . ' ' . $first_name . ' ' . $last_name,
					'id' => $employee_id
				]);
			}
		}
		else // Failure
		{
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Employees.error_adding_updating') . ' ' . $first_name . ' ' . $last_name,
				'id' => -1
			]);
		}
	}

	/*
	This deletes employees from the employees table
	*/
	public function delete()
	{
		$employees_to_delete = $this->xss_clean($this->request->getPost('ids'));

		if($this->Employee->delete_list($employees_to_delete))
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Employees.successful_deleted') . ' ' . count($employees_to_delete) . ' ' . lang('Employees.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Employees.cannot_be_deleted')]);
		}
	}

	public function check_username($employee_id)
	{
		$exists = $this->Employee->username_exists($employee_id, $this->request->getGet('username'));
		echo !$exists ? 'true' : 'false';
	}
}
//TODO: PSR-2 Coding standard says that we need to ditch the closing tag when the file only contains PHP
?>