<?php
class No_Access extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function index($module_id = '', $permission_id = '')
	{
		$data['module_name']   = $this->Module->get_module_name($module_id);
		$data['permission_id'] = $permission_id;
		
		$data = $this->security->xss_clean($data);
		
		$this->load->view('no_access', $data);
	}
}
?>