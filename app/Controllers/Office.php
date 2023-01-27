<?php

namespace App\Controllers;

use App\Models\Employee;

/**
 * @property employee employee
 */
class Office extends Secure_Controller
{
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
		$this->employee = model('Employee');

		$this->employee->logout();
	}
}