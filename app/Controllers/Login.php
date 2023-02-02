<?php

namespace App\Controllers;

use App\Libraries\MY_Migration;
use App\Models\Employee;
use Config\Services;

/**
 * @property employee employee
 */
class Login extends BaseController
{
	protected $helpers = ['form'];

	public function index()
	{
		$this->employee = model('Employee');
		if(!$this->employee->is_logged_in())
		{
			$migration = new MY_Migration(config('Migrations'));
			$data = [
				'has_errors' => false,
				'is_latest' => $migration->is_latest(),
				'latest_version' => $migration->get_last_migration()
			];

			if(strtolower($this->request->getMethod()) !== 'post')
			{
				return view('login', $data);
			}

			$rules = ['username' => 'required|login_check[data]'];
			$messages = ['username' => lang('Login.invalid_username_and_password')];

			if(!$this->validate($rules, $messages))
			{
				$validation = Services::validation();
				$data['has_errors'] = !empty($validation->getErrors());

				return view('login', $data);
			}

		}
		echo "validated";
		return redirect()->to('home');
	}
}
