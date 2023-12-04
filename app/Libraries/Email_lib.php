<?php

namespace app\Libraries;

use CodeIgniter\Email\Email;
use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Encryption\EncrypterInterface;
use Config\OSPOS;
use Config\Services;


/**
 * Email library
 *
 * Library with utilities to configure and send emails
 */

class Email_lib
{
	private Email $email;
	private array $config;

  	public function __construct()
	{
		$this->email = new Email();
		$this->config = config(OSPOS::class)->settings;

		$encrypter = Services::encrypter();

		$smtp_pass = $this->config['smtp_pass'];
		if(!empty($smtp_pass))
		{
			$smtp_pass = $encrypter->decrypt($smtp_pass);
		}

		$email_config = [
			'mailtype' => 'html',
			'useragent' => 'OSPOS',
			'validate' => true,
			'protocol' => $this->config['protocol'],
			'mailpath' => $this->config['mailpath'],
			'smtp_host' => $this->config['smtp_host'],
			'smtp_user' => $this->config['smtp_user'],
			'smtp_pass' => $smtp_pass,
			'smtp_port' => $this->config['smtp_port'],
			'smtp_timeout' => $this->config['smtp_timeout'],
			'smtp_crypto' => $this->config['smtp_crypto']
		];

		$this->email->initialize($email_config);
	}

	/**
	 * Email sending function
	 * Example of use: $response = sendEmail('john@doe.com', 'Hello', 'This is a message', $filename);
	 */
	public function sendEmail(string $to, string $subject, string $message, string $attachment = null): bool
	{
		$email = $this->email;

		$email->setFrom($this->config['email'], $this->config['company']);
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
