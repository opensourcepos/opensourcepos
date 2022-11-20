<?php

namespace app\Libraries;
use CodeIgniter\Email\Email;

class MY_Email extends Email
{
	var $default_cc_address = "";
	var $default_email_address = "";
	var $default_sender_name = "";
	var $default_sender_address = "";
	var $default_bounce_address = "";

	function __construct($config = [])
	{
		parent::__construct($config);
	}

	function sendMail(string $subject, string $body, string $to = NULL, string $reply_name = NULL, string $reply_mail = NULL, string $attachment = NULL): bool
	{
		$this->setReplyTo($reply_mail, $reply_name);
		$this->setFrom($this->default_sender_address, $this->default_sender_name, $this->default_bounce_address);
		$this->setMailtype('html');
		$this->setSubject($subject);
		$this->setMessage($body);
		if ($to == NULL) {
			$to = $this->default_email_address;
			$this->setCc($this->default_cc_address);
		}
		if ($attachment) {
			$this->attach($attachment);
		}
		$this->setTo($to);
		return $this->send();
	}
}