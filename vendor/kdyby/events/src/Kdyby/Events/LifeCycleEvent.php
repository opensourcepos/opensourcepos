<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Events;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
final class LifeCycleEvent extends Nette\Object
{

	/**
	 * Occurs before the application loads presenter
	 */
	const onStartup = 'Nette\\Application\\Application::onStartup';

	/**
	 * Occurs before the application shuts down
	 */
	const onShutdown = 'Nette\\Application\\Application::onShutdown';

	/**
	 * Occurs when a new request is ready for dispatch;
	 */
	const onRequest = 'Nette\\Application\\Application::onRequest';

	/**
	 * Occurs when a presenter is created
	 */
	const onPresenter = 'Nette\\Application\\Application::onPresenter';

	/**
	 * Occurs when a new response is received
	 */
	const onResponse = 'Nette\\Application\\Application::onResponse';

	/**
	 * Occurs when an unhandled exception occurs in the application
	 */
	const onError = 'Nette\\Application\\Application::onError';

}
