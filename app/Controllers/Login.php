<?php

namespace App\Controllers;

use App\Libraries\MY_Migration;
use App\Models\Employee;
use Config\Migrations;
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
				'validation' => Services::validation(),
				'latest_version' => $migration->is_latest(),
				'application_version' => $migration->get_current_version()
			];
//TODO: Validation isn't working. #3595
//			if(strtolower($this->request->getMethod()) != 'post')
//			{
				echo view('login', $data);
//			}

//			if(!$this->validate(['username' => 'required|login_check']))
//			{
//				echo view('login', ['validation' => $this->validator->getErrors()]);
//			}
		}

		return redirect('home');
	}

/*	public function login_check(string $username): bool
	{
		if(!$this->installation_check())
		{
			$this->validator->setMessage('login_check', lang('login_invalid_installation'));	//TODO: This is going to need some work https://codeigniter.com/user_guide/libraries/validation.html?highlight=validation#setting-custom-error-messages

			return FALSE;
		}

		$password = $this->request->getPost('password');

		if(!$this->employee->login($username, $password))
		{
			$this->validator->set_message('login_check', $this->lang->line('login_invalid_username_and_password'));

			return FALSE;
		}

		if(config('OSPOS')->settings['gcaptcha_enable'])
		{
			$g_recaptcha_response = $this->request->getPost('g-recaptcha-response');

			if(!$this->gcaptcha_check($g_recaptcha_response))
			{
				$this->validator->setMessage('login_check', lang('login_invalid_gcaptcha'));

				return FALSE;
			}
		}
		return TRUE;
	}

	private function gcaptcha_check($response): bool
	{
		if(!empty($response))
		{
			$check = array(
				'secret'   => config('OSPOS')->settings['gcaptcha_secret_key'],
				'response' => $response,
				'remoteip' => $this->request->getIPAddress()
			);

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($check));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

			$result = curl_exec($ch);

			curl_close($ch);

			$status = json_decode($result, TRUE);

			if(!empty($status['success']))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	private function installation_check()
	{
		// get PHP extensions and check that the required ones are installed
		$extensions = implode(', ', get_loaded_extensions());
		$keys = array('bcmath', 'intl', 'gd', 'openssl', 'mbstring', 'curl');
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
	}*/
}