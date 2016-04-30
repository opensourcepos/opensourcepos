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
		$data['person_info'] = $this->Person->get_info($person_id);

		$this->load->view('messages/form_sms', $data);
	}

	function send()
	{	
		$username = $this->config->item('msg_uid');
		$password = $this->config->item('msg_pwd');
		$phone = $this->input->post('phone');
		$message = $this->input->post('message');
		$originator = $this->config->item('msg_src');

		$response = $this->sms->sendSMS($username, $password, $phone, $message, $originator);

		if($response)
		{
			echo json_encode(array('success'=>true, 'message'=>$this->lang->line('messages_successfully_sent') . ' ' . $phone));
		}
		else
		{
			echo json_encode(array('success'=>false, 'message'=>$this->lang->line('messages_unsuccessfully_sent') . ' ' . $phone));
		}
	}
	
	function send_form($person_id=-1)
	{	
		$username = $this->config->item('msg_uid');
		$password = $this->config->item('msg_pwd');
		$phone = $this->input->post('phone');
		$message = $this->input->post('message');
		$originator = $this->config->item('msg_src');

		$response = $this->sms->sendSMS($username, $password, $phone, $message, $originator); 

		if($response)
		{
			echo json_encode(array('success'=>true, 'message'=>$this->lang->line('messages_successfully_sent') . ' ' . $phone, 'person_id'=>$person_id));
		}
		else
		{
			echo json_encode(array('success'=>false, 'message'=>$this->lang->line('messages_unsuccessfully_sent') . ' ' . $phone, 'person_id'=>-1));
		}
	}
}
?>
