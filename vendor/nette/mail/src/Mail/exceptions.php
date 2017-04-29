<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Mail;

use Nette;


/**
 * Exception thrown when a mail sending error is encountered.
 */
class SendException extends Nette\InvalidStateException
{
}


/**
 * SMTP mailer exception.
 */
class SmtpException extends SendException
{
}


class FallbackMailerException extends SendException
{
	/** @var SendException[] */
	public $failures;
}
