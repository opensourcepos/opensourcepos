<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Any response returned by presenter.
 */
interface IResponse
{

	/**
	 * Sends response to output.
	 * @return void
	 */
	function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse);

}
