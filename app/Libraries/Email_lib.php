<?php

namespace app\Libraries;

use CodeIgniter\Email\Email;
use app\Models\Appconfig;
use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Encryption\EncrypterInterface;


/**
 * Email library
 *
 * Library with utilities to configure and send emails
 *
 * @property appconfig appconfig
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
		$this->appconfig = model('Appconfig');

		$this->encryption = new Encryption();
		$this->encrypter = $this->encryption->initialize();


		$config = [
			'mailtype' => 'html',
			'useragent' => 'OSPOS',
			'validate' => TRUE,
			'protocol' => $this->appconfig->get('protocol'),
			'mailpath' => $this->appconfig->get('mailpath'),
			'smtp_host' => $this->appconfig->get('smtp_host'),
			'smtp_user' => $this->appconfig->get('smtp_user'),
			'smtp_pass' => $this->encrypter->decrypt($this->appconfig->get('smtp_pass')),
			'smtp_port' => $this->appconfig->get('smtp_port'),
			'smtp_timeout' => $this->appconfig->get('smtp_timeout'),
			'smtp_crypto' => $this->appconfig->get('smtp_crypto')
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

		$email->setFrom($this->appconfig->get('email'), $this->appconfig->get('company'));
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

?>
