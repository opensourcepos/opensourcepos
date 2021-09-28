<?php

namespace App\Controllers;

use app\Models\Employee;

/**
 *
 *
 * @property employee employee
 *
 */
class Office extends Secure_Controller
{
	function __construct()
	{
		parent::__construct('office', NULL, 'office');

		$this->employee = model('Employee');
	}

	public function index()
	{
		echo view('home/office');
	}

	public function logout()
	{
		$this->employee->logout();
	}
}
?>
