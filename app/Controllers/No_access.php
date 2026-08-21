<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Module;
use CodeIgniter\HTTP\ResponseInterface;
use Config\OSPOS;

/**
 * Part of the grants mechanism to restrict access to modules that the user doesn't have permission for.
 * Instantiated in the views.
 *
 * @property module module
 */
class No_access extends BaseController
{
    private Employee $employee;
    private Module $module;

    public function __construct()
    {
        $this->employee = model(Employee::class);
        $this->module = model(Module::class);
    }

    /**
     * @param string $module_id
     * @param string $permission_id
     * @return string
     */
    public function getIndex(string $module_id = '', string $permission_id = ''): string
    {
        $data['module_name']   = $this->module->get_module_name($module_id);
        $data['permission_id'] = $permission_id;

        $userInfo = $this->employee->get_logged_in_employee_info();
        if ($userInfo === false) {
            return view('no_access', $data);
        }

        $menuGroup = session()->get('menu_group');
        $allowedModules = $menuGroup == 'home'
            ? $this->module->get_allowed_home_modules($userInfo->person_id)
            : $this->module->get_allowed_office_modules($userInfo->person_id);

        $data['user_info']       = $userInfo;
        $data['allowed_modules'] = $allowedModules->getResult();
        $data['config']          = config(OSPOS::class)->settings;

        return view('partial/header', $data) . view('no_access', $data) . view('partial/footer');
    }
}
