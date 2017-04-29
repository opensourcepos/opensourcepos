<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Mail;

use Nette;


/**
 * Sends emails via the PHP internal mail() function.
 */
class SendmailMailer implements IMailer
{
	use Nette\SmartObject;

	/** @var string|NULL */
	public $commandArgs;


	/**
	 * Sends email.
	 * @return void
	 * @throws SendException
	 */
	public function send(Message $mail)
	{
		$tmp = clone $mail;
		$tmp->setHeader('Subject', NULL);
		$tmp->setHeader('To', NULL);

		$parts = explode(Message::EOL . Message::EOL, $tmp->generateMessage(), 2);

		$args = [
			str_replace(Message::EOL, PHP_EOL, $mail->getEncodedHeader('To')),
			str_replace(Message::EOL, PHP_EOL, $mail->getEncodedHeader('Subject')),
			str_replace(Message::EOL, PHP_EOL, $parts[1]),
			str_replace(Message::EOL, PHP_EOL, $parts[0]),
		];
		if ($this->commandArgs) {
			$args[] = (string) $this->commandArgs;
		}
		$res = Nette\Utils\Callback::invokeSafe('mail', $args, function ($message) use (&$info) {
			$info = ": $message";
		});
		if ($res === FALSE) {
			throw new SendException("Unable to send email$info.");
		}
	}

}
