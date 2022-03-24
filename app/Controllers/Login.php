<?php

namespace App\Controllers;

use app\Models\Employee;
use Config\Services;

/**
 * @property employee employee
 * @property mixed migration
 */
class Login extends BaseController
{
	public function __construct()
	{
		$this->migration = Services::migrations();
		$this->employee = model('Employee');
	}
	public function index(): void
	{
		if($this->employee->is_logged_in())
		{
			redirect('home');
		}
		else
		{
			$this->validator->set_error_delimiters('<div class="error">', '</div>');	//TODO: Form Validation needs to be upgraded https://codeigniter4.github.io/CodeIgniter4/installation/upgrade_validations.html

			$this->validator->setRule('username', 'lang:login_username', 'required|callback_login_check');	//TODO: There are no callbacks in CI4 form validation so we need to convert that.


			if(config('OSPOS')->gcaptcha_enable)
			{
				$this->validator->setRule('g-recaptcha-response', 'lang:login_gcaptcha', 'required|callback_gcaptcha_check');
			}

			if (!$this->validate([]))
			{
				echo view('login', ['validation' => $this->validator,]);
			}
			else
			{
				redirect('home');
			}
		}
	}

	/**
	 * Checks to make sure that the user is logged in or not.  Called in a validator callback.
	 *
	 * @param string $username
	 * @return bool
	 */
	public function login_check(string $username): bool
	{
		$password = $this->request->getPost('password');

		if(!$this->_installation_check())	//TODO: Hungarian notation
		{
			$this->validator->set_message('login_check', lang('Login.invalid_installation'));

			return FALSE;
		}

		set_time_limit(3600);
		$this->migration->latest();

		if(!$this->employee->login($username, $password))
		{
			$this->validator->set_message('login_check', lang('Login.invalid_username_and_password'));

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Processes gCaptcha response and returns result. Called in a validator callback.
	 *
	 * @param string $recaptchaResponse
	 * @return bool
	 */
	public function gcaptcha_check(string $recaptchaResponse): bool
	{
		$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . config('OSPOS')->gcaptcha_secret_key . '&response=' . $recaptchaResponse . '&remoteip=' . $this->request->getIPAddress();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);

		$status = json_decode($result, TRUE);

		if(empty($status['success']))
		{
			$this->form_validation->set_message('gcaptcha_check', lang('Login.invalid_gcaptcha'));

			return FALSE;
		}

		return TRUE;
	}

	private function _installation_check()	//TODO: Hungarian Notation
	{
		// get PHP extensions and check that the required ones are installed
		$extensions = implode(', ', get_loaded_extensions());
		$keys = [
			'bcmath',
			'intl',
			'gd',
			'openssl',
			'mbstring',
			'curl'
		];

		$pattern = '/';
		foreach($keys as $key) 
		{
			$pattern .= '(?=.*\b' . preg_quote($key, '/') . '\b)';
		}
		$pattern .= '/i';
		$result = preg_match($pattern, $extensions);

		if(!$result)
		{
			error_log('Check your php.ini');
			error_log('PHP installed extensions: ' . $extensions);
			error_log('PHP required extensions: ' . implode(', ', $keys));
		}

		return $result;
	}
}