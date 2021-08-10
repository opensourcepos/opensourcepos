<?php 

class MY_Email extends CI_Email {
	
	var $default_cc_address 	= "";
	var $default_email_address 	= "";
	var $default_sender_name 	= "";
	var $default_sender_address = "";
	var $default_bounce_address = "";

	function __construct($config = array())
	{
		parent::__construct($config);
	}

	function send_mail($subject, $body, $to = NULL, $reply_name = NULL, $reply_mail = NULL, $attachment = NULL) {
		$this->reply_to($reply_mail, $reply_name);
		$this->from($this->default_sender_address, $this->default_sender_name, $this->default_bounce_address);
		$this->set_mailtype('html');
		$this->subject($subject);
		$this->message($body);
		if ($to == NULL) {
			$to = $this->default_email_address;
			$this->cc($this->default_cc_address); 
		}
		if ($attachment) {
			$this->attach($attachment);
		}
		$this->to($to);
		return $this->send();
	}
    
}

?>
