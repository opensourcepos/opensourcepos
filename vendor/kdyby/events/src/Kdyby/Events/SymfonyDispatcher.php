<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Events;

use Kdyby;
use Nette;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;



if (!interface_exists('Symfony\Component\EventDispatcher\EventDispatcherInterface')) {
	eval('namespace Symfony\Component\EventDispatcher {
		interface EventDispatcherInterface {}
	}');
}

/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SymfonyDispatcher extends Nette\Object implements EventDispatcherInterface
{

	/**
	 * @var EventManager
	 */
	private $evm;



	public function __construct(EventManager $eventManager)
	{
		$this->evm = $eventManager;
	}



	public function dispatch($eventName, SymfonyEvent $event = null)
	{
		$this->evm->dispatchEvent($eventName, new EventArgsList(array($event)));
	}



	public function addListener($eventName, $listener, $priority = 0)
	{
		throw new NotSupportedException();
	}



	public function addSubscriber(EventSubscriberInterface $subscriber)
	{
		throw new NotSupportedException();
	}



	public function removeListener($eventName, $listener)
	{
		throw new NotSupportedException();
	}



	public function removeSubscriber(EventSubscriberInterface $subscriber)
	{
		throw new NotSupportedException();
	}



	public function getListeners($eventName = null)
	{
		return $this->getListeners($eventName);
	}



	public function hasListeners($eventName = null)
	{
		return $this->evm->hasListeners($eventName);
	}

}
