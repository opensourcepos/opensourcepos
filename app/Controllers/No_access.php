<?php

namespace App\Controllers;

use App\Models\Module;

/**
 * Part of the grants mechanism to restrict access to modules that the user doesn't have permission for.
 * Instantiated in the views.
 *
 * @property Module module
 */
class No_access extends BaseController
{
    private Module $module;

    public function __construct()
    {
        $this->module = model(Module::class);
    }

    public function getIndex(string $module_id = '', string $permission_id = ''): void
    {
        $data['module_name']   = $this->module->get_module_name($module_id);
        $data['permission_id'] = $permission_id;

        echo view('no_access', $data);
    }
}
