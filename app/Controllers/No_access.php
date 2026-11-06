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
        if ($userInfo === false || $this->request->isAJAX()) {
            return view('no_access', $data);
        }

        $menuGroup = session()->get('menu_group');

        $allowedModules = $this->module->getNavigationModules(
            $userInfo->person_id,
            $menuGroup,
            $this->employee->has_module_grant('office', $userInfo->person_id)
        );

        $data['user_info']       = $userInfo;
        $data['allowed_modules'] = $allowedModules;
        $data['config']          = config(OSPOS::class)->settings;

        return view('partial/header', $data) . view('no_access', $data) . view('partial/footer');
    }
}
