<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Events;

use Doctrine;
use Doctrine\Common\EventSubscriber;
use Kdyby;
use Nette;
use Nette\Utils\ObjectMixin;



if (!class_exists('Nette\Utils\ObjectMixin')) {
	class_alias('Nette\ObjectMixin', 'Nette\Utils\ObjectMixin');
}

/**
 * Registry of system-wide listeners that get's invoked, when the event, that they are listening to, is dispatched.
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
class EventManager extends Doctrine\Common\EventManager
{

	/**
	 * [Event => [Priority => [[Listener, method], Subscriber, Subscriber, ...]]]
	 *
	 * @var array[]
	 */
	private $listeners = array();

	/**
	 * [Event => [Subscriber, Subscriber, [callable], ...]]
	 *
	 * @var array[]
	 */
	private $sorted = array();

	/**
	 * [SubscriberHash => Subscriber]
	 *
	 * @var array[]
	 */
	private $subscribers = array();

	/**
	 * @var Diagnostics\Panel
	 */
	private $panel;

	/**
	 * @var IExceptionHandler
	 */
	private $exceptionHandler;



	/**
	 * @internal
	 * @param Diagnostics\Panel $panel
	 */
	public function setPanel(Diagnostics\Panel $panel)
	{
		$this->panel = $panel;
	}



	/**
	 * @param IExceptionHandler $exceptionHandler
	 */
	public function setExceptionHandler(IExceptionHandler $exceptionHandler)
	{
		$this->exceptionHandler = $exceptionHandler;
	}



	/**
	 * Dispatches an event to all registered listeners.
	 *
	 * @param string $eventName The name of the event to dispatch. The name of the event is the name of the method that is invoked on listeners.
	 * @param Doctrine\Common\EventArgs $eventArgs The event arguments to pass to the event handlers/listeners. If not supplied, the single empty EventArgs instance is used
	 */
	public function dispatchEvent($eventName, Doctrine\Common\EventArgs $eventArgs = NULL)
	{
		if ($this->panel) {
			$this->panel->eventDispatch($eventName, $eventArgs);
		}

		list($namespace, $event) = Event::parseName($eventName);
		foreach ($this->getListeners($eventName) as $listener) {
			try {
				if ($listener instanceof EventSubscriber) {
					$listener = array($listener, $event);
				}

				if ($eventArgs instanceof EventArgsList) {
					/** @var EventArgsList $eventArgs */
					call_user_func_array($listener, $eventArgs->getArgs());

				} else {
					call_user_func($listener, $eventArgs);
				}

			} catch (\Exception $e) {
				if ($this->exceptionHandler) {
					$this->exceptionHandler->handleException($e);
				} else {
					throw $e;
				}
			}
		}

		if ($this->panel) {
			$this->panel->eventDispatched($eventName, $eventArgs);
		}
	}



	/**
	 * Gets the listeners of a specific event or all listeners.
	 *
	 * @param string $eventName
	 * @return Doctrine\Common\EventSubscriber[]|callable[]
	 */
	public function getListeners($eventName = NULL)
	{
		if ($eventName !== NULL) {
			if (!isset($this->sorted[$eventName])) {
				$this->sortListeners($eventName);
			}

			return $this->sorted[$eventName];
		}

		foreach ($this->listeners as $event => $prioritized) {
			if (!isset($this->sorted[$event])) {
				$this->sortListeners($event);
			}
		}

		return array_filter($this->sorted);
	}



	/**
	 * Checks whether an event has any registered listeners.
	 *
	 * @param string $eventName
	 * @return boolean TRUE if the specified event has any listeners, FALSE otherwise.
	 */
	public function hasListeners($eventName)
	{
		return (bool) count($this->getListeners($eventName));
	}



	/**
	 * Adds an event listener that listens on the specified events.
	 *
	 * @param string|array $events The event(s) to listen on.
	 * @param Doctrine\Common\EventSubscriber|array $subscriber The listener object.
	 * @param int $priority
	 *
	 * @throws InvalidListenerException
	 */
	public function addEventListener($events, $subscriber, $priority = 0)
	{
		foreach ((array) $events as $eventName) {
			list($namespace, $event) = Event::parseName($eventName);
			$callback = !is_array($subscriber) ? array($subscriber, $event) : $subscriber;

			if (!method_exists($callback[0], $callback[1])) {
				throw new InvalidListenerException("Event listener '" . get_class($callback[0]) . "' has no method '" . $callback[1] . "'");
			}

			$this->listeners[$eventName][$priority][] = $subscriber;
			unset($this->sorted[$eventName]);
		}
	}



	/**
	 * Removes an event listener from the specified events.
	 *
	 * @param string|array $unsubscribe
	 * @param Doctrine\Common\EventSubscriber|array $subscriber
	 */
	public function removeEventListener($unsubscribe, $subscriber = NULL)
	{
		if ($unsubscribe instanceof EventSubscriber) {
			list($unsubscribe, $subscriber) = $this->extractSubscriber($unsubscribe);
		}

		foreach ((array) $unsubscribe as $eventName) {
			$eventName = ltrim($eventName, '\\');
			foreach ($this->listeners[$eventName] as $priority => $listeners) {
				foreach ($listeners as $k => $listener) {
					if (!($listener == $subscriber || (is_array($listener) && $listener[0] == $subscriber))) {
						continue(2);
					}
					$key = $k;
					break;
				}

				unset($this->listeners[$eventName][$priority][$key]);
				if (empty($this->listeners[$eventName][$priority])) {
					unset($this->listeners[$eventName][$priority]);
				}
				if (empty($this->listeners[$eventName])) {
					unset($this->listeners[$eventName]);
					// there are no listeners for this specific event, so no reason to call sort on next dispatch
					$this->sorted[$eventName] = array();
				} else {
					// otherwise it needs to be sorted again
					unset($this->sorted[$eventName]);
				}

			}
		}
	}



	/**
	 * @param EventSubscriber $unsubscribe
	 * @return array
	 */
	protected function extractSubscriber(EventSubscriber $unsubscribe)
	{
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

		unset($this->subscribers[spl_object_hash($subscriber)]);

		return array($unsubscribe, $subscriber);
	}



	public function addEventSubscriber(EventSubscriber $subscriber)
	{
		if (isset($this->subscribers[$hash = spl_object_hash($subscriber)])) {
			return;
		}
		$this->subscribers[$hash] = $subscriber;

		foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
			if (is_numeric($eventName) && is_string($params)) { // [EventName, ...]
				$this->addEventListener($params, $subscriber);

			} elseif (is_string($eventName)) { // [EventName => ???, ...]
				if (is_string($params)) { // [EventName => method, ...]
					$this->addEventListener($eventName, array($subscriber, $params));

				} elseif (is_string($params[0])) { // [EventName => [method, priority], ...]
					$this->addEventListener($eventName, array($subscriber, $params[0]), isset($params[1]) ? $params[1] : 0);

				} else {
					foreach ($params as $listener) { // [EventName => [[method, priority], ...], ...]
						$this->addEventListener($eventName, array($subscriber, $listener[0]), isset($listener[1]) ? $listener[1] : 0);
					}
				}
			}
		}
	}



	public function removeEventSubscriber(EventSubscriber $subscriber)
	{
		$this->removeEventListener($subscriber);
	}



	/**
	 * @param string|array $name
	 * @param array $defaults
	 * @param string $argsClass
	 * @return Event
	 */
	public function createEvent($name, $defaults = array(), $argsClass = NULL)
	{
		$event = new Event($name, $defaults, $argsClass);
		$event->injectEventManager($this);

		if ($this->panel) {
			$this->panel->registerEvent($event);
		}

		return $event;
	}



	private function sortListeners($eventName)
	{
		$this->sorted[$eventName] = array();

		$available = array();
		list($namespace, $event, $separator) = Event::parseName($eventName);
		$className = $namespace;
		do {
			if (empty($this->listeners[$key = ($className ? $className . $separator : '') . $event])) {
				continue;
			}

			$available = $available + array_fill_keys(array_keys($this->listeners[$key]), array());
			foreach ($this->listeners[$key] as $priority => $listeners) {
				foreach ($listeners as $listener) {
					if ($className === $namespace && in_array($listener, $available[$priority], TRUE)) {
						continue;
					}

					$available[$priority][] = $listener;
				}
			}

		} while ($className && class_exists($className) && ($className = get_parent_class($className)));

		if (empty($available)) {
			return;
		}

		krsort($available); // [priority => [[listener, ...], ...]
		$sorted = call_user_func_array('array_merge', $available);

		$this->sorted[$eventName] = array_map(function ($callable) use ($event) {
			if ($callable instanceof EventSubscriber) {
				return $callable;
			}

			if (is_object($callable) && method_exists($callable, $event)) {
				$callable = array($callable, $event);
			}

			return $callable;
		}, $sorted); // [callback, ...]
	}



	/*************************** Nette\Object ***************************/



	/**
	 * Access to reflection.
	 * @return \Nette\Reflection\ClassType
	 */
	public static function getReflection()
	{
		return new Nette\Reflection\ClassType(get_called_class());
	}



	/**
	 * Call to undefined method.
	 *
	 * @param string $name
	 * @param array $args
	 *
	 * @throws \Nette\MemberAccessException
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}



	/**
	 * Call to undefined static method.
	 *
	 * @param string $name
	 * @param array $args
	 *
	 * @throws \Nette\MemberAccessException
	 * @return mixed
	 */
	public static function __callStatic($name, $args)
	{
		ObjectMixin::callStatic(get_called_class(), $name, $args);
	}



	/**
	 * Adding method to class.
	 *
	 * @param $name
	 * @param null $callback
	 *
	 * @throws \Nette\MemberAccessException
	 * @return callable|null
	 */
	public static function extensionMethod($name, $callback = NULL)
	{
		if (strpos($name, '::') === FALSE) {
			$class = get_called_class();
		} else {
			list($class, $name) = explode('::', $name);
		}
		if ($callback === NULL) {
			return ObjectMixin::getExtensionMethod($class, $name);
		} else {
			ObjectMixin::setExtensionMethod($class, $name, $callback);
		}
	}



	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param string $name
	 *
	 * @throws \Nette\MemberAccessException
	 * @return mixed
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	/**
	 * Sets value of a property. Do not call directly.
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @throws \Nette\MemberAccessException
	 * @return void
	 */
	public function __set($name, $value)
	{
		ObjectMixin::set($this, $name, $value);
	}



	/**
	 * Is property defined?
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	/**
	 * Access to undeclared property.
	 *
	 * @param string $name
	 *
	 * @throws \Nette\MemberAccessException
	 * @return void
	 */
	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}
