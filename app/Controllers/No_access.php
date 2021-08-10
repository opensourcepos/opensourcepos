<?php

namespace App\Controllers;

class No_Access extends BaseController
{
	public function index($module_id = '', $permission_id = '')
	{
		$data['module_name']   = $this->Module->get_module_name($module_id);
		$data['permission_id'] = $permission_id;
		
		$data = $this->security->xss_clean($data);
		
		echo view('no_access', $data);
	}
}
?>
