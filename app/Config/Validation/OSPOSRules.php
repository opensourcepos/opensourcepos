<?php
namespace App\Config\Validation;

use App\Models\Employee;
use CodeIgniter\HTTP\IncomingRequest;
use Config\OSPOS;
use Config\Services;

/**
 * @property Employee employee
 * @property IncomingRequest request
 */
class OSPOSRules
{
	private IncomingRequest $request;
	private array $config;

	/**
	 * Validates the username and password sent to the login view. User is logged in on successful validation.
	 *
	 * @param string $username Username to check against.
	 * @param string $fields Comma separated string of the fields for validation.
	 * @param array $data Data sent to the view.
	 * @param string|null $error The error sent back to the validation handler on failure.
	 * @return bool True if validation passes or false if there are errors.
	 * @noinspection PhpUnused
	 */
	public function login_check(string $username, string $fields , array $data, ?string &$error = null): bool
	{
		$employee = model(Employee::class);
		$this->request = Services::request();
		$this->config = config(OSPOS::class)->settings;

		//Installation Check
		if(!$this->installation_check())
		{
			$error = lang('Login.invalid_installation');

			return false;
		}

		$password = $data['password'];
		if(!$employee->login($username, $password))
		{
			$error = lang('Login.invalid_username_and_password');

			return false;
		}

		$gcaptcha_enabled = array_key_exists('gcaptcha_enable', $this->config) && $this->config['gcaptcha_enable'];
		if($gcaptcha_enabled)
		{
			$g_recaptcha_response = $this->request->getPost('g-recaptcha-response');

			if(!$this->gcaptcha_check($g_recaptcha_response))
			{
				$error = lang('Login.invalid_gcaptcha');

				return false;
			}
		}

		return true;
	}

	/**
	 * Checks to see if GCaptcha verification was successful.
	 *
	 * @param $response
	 * @return bool true on successful GCaptcha verification or false if GCaptcha failed.
	 */
	private function gcaptcha_check($response): bool
	{
		if(!empty($response))
		{
			$check = [
				'secret'   => $this->config['gcaptcha_secret_key'],
				'response' => $response,
				'remoteip' => $this->request->getIPAddress()
			];

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($check));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);

			curl_close($ch);

			$status = json_decode($result, true);

			if(!empty($status['success']))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks to make sure dependency PHP extensions are installed
	 *
	 * @return bool
	 */
	private function installation_check(): bool
	{
		$installed_extensions = implode(', ', get_loaded_extensions());
		$required_extensions = ['bcmath', 'intl', 'gd', 'openssl', 'mbstring', 'curl'];
		$pattern = '/';

		foreach($required_extensions as $extension)
		{
			$pattern .= '(?=.*\b' . preg_quote($extension, '/') . '\b)';
		}

		$pattern .= '/i';
		$is_installed = preg_match($pattern, $installed_extensions);

		if(!$is_installed)
		{
			log_message('error', '[ERROR] Check your php.ini.');
			log_message('error',"PHP installed extensions: $installed_extensions");
			log_message('error','PHP required extensions: ' . implode(', ', $required_extensions));
		}

		return $is_installed;
	}

	/**
	 * Validates the candidate as a decimal number. Takes the locale into account. Used in validation rule calls.
	 *
	 * @param string $candidate
	 * @param string|null $error
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function decimal_locale(string $candidate, ?string &$error = null): bool
	{
		$candidate = prepare_decimal($candidate);
		$validation = Services::validation();

		$validation->setRules([
			'candidate' => 'decimal'
		]);

		$data = [
			'candidate' => $candidate
		];

		if (!$validation->run($data))
		{
			$error = $validation->getErrors();
			return false;
		}

		return true;
	}
}
