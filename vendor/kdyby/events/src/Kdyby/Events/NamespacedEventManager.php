<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Events;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class NamespacedEventManager extends EventManager
{

	/**
	 * @var bool
	 */
	public $dispatchGlobalEvents = FALSE;

	/**
	 * @var EventManager
	 */
	private $evm;

	/**
	 * @var string
	 */
	private $namespace;



	public function __construct($namespace, EventManager $eventManager)
	{
		$this->namespace = $namespace;
		$this->evm = $eventManager;
	}



	/**
	 * {@inheritDoc}
	 */
	public function dispatchEvent($eventName, Doctrine\Common\EventArgs $eventArgs = null)
	{
		list($ns, $event) = Event::parseName($eventName);

		$this->evm->dispatchEvent($ns !== NULL ? $eventName : $this->namespace . $eventName, $eventArgs);

		if ($this->dispatchGlobalEvents && $this->evm->hasListeners($event)) {
			$this->evm->dispatchEvent($event, $eventArgs);
		}
	}



	/**
	 * {@inheritDoc}
	 */
	public function getListeners($eventName = null)
	{
		if ($eventName === NULL) {
			$listeners = array();
			foreach ($this->evm->getListeners(NULL, TRUE) as $eventName => $subscribers) {
				list($ns, $event) = Event::parseName($eventName);
				if ($ns === NULL || stripos($this->namespace, $ns) !== FALSE) {
					$listeners[$eventName] = $subscribers;
				}
			}

			return $listeners;
		}

		list($ns, $event) = Event::parseName($eventName);

		if ($ns !== NULL) {
			throw new InvalidArgumentException("Unexpected event with namespace.");
		}

		return array_merge(
			$this->evm->getListeners($event),
			$this->evm->getListeners($this->namespace . $event)
		);
	}



	/**
	 * {@inheritDoc}
	 */
	public function hasListeners($eventName)
	{
		list($ns, $event) = Event::parseName($eventName);

		if ($ns) {
			return $this->evm->hasListeners($eventName) || $this->evm->hasListeners($event);
		}

		return $this->evm->hasListeners($this->namespace . $eventName) || $this->evm->hasListeners($eventName);
	}



	/**
	 * {@inheritDoc}
	 */
	public function addEventListener($events, $subscriber, $priority = 0)
	{
		foreach ((array) $events as $eventName) {
			list($ns, $event) = Event::parseName($eventName);
			$this->evm->addEventListener(array($ns === NULL ? $this->namespace . $event : $eventName), $subscriber);
		}
	}



	/**
	 * {@inheritDoc}
	 */
	public function removeEventListener($unsubscribe, $subscriber = NULL)
	{
		if ($unsubscribe instanceof Doctrine\Common\EventSubscriber) {
			$subscriber = $unsubscribe;
			$unsubscribe = array();

			foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
				if ((is_array($params) && is_array($params[0])) || !is_numeric($eventName)) {
					// [EventName => [[method, priority], ...], ...]
					// [EventName => [method, priority], ...] && [EventName => method, .
					$unsubscribe[] = $eventName;

				} else { // [EventName, ...]
					$unsubscribe[] = $params;
				}
			}
		}

		$namespace = $this->namespace;
		$unsubscribe = array_map(function ($eventName) use ($namespace) {
			list($ns, $event) = Event::parseName($eventName);
			return $ns === NULL ? $namespace . $event : $eventName;
		}, (array) $unsubscribe);

		$this->evm->removeEventListener($unsubscribe, $subscriber);
	}



	/**
	 * {@inheritDoc}
	 */
	public function setExceptionHandler(IExceptionHandler $exceptionHandler)
	{
		throw new NotSupportedException;
	}



	/**
	 * {@inheritDoc}
	 */
	public function createEvent($name, $defaults = array(), $argsClass = NULL)
	{
		return $this->evm->createEvent($this->namespace . $name, $defaults, $argsClass);
	}

}
