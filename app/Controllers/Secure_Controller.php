<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Module;

use CodeIgniter\Model;
use CodeIgniter\Session\Session;
use Config\Services;

/**
 * Controllers that are considered secure extend Secure_Controller, optionally a $module_id can
 * be set to also check if a user can access a particular module in the system.
 *
 * @property employee employee
 * @property module module
 * @property array global_view_data
 * @property session session
 *
 */
class Secure_Controller extends BaseController
{
	public array $global_view_data;
	public Model $employee;
	public Model $module;
	public Session $session;

	public function __construct(string $module_id = '', string $submodule_id = null, string $menu_group = null)
	{
		$this->employee = model(Employee::class);
		$this->module = model(Module::class);
		$config = config('OSPOS')->settings;
		$validation = Services::validation();

		if(!$this->employee->is_logged_in())
		{
			header("Location:".base_url('login'));
			exit();
		}

		$logged_in_employee_info = $this->employee->get_logged_in_employee_info();
		if(!$this->employee->has_module_grant($module_id, $logged_in_employee_info->person_id)
			|| (isset($submodule_id) && !$this->employee->has_module_grant($submodule_id, $logged_in_employee_info->person_id)))
		{
			redirect("no_access/$module_id/$submodule_id");
		}

		// load up global global_view_data visible to all the loaded views
		$this->session = session();
		if($menu_group == NULL)
		{
			$menu_group = $this->session->get('menu_group');
		}
		else
		{
			$this->session->set('menu_group', $menu_group);
		}

		$allowed_modules = $menu_group == 'home'
			? $this->module->get_allowed_home_modules($logged_in_employee_info->person_id)
			: $this->module->get_allowed_office_modules($logged_in_employee_info->person_id);

		foreach($allowed_modules->getResult() as $module)
		{
			$global_view_data['allowed_modules'][] = $module;
		}

		$global_view_data += [
			'user_info' => $logged_in_employee_info,
			'controller_name' => $module_id,
			'config' => $config
		];
		view('viewData', $global_view_data);
	}

	/**
	 * AJAX function used to confirm whether values sent in the request are numeric
	 * @return void
	 */
	public function getCheckNumeric()
	{
		$result = true;

		foreach($this->request->getVar(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS) as $value)
		{
			$result &= (int)parse_decimals($value);
		}

		echo $result !== false ? 'true' : 'false';
	}

	public function getConfig($key)
	{
		if (isset($config[$key]))
		{
			return $config[$key];
		}
	}

	// this is the basic set of methods most OSPOS Controllers will implement
	public function getIndex() { return false; }
	public function getSearch() { return false; }
	public function suggest_search() { return false; }
	public function getView(int $data_item_id = -1) { return false; }
	public function postSave(int $data_item_id = -1) { return false; }
	public function postDelete() { return false; }
}
