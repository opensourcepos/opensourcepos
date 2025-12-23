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
     * @return void
     */
    public function getIndex(): ResponseInterface|string
    {
        return view('home/office');
    }

    /**
     * @return void
     */
    public function logout(): ResponseInterface|string
    {
        $this->employee = model(Employee::class);

        $this->employee->logout();
    }
}
