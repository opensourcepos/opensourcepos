<?php

namespace App\Controllers;

use App\Models\Employee;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * @property Employee employee
 */
class Office extends Secure_Controller
{
    protected Employee $employee;

    public function __construct()
    {
        parent::__construct('office', null, 'office');
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return view('home/office');
    }

    public function postLogout(): RedirectResponse
    {
        $this->employee = model(Employee::class);

        $this->employee->logout();

        return redirect()->to('login');
    }
}
