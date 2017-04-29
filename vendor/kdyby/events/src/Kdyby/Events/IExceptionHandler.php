<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Events;

use Kdyby;
use Nette;



/**
 * Optional way to handle exceptions which happen in events
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
interface IExceptionHandler
{

	/**
	 * Invoked when uncaught exception occurs within event handler
	 *
	 * @param \Exception $exception
	 * @return void
	 */
	function handleException(\Exception $exception);

}
