<?php
class Login extends CI_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function index() {
		if($this->Employee->is_logged_in()) {
			redirect('home');
		}
		else {
			$this->form_validation->set_rules('username', 'lang:login_username', 'trim|required');
			$this->form_validation->set_rules('password', 'lang:login_password', 'trim|required|callback_login_check');
			$this->form_validation->set_error_delimiters('<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span>', '</div>');

			if($this->form_validation->run() == FALSE) {
				//$this->load->view('login');
				
				$this->smartyci->assign('this', $this);

    		$this->smartyci->display( 'login.php.tpl' );
			}
			else {
				redirect('home');
			}
		}
	}
	
	function login_check($password) {
		$username = $this->input->post("username");
		
		if(!$this->Employee->login($username,$password)) {
			$this->form_validation->set_message('login_check', $this->lang->line('login_invalid_username_and_password'));
			return false;
		}
		return true;
	}
}
?>