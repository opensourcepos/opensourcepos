<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Module;

use CodeIgniter\Model;
use CodeIgniter\Session\Session;
use Config\OSPOS;
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
	protected Employee $employee;
	protected Module $module;
	protected Session $session;

	/**
	 * @param string $module_id
	 * @param string|null $submodule_id
	 * @param string|null $menu_group
	 */
	public function __construct(string $module_id = '', string $submodule_id = null, string $menu_group = null)
	{
		$this->employee = model(Employee::class);
		$this->module = model(Module::class);
		$config = config(OSPOS::class)->settings;
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
			header("Location:".base_url("no_access/$module_id/$submodule_id"));
			exit();
		}

		// load up global global_view_data visible to all the loaded views
		$this->session = session();
		if($menu_group == null)
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

		$this->global_view_data = [];
		foreach($allowed_modules->getResult() as $module)
		{
			$this->global_view_data['allowed_modules'][] = $module;
		}

		$this->global_view_data += [
			'user_info' => $logged_in_employee_info,
			'controller_name' => $module_id,
			'config' => $config
		];
		view('viewData', $this->global_view_data);
	}

	/**
	 * AJAX function used to confirm whether values sent in the request are numeric
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function getCheckNumeric(): void
	{
		$result = true;

		foreach($this->request->getGet(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS) as $value)
		{
			$result &= (int)parse_decimals($value);
		}

		echo $result !== false ? 'true' : 'false';
	}

	/**
	 * @param $key
	 * @return mixed|void
	 */
	public function getConfig($key)
	{
		if (isset($config[$key]))
		{
			return $config[$key];
		}
	}

	/**
	 * @return false
	 */
	public function getIndex() { return false; }

	/**
	 * @return false
	 */
	public function getSearch() { return false; }

	/**
	 * @return false
	 */
	public function suggest_search() { return false; }

	/**
	 * @param int $data_item_id
	 * @return false
	 */
	public function getView(int $data_item_id = -1) { return false; }

	/**
	 * @param int $data_item_id
	 * @return false
	 */
	public function postSave(int $data_item_id = -1) { return false; }

	/**
	 * @return false
	 */
	public function postDelete() { return false; }
}
