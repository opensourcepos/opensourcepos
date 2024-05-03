<?php

namespace App\Controllers;

use App\Models\Module;

/**
 * Part of the grants mechanism to restrict access to modules that the user doesn't have permission for.
 * Instantiated in the views.
 *
 * @property module module
 */
class No_access extends BaseController
{
	public function __construct()
	{
		$this->module = model('Module');
	}

	/**
	 * @param string $module_id
	 * @param string $permission_id
	 * @return void
	 */
	public function getIndex(string $module_id = '', string $permission_id = ''): void
	{
		$data['module_name']   = $this->module->get_module_name($module_id);
		$data['permission_id'] = $permission_id;

		echo view('no_access', $data);
	}
}
