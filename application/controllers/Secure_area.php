<?php
class Secure_area extends CI_Controller 
{
	private $controller_name;
	
	/*
	Controllers that are considered secure extend Secure_area, optionally a $module_id can
	be set to also check if a user can access a particular module in the system.
	*/
	function __construct($module_id = null, $submodule_id = null)
	{
		parent::__construct();
		
		$this->load->model('Employee');

		if(!$this->Employee->is_logged_in())
		{
			redirect('login');
		}
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		if(!$this->Employee->has_module_grant($module_id,$employee_id) || 
			(isset($submodule_id) && !$this->Employee->has_module_grant($submodule_id,$employee_id)))
		{
			redirect('no_access/' . $module_id . '/' . $submodule_id);
		}
		
		//load up global data
		$logged_in_employee_info = $this->Employee->get_logged_in_employee_info();
		$data['allowed_modules'] = $this->Module->get_allowed_modules($logged_in_employee_info->person_id);
		$data['backup_allowed'] = false;
		foreach($data['allowed_modules']->result_array() as $module) 
		{
			$data['backup_allowed'] |= $module['module_id'] === 'config';
		}
		$data['user_info'] = $logged_in_employee_info;
		$data['controller_name'] = $module_id;
		$this->controller_name = $module_id;

		$this->load->vars($data);
	}
	
	public function get_controller_name()
	{
		return strtolower($this->controller_name);
	}
}
?>