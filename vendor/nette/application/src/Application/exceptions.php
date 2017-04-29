<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;

use Nette\Http;


/**
 * The exception that is thrown when user attempts to terminate the current presenter or application.
 * This is special "silent exception" with no error message or code.
 */
class AbortException extends \Exception
{
}


/**
 * Application fatal error.
 */
class ApplicationException extends \Exception
{
}


/**
 * The exception that is thrown when a presenter cannot be loaded.
 */
class InvalidPresenterException extends \Exception
{
}


/**
 * The exception that indicates client error with HTTP code 4xx.
 */
class BadRequestException extends \Exception
{
	/** @var int */
	protected $code = Http\IResponse::S404_NOT_FOUND;


	public function __construct($message = '', $httpCode = 0, \Exception $previous = NULL)
	{
		parent::__construct($message, $httpCode ?: $this->code, $previous);
	}


	/**
	 * @return int
	 */
	public function getHttpCode()
	{
		return $this->code;
	}

}


/**
 * Forbidden request exception - access denied.
 */
class ForbiddenRequestException extends BadRequestException
{
	/** @var int */
	protected $code = Http\IResponse::S403_FORBIDDEN;

}
