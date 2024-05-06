<?php

namespace App\Controllers;

use App\Libraries\MY_Migration;
use App\Models\Employee;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Model;
use Config\OSPOS;
use Config\Services;

/**
 * @property employee employee
 */
class Login extends BaseController
{
	public Model $employee;

	/**
	 * @return RedirectResponse|string
	 */
	public function index(): string|RedirectResponse
	{
		$this->employee = model(Employee::class);
		if(!$this->employee->is_logged_in())
		{
			$migration = new MY_Migration(config('Migrations'));
			$config = config(OSPOS::class)->settings;

			$gcaptcha_enabled = array_key_exists('gcaptcha_enable', $config)
				? $config['gcaptcha_enable']
				: false;

			$migration->migrate_to_ci4();

			$data = [
				'has_errors' => false,
				'is_latest' => $migration->is_latest(),
				'latest_version' => $migration->get_latest_migration(),
				'gcaptcha_enabled' => $gcaptcha_enabled,
				'config' => $config
			];

			if($this->request->getMethod() !== 'POST')
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

			if(!$data['is_latest'])
			{
				set_time_limit(3600);

				$migration->setNamespace('App')->latest();
				return redirect()->to('login');
			}
		}

		return redirect()->to('home');
	}
}
