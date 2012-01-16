<?php
class No_Access extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index($module_id='')
	{
		$data['module_name']=$this->Module->get_module_name($module_id);
		$this->load->view('no_access',$data);
	}
}
?>