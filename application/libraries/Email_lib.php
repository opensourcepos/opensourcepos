<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class Email_lib
{
	private $CI;

  	public function __construct()
	{
		$this->CI =& get_instance();
		
		$this->CI->load->library('email');

		$config = array(
			'mailtype' => 'html',
			'useragent' => 'OSPOS',
			'validate' => TRUE,
			'protocol' => $this->CI->config->item('protocol'),
			'mailpath' => $this->CI->config->item('mailpath'),
			'smtp_host' => $this->CI->config->item('smtp_host'),
			'smtp_user' => $this->CI->config->item('smtp_user'),
			'smtp_pass' => $this->CI->config->item('smtp_pass'),
			'smtp_port' => $this->CI->config->item('smtp_port'),
			'smtp_timeout' => $this->CI->config->item('smtp_timeout'),
			'smtp_crypto' => $this->CI->config->item('smtp_crypto')
		);
		
		$this->CI->email->initialize($config);
	}
	
	/*
	 * Email sending function
	 * Example of use: $response = sendEmail('john@doe.com', 'Hello', 'This is a message', $filename);
	 */
	public function sendEmail($to, $subject, $message, $attachment = NULL)
	{
		$this->CI->email->from($this->CI->config->item('email'), $this->CI->config->item('company'));
		$this->CI->email->to($to);
		$this->CI->email->subject($subject);
		$this->CI->email->message($message);
		if( !empty($attachment) )
		{
			$this->CI->email->attach($attachment);
		}

		return $this->CI->email->send();
	}
}

?>
