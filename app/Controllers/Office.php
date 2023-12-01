<?php

namespace App\Controllers;

use App\Models\Employee;

/**
 * @property Employee employee
 */
class Office extends Secure_Controller
{
	protected Employee $employee;

	function __construct()
	{
		parent::__construct('office', NULL, 'office');
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
