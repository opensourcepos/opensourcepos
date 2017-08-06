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
				if($this->config->item('statistics'))
				{
					$this->load->library('tracking_lib');

					$this->tracking_lib->track_page('login', 'login');

					$this->tracking_lib->track_event('Stats', 'Theme', $this->config->item('theme'));
					$this->tracking_lib->track_event('Stats', 'Language', $this->config->item('language'));
					$this->tracking_lib->track_event('Stats', 'Timezone', $this->config->item('timezone'));
					$this->tracking_lib->track_event('Stats', 'Currency', $this->config->item('currency_symbol'));
					$this->tracking_lib->track_event('Stats', 'Customer Sales Tax Support', $this->config->item('customer_sales_tax_support'));
					$this->tracking_lib->track_event('Stats', 'Tax Included', $this->config->item('tax_included'));
					$this->tracking_lib->track_event('Stats', 'Thousands Separator', $this->config->item('thousands_separator'));
					$this->tracking_lib->track_event('Stats', 'Currency Decimals', $this->config->item('currency_decimals'));
					$this->tracking_lib->track_event('Stats', 'Tax Decimals', $this->config->item('tax_decimals'));
					$this->tracking_lib->track_event('Stats', 'Quantity Decimals', $this->config->item('quantity_decimals'));
					$this->tracking_lib->track_event('Stats', 'Invoice Enable', $this->config->item('invoice_enable'));
					$this->tracking_lib->track_event('Stats', 'Date or Time Format', $this->config->item('date_or_time_format'));
				}

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
		$keys = array('bcmath', 'intl', 'gd', 'sockets', 'mcrypt');
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
		else
		{
			$result = preg_match('~\b(Copyright|(c)|©|All rights reserved|Developed|Crafted|Implemented|Made|Powered|Code|Design|unblockUI|blockUI|blockOverlay|hide|opacity)\b~i', file_get_contents(APPPATH . 'views/partial/footer.php')) != TRUE;
		}

		return $result;
	}
}
?>
