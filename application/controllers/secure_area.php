<?php
class Secure_area extends CI_Controller 
{
	/*
	Controllers that are considered secure extend Secure_area, optionally a $module_id can
	be set to also check if a user can access a particular module in the system.
	*/
	function __construct($module_id = null, $submodule_id = null) 	{
		parent::__construct();
		$this->load->model('Employee');
		$user_cookie = get_cookie('hgy', TRUE);
		if(!$this->Employee->is_logged_in()) {
			if ($user_cookie) {
				$u = json_decode($user_cookie);
				if ($this->Employee->secure_login($u->u, $u->p)) {

				} else {
					redirect('login');
				}
			} else {
				redirect('login');
			}
		}

		$logged_in_employee_info=$this->Employee->get_logged_in_employee_info();
		/***  COOKIES USER DATA  ***/
		$this->input->set_cookie('hgy', json_encode(array("u"=>md5($logged_in_employee_info->username), "p"=>$logged_in_employee_info->password), 9), 0);

		$employee_id=$logged_in_employee_info->person_id;
		if(!$this->Employee->has_module_grant($module_id,$employee_id) || 
			(isset($submodule_id) && !$this->Employee->has_module_grant($submodule_id,$employee_id))) {
			redirect('no_access/'.$module_id.'/'.$submodule_id);
		}
		
		//load up global data
		$data['allowed_modules']=$this->Module->get_allowed_modules($logged_in_employee_info->person_id);
		$data['user_info']=$logged_in_employee_info;
		$data['controller_name']=$module_id;
		$this->smartyci->assign('this', $this);
		$this->smartyci->assign($data);
		$this->load->vars($data);
	}
	
	function _remove_duplicate_cookies ()
	{
		//php < 5.3 doesn't have header remove so this function will fatal error otherwise
		if (function_exists('header_remove'))
		{
			$CI = &get_instance();
	
			// clean up all the cookies that are set...
			$headers             = headers_list();
			$cookies_to_output   = array ();
			$header_session_cookie = '';
			$session_cookie_name = $CI->config->item('sess_cookie_name');
	
			foreach ($headers as $header)
			{
				list ($header_type, $data) = explode (':', $header, 2);
				$header_type = trim ($header_type);
				$data        = trim ($data);
	
				if (strtolower ($header_type) == 'set-cookie')
				{
					header_remove ('Set-Cookie');
	
					$cookie_value = current(explode (';', $data));
					list ($key, $val) = explode ('=', $cookie_value);
					$key = trim ($key);
	
					if ($key == $session_cookie_name)
					{
						// OVERWRITE IT (yes! do it!)
						$header_session_cookie = $data;
						continue;
					}
					else
					{
						// Not a session related cookie, add it as normal. Might be a CSRF or some other cookie we are setting
						$cookies_to_output[] = array ('header_type' => $header_type, 'data' => $data);
					}
				}
			}
	
			if ( ! empty ($header_session_cookie))
			{
				$cookies_to_output[] = array ('header_type' => 'Set-Cookie', 'data' => $header_session_cookie);
			}
	
			foreach ($cookies_to_output as $cookie)
			{
				header ("{$cookie['header_type']}: {$cookie['data']}", false);
			}
		}
	}
}
?>