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
		$this->load->view('messages/sms');
		
	}
	

	function view($person_id=-1)
	{ 
		$data['person_info']=$this->Person->get_info($person_id);
		$this->load->view('messages/sms-sender',$data);
	}

	
	function send()
	{	
		$uid = $this->config->item('msg_uid');
		$pwd = $this->config->item('msg_pwd');
		$phone = $this->input->post('phone');
		$msg = $this->input->post('msg');

		$response = $this->sms->sendsms($uid,$pwd,$phone,$msg); 
		$this->load->view('messages/sms');

		if('send')
		{
		    echo "Message Submitted Successfully";
		}
		else
		{   
		    echo "Message NOT Submitted Successfully";
		}
		
	}
}
?>
