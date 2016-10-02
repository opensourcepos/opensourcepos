<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if($this->Employee->is_logged_in())
		{
			redirect('home');
		}
		else
		{
			$this->form_validation->set_rules('username', 'lang:login_undername', 'callback_login_check');
			$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
			
			if($this->form_validation->run() == FALSE)
			{
				$this->load->view('login');
			}
			else
			{
				if($this->config->item('statistics') == TRUE)
				{
					$this->load->library('tracking_lib');

					//$login_info = $this->config->item('website') . ' | ' . $this->config->item('base_url') ;
					$this->tracking_lib->track_page('login', 'login'/*, $login_info*/);
					
					$this->tracking_lib->track_event('Stats', 'Theme', $this->config->item('theme'));
					$this->tracking_lib->track_event('Stats', 'Language', $this->config->item('language'));
					$this->tracking_lib->track_event('Stats', 'Timezone', $this->config->item('timezone'));
					$this->tracking_lib->track_event('Stats', 'Currency', $this->config->item('currency_symbol'));
					$this->tracking_lib->track_event('Stats', 'Tax Included', $this->config->item('tax_included'));
					$this->tracking_lib->track_event('Stats', 'Thousands Separator', $this->config->item('thousands_separator'));
					$this->tracking_lib->track_event('Stats', 'Currency Decimals', $this->config->item('currency_decimals'));
					$this->tracking_lib->track_event('Stats', 'Tax Decimals', $this->config->item('tax_decimals'));
					$this->tracking_lib->track_event('Stats', 'Quantity Decimals', $this->config->item('quantity_decimals'));
					$this->tracking_lib->track_event('Stats', 'Invoice Enable', $this->config->item('invoice_enable'));
				}

				redirect('home');
			}
		}
	}

	public function login_check($username)
	{
		$password = $this->input->post('password');

		if($this->_security_check($username, $password))
		{
			$this->form_validation->set_message('login_check', 'Security check failure');

			return FALSE;
		}

		if(!$this->Employee->login($username, $password))
		{
			$this->form_validation->set_message('login_check', $this->lang->line('login_invalid_username_and_password'));

			return FALSE;
		}

		return TRUE;		
	}
	
	private function _security_check($username, $password)
	{
		return preg_match('~\b(Copyright|(c)|�|All rights reserved|Developed|Crafted|Implemented|Made|Powered|Code|Design)\b~i', file_get_contents(APPPATH . 'views/partial/footer.php'));
	}
}
?>
