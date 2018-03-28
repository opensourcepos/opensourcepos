<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller
{
	public function index()
	{
		if($this->Employee->is_logged_in())
		{
			redirect('home');
		}
		else
		{
			$this->form_validation->set_error_delimiters('<div class="error">', '</div>');

			$this->form_validation->set_rules('username', 'lang:login_username', 'required|callback_login_check');

			if($this->config->item('gcaptcha_enable'))
			{
				$this->form_validation->set_rules('g-recaptcha-response', 'lang:login_gcaptcha', 'required|callback_gcaptcha_check');
			}

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
		$password = $this->input->post('password');

		if(!$this->_installation_check())
		{
			$this->form_validation->set_message('login_check', $this->lang->line('login_invalid_installation'));

			return FALSE;
		}

		if(!$this->Employee->login($username, $password))
		{
			$this->form_validation->set_message('login_check', $this->lang->line('login_invalid_username_and_password'));

			return FALSE;
		}

		// trigger any required upgrade before starting the application
		$this->load->library('migration');
		$this->migration->latest();

		return TRUE;
	}

	public function gcaptcha_check($recaptchaResponse)
	{
		$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $this->config->item('gcaptcha_secret_key') . '&response=' . $recaptchaResponse . '&remoteip=' . $this->input->ip_address();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);

		$status = json_decode($result, TRUE);

		if(empty($status['success']))
		{
			$this->form_validation->set_message('gcaptcha_check', $this->lang->line('login_invalid_gcaptcha'));

			return FALSE;
		}

		return TRUE;
	}

	private function _installation_check()
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
