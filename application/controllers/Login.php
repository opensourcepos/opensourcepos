<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller
{
	public function index()
	{
		$this->load->library('migration');

		if($this->Employee->is_logged_in())
		{
			redirect('home');
		}
		else
		{
			$this->form_validation->set_error_delimiters('<div class="error">', '</div>');

			$this->form_validation->set_rules('username', 'lang:login_username', 'required|callback_login_check');

			if($this->form_validation->run() == FALSE)
			{
				$this->load->view('login');
			}
			else
			{
				redirect('home');
			}
		}
	}

	public function login_check($username)
	{
		if(!$this->installation_check())
		{
			$this->form_validation->set_message('login_check', $this->lang->line('login_invalid_installation'));

			return FALSE;
		}

		if(!$this->migration->is_latest())
		{
			set_time_limit(3600);
			// trigger any required upgrade before starting the application
			$this->migration->latest();
		}

		$password = $this->input->post('password');

		if(!$this->Employee->login($username, $password))
		{
			$this->form_validation->set_message('login_check', $this->lang->line('login_invalid_username_and_password'));

			return FALSE;
		}

		if($this->config->item('gcaptcha_enable'))
		{
			$g_recaptcha_response = $this->input->post('g-recaptcha-response');

			if(!$this->gcaptcha_check($g_recaptcha_response))
			{
				$this->form_validation->set_message('login_check', $this->lang->line('login_invalid_gcaptcha'));

				return FALSE;
			}
		}

		return TRUE;
	}

	private function gcaptcha_check($response)
	{
		if(!empty($response))
		{
			$check = array(
				'secret'   => $this->config->item('gcaptcha_secret_key'),
				'response' => $response,
				'remoteip' => $this->input->ip_address()
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
	}
}
?>
