<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('home', null, 'home');
	}

	public function getIndex(): void
	{
		$logged_in = $this->employee->is_logged_in();
		echo view('home/home');
	}

	public function getLogout(): RedirectResponse
	{
		$this->employee->logout();
		return redirect()->to('login');
	}

	/**
	 * Load "change employee password" form
	 */
	public function change_password(int $employee_id = -1): void	//TODO: Replace -1 with a constant
	{
		$person_info = $this->employee->get_info($employee_id);
		foreach(get_object_vars($person_info) as $property => $value)
		{
			$person_info->$property = $value;
		}
		$data['person_info'] = $person_info;

		echo view('home/form_change_password', $data);
	}

	/**
	 * Change employee password
	 */
	public function save(int $employee_id = -1): void	//TODO: Replace -1 with a constant
	{
		if($this->request->getPost('current_password') != '' && $employee_id != -1)
		{
			if($this->employee->check_password($this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS), $this->request->getPost('current_password')))
			{
				$employee_data = [
					'username' => $this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
					'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
					'hash_version' => 2
				];

				if($this->employee->change_password($employee_data, $employee_id))
				{
					echo json_encode ([
						'success' => true,
						'message' => lang('Employees.successful_change_password'),
						'id' => $employee_id
					]);
				}
				else//failure
				{//TODO: Replace -1 with constant
					echo json_encode ([
						'success' => false,
						'message' => lang('Employees.unsuccessful_change_password'),
						'id' => -1
					]);
				}
			}
			else
			{//TODO: Replace -1 with constant
				echo json_encode ([
					'success' => false,
					'message' => lang('Employees.current_password_invalid'),
					'id' => -1
				]);
			}
		}
		else
		{//TODO: Replace -1 with constant
			echo json_encode ([
				'success' => false,
				'message' => lang('Employees.current_password_invalid'),
				'id' => -1
			]);
		}
	}
}
