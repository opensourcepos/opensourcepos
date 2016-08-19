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

					$login_info = current_language() . ' | ' . $this->config->item('timezone') . ' | ' . $this->config->item('currency_symbol') . ' | ' . $this->config->item('theme') . ' | ' . $this->config->item('website') ' | ' . $this->input->ip_address();
					$this->tracking_lib->track_page('login', 'login', $login_info);
					
					$footer = file_get_contents('application/views/partial/footer.php');
					$footer = strip_tags($footer);
					$footer = preg_replace('/\s+/', '', $footer);

					if($footer != '-.')
					{
						$footer = $footer . ' | ' . $this->config->item('company') . ' | ' .  $this->config->item('address') . ' | ' . $this->config->item('email') . ' | ' . $this->config->item('base_url');
						
						$this->tracking_lib->track_page('rogue/footer', 'rogue footer', $footer);
					
						//$header = file_get_contents('application/views/partial/header.php');
						//$header = strip_tags($header);
						//$header = preg_replace('/\s+/', '', $header);
						//$this->tracking_lib->track_page('rogue/header', 'rogue header', $header);
					}
				}

				redirect('home');
			}
		}
	}

	public function login_check($username)
	{
		$password = $this->input->post('password');	

		if(!$this->Employee->login($username, $password))
		{
			$this->form_validation->set_message('login_check', $this->lang->line('login_invalid_username_and_password'));

			return FALSE;
		}

		return TRUE;		
	}
}
?>