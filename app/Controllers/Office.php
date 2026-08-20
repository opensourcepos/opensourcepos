<?php

namespace App\Controllers;

use App\Models\Employee;
use CodeIgniter\HTTP\ResponseInterface;

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

    /**
     * @return void
     */
    public function logout(): void
    {
        $this->employee = model(Employee::class);

        $this->employee->logout();
    }
}
