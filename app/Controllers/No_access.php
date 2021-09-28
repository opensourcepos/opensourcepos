<?php

namespace App\Controllers;

use app\Models\Module;
use Config\Services;

/**
 *
 *
 * @property module module
 *
 * @property mixed security
 *
 */
class No_Access extends BaseController
{
	public function __construct()
	{
		$this->module = model("Module");

		$this->security = Services::security();
	}
	public function index(string $module_id = '', string $permission_id = '')
	{
		$data['module_name']   = $this->module->get_module_name($module_id);
		$data['permission_id'] = $permission_id;
		
		$data = $this->security->xss_clean($data);	//TODO: CI4 has no xss_clean because it's considered deprecated.  This needs to be replaced https://forum.codeigniter.com/thread-75338.html
		
		echo view('no_access', $data);
	}
}
?>
