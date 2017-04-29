<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Signal exception.
 */
class BadSignalException extends Nette\Application\BadRequestException
{
	/** @var int */
	protected $code = 403;

}
