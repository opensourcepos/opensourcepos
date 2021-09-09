<?php

namespace app\Libraries;

/**
 * Email library
 *
 * Library with utilities to configure and send emails
 */

class Email_lib
{
	private $CI;

  	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->email = new Email();

		$config = array(
			'mailtype' => 'html',
			'useragent' => 'OSPOS',
			'validate' => TRUE,
			'protocol' => $this->CI->config->get('protocol'),
			'mailpath' => $this->CI->config->get('mailpath'),
			'smtp_host' => $this->CI->config->get('smtp_host'),
			'smtp_user' => $this->CI->config->get('smtp_user'),
			'smtp_pass' => $this->CI->encryption->decrypt($this->CI->config->get('smtp_pass')),
			'smtp_port' => $this->CI->config->get('smtp_port'),
			'smtp_timeout' => $this->CI->config->get('smtp_timeout'),
			'smtp_crypto' => $this->CI->config->get('smtp_crypto')
		);

		$this->CI->email->initialize($config);
	}

	/**
	 * Email sending function
	 * Example of use: $response = sendEmail('john@doe.com', 'Hello', 'This is a message', $filename);
	 */
	public function sendEmail($to, $subject, $message, $attachment = NULL)
	{
		$email = $this->CI->email;

		$email->from($this->CI->config->get('email'), $this->CI->config->get('company'));
		$email->to($to);
		$email->subject($subject);
		$email->message($message);
		if(!empty($attachment))
		{
			$email->attach($attachment);
		}

		$result = $email->send();

		if(!$result)
		{
			error_log($email->print_debugger());
		}

		return $result;
	}
}

?>
