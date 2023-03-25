<?php

namespace App\Controllers;

use App\Libraries\Sms_lib;

use App\Models\Person;

/**
 *
 *
 * @property sms_lib sms_lib
 *
 * @property person person
 *
 */
class Messages extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('messages');
		
		$this->sms_lib = new Sms_lib();

		$this->person = model('Person');
	}
	
	public function getIndex(): void
	{
		echo view('messages/sms');
	}

	public function getView(int $person_id = NEW_ENTRY): void
	{ 
		$info = $this->person->get_info($person_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $value;
		}
		$data['person_info'] = $info;

		echo view('messages/form_sms', $data);
	}

	public function send(): void
	{
		$phone   = $this->request->getPost('phone', FILTER_SANITIZE_STRING);
		$message = $this->request->getPost('message', FILTER_SANITIZE_STRING);

		$response = $this->sms_lib->sendSMS($phone, $message);

		if($response)
		{
			echo json_encode (['success' => TRUE, 'message' => lang('Messages.successfully_sent') . ' ' . esc($phone)]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Messages.unsuccessfully_sent') . ' ' . esc($phone)]);
		}
	}

	/**
	 * Sends an SMS message to a user. Called in the view.
	 *
	 * @param int $person_id
	 * @return void
	 */
	public function send_form(int $person_id = NEW_ENTRY): void
	{	
		$phone   = $this->request->getPost('phone', FILTER_SANITIZE_STRING);
		$message = $this->request->getPost('message', FILTER_SANITIZE_STRING);

		$response = $this->sms_lib->sendSMS($phone, $message);

		if($response)
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Messages.successfully_sent') . ' ' . esc($phone),
				'person_id' => $person_id
			]);
		}
		else
		{
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Messages.unsuccessfully_sent') . ' ' . esc($phone),
				'person_id' => NEW_ENTRY
			]);
		}
	}
}
