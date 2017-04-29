<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\Responses;

use Nette;


/**
 * Callback response.
 */
class CallbackResponse implements Nette\Application\IResponse
{
	use Nette\SmartObject;

	/** @var callable */
	private $callback;


	/**
	 * @param  callable  function (Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	 */
	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		call_user_func($this->callback, $httpRequest, $httpResponse);
	}

}
