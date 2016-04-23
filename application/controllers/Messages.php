<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once ("Secure_area.php");

class Messages extends Secure_area
{
	function __construct()
	{
		parent::__construct('messages');
	}
	
	public function index()
	{
		$data['controller_name'] = $this->get_controller_name();
		$this->load->view("messages/sms");
	}
	/*
	Loads the sms sender form
	*/
	function view($person_id=-1)
	{ 
		$data['person_info']=$this->Person->get_info($person_id);
		$this->load->view("messages/sms-sender",$data);
	}	
}
?>
