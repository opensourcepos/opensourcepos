<?php

namespace App\Controllers;

use App\Models\Employee;

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

    public function getIndex(): void
    {
        echo view('home/office');
    }

    public function logout(): void
    {
        $this->employee = model(Employee::class);

        $this->employee->logout();
    }
}
