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
			'protocol' => config('OSPOS')->protocol,
			'mailpath' => config('OSPOS')->mailpath,
			'smtp_host' => config('OSPOS')->smtp_host,
			'smtp_user' => config('OSPOS')->smtp_user,
			'smtp_pass' => $this->encrypter->decrypt(config('OSPOS')->smtp_pass),
			'smtp_port' => config('OSPOS')->smtp_port,
			'smtp_timeout' => config('OSPOS')->smtp_timeout,
			'smtp_crypto' => config('OSPOS')->smtp_crypto
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

		$email->setFrom(config('OSPOS')->email, config('OSPOS')->company);
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