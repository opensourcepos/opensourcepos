<?php

namespace App\Controllers;

use app\Models\Employee;
use app\Models\Module;

use CodeIgniter\Session\Session;

/**
 * Controllers that are considered secure extend Secure_Controller, optionally a $module_id can
 * be set to also check if a user can access a particular module in the system.
 *
 * @property employee employee
 * @property module module
 * 
 * @property session session
 *
 */
class Secure_Controller extends BaseController
{
	public function __construct(string $module_id = NULL, string $submodule_id = NULL, string $menu_group = NULL)
	{
		$this->employee = model('Employee');
		$this->module = model('Module');

		if(!$this->employee->is_logged_in())
		{
			redirect('login');
		}

		$logged_in_employee_info = $this->employee->get_logged_in_employee_info();
		if(!$this->employee->has_module_grant($module_id, $logged_in_employee_info->person_id)
			|| (isset($submodule_id) && !$this->employee->has_module_grant($submodule_id, $logged_in_employee_info->person_id)))
		{
			redirect("no_access/$module_id/$submodule_id");
		}

		// load up global data visible to all the loaded views
		$this->session = session();
		if($menu_group == NULL)
		{
			$menu_group = $this->session->get('menu_group');
		}
		else
		{
			$this->session->set('menu_group', $menu_group);
		}

		if($menu_group == 'home')	//TODO: Convert to ternary notation
		{
			$allowed_modules = $this->module->get_allowed_home_modules($logged_in_employee_info->person_id);
		}
		else
		{
			$allowed_modules = $this->module->get_allowed_office_modules($logged_in_employee_info->person_id);
		}

		foreach($allowed_modules->getResult() as $module)
		{
			$data['allowed_modules'][] = $module;
		}

		$data['user_info'] = $logged_in_employee_info;
		$data['controller_name'] = $module_id;

		$this->load->vars($data);	//TODO: need to find out how to convert this.
	}

	public function check_numeric()
	{
		$result = TRUE;

		foreach($this->request->getGet(NULL, FILTER_SANITIZE_STRING) as $str)
		{
			$result &= parse_decimals($str);
		}

		echo $result !== FALSE ? 'true' : 'false';
	}

	// this is the basic set of methods most OSPOS Controllers will implement
	public function index() { return FALSE; }
	public function search() { return FALSE; }
	public function suggest_search() { return FALSE; }
	public function view(int $data_item_id = -1) { return FALSE; }
	public function save(int $data_item_id = -1) { return FALSE; }
	public function delete() { return FALSE; }
}