<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Loader extends CI_Loader
{
	public function __construct()
	{
		parent::__construct();

		log_message('debug', "MY_Loader Class Initialized");
	}

	function view($view, $vars = array(), $return = FALSE)
	{
		include APPPATH . 'config/theme.php';

		// add other first view path if exist
		if(!empty($config['theme_name']) && file_exists('templates/' . $config['theme_name'] . '/views'))
		{
			$this->_ci_view_paths = array_merge(array('templates/' . $config['theme_name'] . '/views' . DIRECTORY_SEPARATOR => 1), $this->_ci_view_paths);
		}

		return $this->_ci_load(array('_ci_view'=>$view, '_ci_vars'=>$this->_ci_object_to_array($vars), '_ci_return'=>$return));
	}
}