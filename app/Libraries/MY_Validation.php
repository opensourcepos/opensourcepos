<?php

namespace app\Libraries;

use CodeIgniter\Database\Migration;
use CodeIgniter\Validation\Validation;
use Config\Services;
use app\Models\Employee;

/**
 * @property migration migration
 * @property employee employee
 */
class MY_Validation extends Validation
{
	/**
	 * Checks to make sure that the user is logged in or not.  Called as a validator rule.
	 *
	 * @param string $username
	 * @param string|null $error
	 * @return bool
	 */
	public function login_check(string $username, string &$error = null): bool
	{
		$migration = Services::migrations();
		$employee = model(Employee::class);

		$password = $this->request->getPost('password');	//TODO: This needs to get passed as a parameter in some way

		if(!$this->_installation_check())	//TODO: Hungarian notation
		{
			$error = lang('Login.invalid_installation');

			return FALSE;
		}

		set_time_limit(3600);
		$migration->latest();

		if(!$employee->login($username, $password))
		{
			$error = lang('Login.invalid_username_and_password');

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

	/**
	 * Processes gCaptcha response and returns result. Called as a validator rule.
	 *
	 * @param string $recaptchaResponse
	 * @param null $error
	 * @return bool
	 */
	public function gcaptcha_check(string $recaptchaResponse, &$error = null): bool
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
			$error = lang('Login.invalid_gcaptcha');

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Validation function used in controllers.
	 * @param string $str
	 * @return float|bool Returns a float if numeric or FALSE if not numeric
	 */
	public function numeric(string $str)
	{
		return parse_decimals($str);
	}
}