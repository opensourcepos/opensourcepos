<?php

namespace App\Controllers;

use App\Models\Employee;

/**
 * @property employee employee
 */
class Login extends BaseController
{
	public function index(): void
	{
		$this->employee = model('Employee');

		if($this->employee->is_logged_in())
		{
			redirect('home');
		}
		else
		{
			$this->validator->setRule('username', 'lang:login_username', 'required|login_check');


			if(config('OSPOS')->gcaptcha_enable)
			{
				$this->validator->setRule('g-recaptcha-response', 'lang:login_gcaptcha', 'required|gcaptcha_check');
			}

			if(!$this->validate([]))
			{
				echo view('login', ['validation' => $this->validator]);
			}
			else
			{
				redirect('home');
			}
		}
	}
}