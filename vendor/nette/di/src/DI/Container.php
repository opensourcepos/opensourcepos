<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\DI;

use Nette;


/**
 * The dependency injection container default implementation.
 */
class Container
{
	use Nette\SmartObject;

	const TAGS = 'tags';
	const TYPES = 'types';
	const SERVICES = 'services';
	const ALIASES = 'aliases';

	/** @var array  user parameters */
	/*private*/public $parameters = [];

	/** @var object[]  storage for shared objects */
	private $registry = [];

	/** @var array[] */
	protected $meta = [];

	/** @var array circular reference detector */
	private $creating;


	public function __construct(array $params = [])
	{
		$this->parameters = $params + $this->parameters;
	}


	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	/**
	 * Adds the service to the container.
	 * @param  string
	 * @param  object
	 * @return static
	 */
	public function addService($name, $service)
	{
		if (!is_string($name) || !$name) {
			throw new Nette\InvalidArgumentException(sprintf('Service name must be a non-empty string, %s given.', gettype($name)));

		}
		$name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
		if (isset($this->registry[$name])) {
			throw new Nette\InvalidStateException("Service '$name' already exists.");

		} elseif (!is_object($service)) {
			throw new Nette\InvalidArgumentException(sprintf("Service '%s' must be a object, %s given.", $name, gettype($service)));

		} elseif (isset($this->meta[self::SERVICES][$name]) && !$service instanceof $this->meta[self::SERVICES][$name]) {
			throw new Nette\InvalidArgumentException(sprintf("Service '%s' must be instance of %s, %s given.", $name, $this->meta[self::SERVICES][$name], get_class($service)));
		}

		$this->registry[$name] = $service;
		return $this;
	}


	/**
	 * Removes the service from the container.
	 * @param  string
	 * @return void
	 */
	public function removeService($name)
	{
		$name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
		unset($this->registry[$name]);
	}


	/**
	 * Gets the service object by name.
	 * @param  string
	 * @return object
	 * @throws MissingServiceException
	 */
	public function getService($name)
	{
		if (!isset($this->registry[$name])) {
			if (isset($this->meta[self::ALIASES][$name])) {
				return $this->getService($this->meta[self::ALIASES][$name]);
			}
			$this->registry[$name] = $this->createService($name);
		}
		return $this->registry[$name];
	}


	/**
	 * Gets the service type by name.
	 * @param  string
	 * @return string
	 * @throws MissingServiceException
	 */
	public function getServiceType($name)
	{
		if (isset($this->meta[self::ALIASES][$name])) {
			return $this->getServiceType($this->meta[self::ALIASES][$name]);

		} elseif (isset($this->meta[self::SERVICES][$name])) {
			return $this->meta[self::SERVICES][$name];

		} else {
			throw new MissingServiceException("Service '$name' not found.");
		}
	}


	/**
	 * Does the service exist?
	 * @param  string service name
	 * @return bool
	 */
	public function hasService($name)
	{
		$name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
		return isset($this->registry[$name])
			|| (method_exists($this, $method = self::getMethodName($name))
				&& (new \ReflectionMethod($this, $method))->getName() === $method);
	}


	/**
	 * Is the service created?
	 * @param  string service name
	 * @return bool
	 */
	public function isCreated($name)
	{
		if (!$this->hasService($name)) {
			throw new MissingServiceException("Service '$name' not found.");
		}
		$name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
		return isset($this->registry[$name]);
	}


	/**
	 * Creates new instance of the service.
	 * @param  string service name
	 * @return object
	 * @throws MissingServiceException
	 */
	public function createService($name, array $args = [])
	{
		$name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
		$method = self::getMethodName($name);
		if (isset($this->creating[$name])) {
			throw new Nette\InvalidStateException(sprintf('Circular reference detected for services: %s.', implode(', ', array_keys($this->creating))));

		} elseif (!method_exists($this, $method) || (new \ReflectionMethod($this, $method))->getName() !== $method) {
			throw new MissingServiceException("Service '$name' not found.");
		}

		try {
			$this->creating[$name] = TRUE;
			$service = $this->$method(...$args);

		} finally {
			unset($this->creating[$name]);
		}

		if (!is_object($service)) {
			throw new Nette\UnexpectedValueException("Unable to create service '$name', value returned by method $method() is not object.");
		}

		return $service;
	}


	/**
	 * Resolves service by type.
	 * @param  string  class or interface
	 * @param  bool    throw exception if service doesn't exist?
	 * @return object  service or NULL
	 * @throws MissingServiceException
	 */
	public function getByType($class, $throw = TRUE)
	{
		$class = ltrim($class, '\\');
		if (!empty($this->meta[self::TYPES][$class][TRUE])) {
			if (count($names = $this->meta[self::TYPES][$class][TRUE]) === 1) {
				return $this->getService($names[0]);
			}
			throw new MissingServiceException("Multiple services of type $class found: " . implode(', ', $names) . '.');

		} elseif ($throw) {
			throw new MissingServiceException("Service of type $class not found.");
		}
	}


	/**
	 * Gets the service names of the specified type.
	 * @param  string
	 * @return string[]
	 */
	public function findByType($class)
	{
		$class = ltrim($class, '\\');
		return empty($this->meta[self::TYPES][$class])
			? []
			: array_merge(...array_values($this->meta[self::TYPES][$class]));
	}


	/**
	 * Gets the service names of the specified tag.
	 * @param  string
	 * @return array of [service name => tag attributes]
	 */
	public function findByTag($tag)
	{
		return isset($this->meta[self::TAGS][$tag]) ? $this->meta[self::TAGS][$tag] : [];
	}


	/********************* autowiring ****************d*g**/


	/**
	 * Creates new instance using autowiring.
	 * @param  string  class
	 * @param  array   arguments
	 * @return object
	 * @throws Nette\InvalidArgumentException
	 */
	public function createInstance($class, array $args = [])
	{
		$rc = new \ReflectionClass($class);
		if (!$rc->isInstantiable()) {
			throw new ServiceCreationException("Class $class is not instantiable.");

		} elseif ($constructor = $rc->getConstructor()) {
			return $rc->newInstanceArgs(Helpers::autowireArguments($constructor, $args, $this));

		} elseif ($args) {
			throw new ServiceCreationException("Unable to pass arguments, class $class has no constructor.");
		}
		return new $class;
	}


	/**
	 * Calls all methods starting with with "inject" using autowiring.
	 * @param  object
	 * @return void
	 */
	public function callInjects($service)
	{
		Extensions\InjectExtension::callInjects($this, $service);
	}


	/**
	 * Calls method using autowiring.
	 * @return mixed
	 */
	public function callMethod(callable $function, array $args = [])
	{
		return $function(...Helpers::autowireArguments(Nette\Utils\Callback::toReflection($function), $args, $this));
	}


	/********************* shortcuts ****************d*g**/


	/**
	 * Expands %placeholders%.
	 * @param  mixed
	 * @return mixed
	 * @deprecated
	 */
	public function expand($s)
	{
		return Helpers::expand($s, $this->parameters);
	}


	/** @deprecated */
	public function &__get($name)
	{
		trigger_error(__METHOD__ . ' is deprecated; use getService().', E_USER_DEPRECATED);
		$tmp = $this->getService($name);
		return $tmp;
	}


	/** @deprecated */
	public function __set($name, $service)
	{
		trigger_error(__METHOD__ . ' is deprecated; use addService().', E_USER_DEPRECATED);
		$this->addService($name, $service);
	}


	/** @deprecated */
	public function __isset($name)
	{
		trigger_error(__METHOD__ . ' is deprecated; use hasService().', E_USER_DEPRECATED);
		return $this->hasService($name);
	}


	/** @deprecated */
	public function __unset($name)
	{
		trigger_error(__METHOD__ . ' is deprecated; use removeService().', E_USER_DEPRECATED);
		$this->removeService($name);
	}


	public static function getMethodName($name)
	{
		$uname = ucfirst($name);
		return 'createService' . ((string) $name === $uname ? '__' : '') . str_replace('.', '__', $uname);
	}

}
