<?php

namespace App\Controllers;

class Home extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct(NULL, NULL, 'home');
	}

	public function index()
	{
		echo view('home/home');
	}

	public function logout()
	{
		$this->Employee->logout();
	}

	/*
	Load "change employee password" form
	*/
	public function change_password(int $employee_id = -1)	//TODO: Replace -1 with a constant
	{
		$person_info = $this->Employee->get_info($employee_id);
		foreach(get_object_vars($person_info) as $property => $value)
		{
			$person_info->$property = $this->xss_clean($value);
		}
		$data['person_info'] = $person_info;

		echo view('home/form_change_password', $data);
	}

	/*
	Change employee password
	*/
	public function save(int $employee_id = -1)	//TODO: Replace -1 with a constant
	{
		if($this->request->getPost('current_password') != '' && $employee_id != -1)
		{
			if($this->Employee->check_password($this->request->getPost('username'), $this->request->getPost('current_password')))
			{
				$employee_data = [
					'username' => $this->request->getPost('username'),
					'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
					'hash_version' => 2
				];

				if($this->Employee->change_password($employee_data, $employee_id))
				{
					echo json_encode ([
						'success' => TRUE,
						'message' => lang('Employees.successful_change_password'),
						'id' => $employee_id
					]);
				}
				else//failure
				{//TODO: Replace -1 with constant
					echo json_encode ([
						'success' => FALSE,
						'message' => lang('Employees.unsuccessful_change_password'),
						'id' => -1
					]);
				}
			}
			else
			{//TODO: Replace -1 with constant
				echo json_encode ([
					'success' => FALSE,
					'message' => lang('Employees.current_password_invalid'),
					'id' => -1
				]);
			}
		}
		else
		{//TODO: Replace -1 with constant
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Employees.current_password_invalid'),
				'id' => -1
			]);
		}
	}
}
?>
