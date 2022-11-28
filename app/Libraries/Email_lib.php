<?php

namespace app\Libraries;

use CodeIgniter\Email\Email;
use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Encryption\EncrypterInterface;


/**
 * Email library
 *
 * Library with utilities to configure and send emails
 *
 * @property email email
 * @property encryption encryption
 * @property encrypterinterface encrypter
 */

class Email_lib
{
  	public function __construct()
	{
		$this->email = new Email();
		$this->encryption = new Encryption();
		$this->encrypter = $this->encryption->initialize();


		$config = [
			'mailtype' => 'html',
			'useragent' => 'OSPOS',
			'validate' => TRUE,
			'protocol' => config('OSPOS')->settings['protocol'],
			'mailpath' => config('OSPOS')->settings['mailpath'],
			'smtp_host' => config('OSPOS')->settings['smtp_host'],
			'smtp_user' => config('OSPOS')->settings['smtp_user'],
			'smtp_pass' => $this->encrypter->decrypt(config('OSPOS')->settings['smtp_pass']),
			'smtp_port' => config('OSPOS')->settings['smtp_port'],
			'smtp_timeout' => config('OSPOS')->settings['smtp_timeout'],
			'smtp_crypto' => config('OSPOS')->settings['smtp_crypto']
		];

		$this->email->initialize($config);
	}

	/**
	 * Email sending function
	 * Example of use: $response = sendEmail('john@doe.com', 'Hello', 'This is a message', $filename);
	 */
	public function sendEmail(string $to, string $subject, string $message, string $attachment = NULL): bool
	{
		$email = $this->email;

		$email->setFrom(config('OSPOS')->settings['email'], config('OSPOS')->settings['company']);
		$email->setTo($to);
		$email->setSubject($subject);
		$email->setMessage($message);

		if(!empty($attachment))
		{
			$email->attach($attachment);
		}

		$result = $email->send();

		if(!$result)
		{
			error_log($email->printDebugger());
		}

		return $result;
	}
}