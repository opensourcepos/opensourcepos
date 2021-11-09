<?php

namespace App\Controllers;

use app\Models\Module;

/**
 *
 *
 * @property module module
 *
 */
class No_Access extends BaseController
{
	public function __construct()
	{
		$this->module = model("Module");
	}
	public function index(string $module_id = '', string $permission_id = ''): void
	{
		$data['module_name']   = $this->module->get_module_name($module_id);
		$data['permission_id'] = $permission_id;

		echo view('no_access', $data);
	}
}
?>
