<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette;

use Nette\Utils\Callback;
use Nette\Utils\ObjectMixin;


/**
 * Strict class for better experience.
 * - 'did you mean' hints
 * - access to undeclared members throws exceptions
 * - support for @property annotations
 * - support for calling event handlers stored in $onEvent via onEvent()
 * - compatible with Nette\Object
 */
trait SmartObject
{

	/**
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public function __call($name, $args)
	{
		$class = get_class($this);
		$isProp = ObjectMixin::hasProperty($class, $name);

		if ($name === '') {
			throw new MemberAccessException("Call to class '$class' method without name.");

		} elseif ($isProp === 'event') { // calling event handlers
			if (is_array($this->$name) || $this->$name instanceof \Traversable) {
				foreach ($this->$name as $handler) {
					Callback::invokeArgs($handler, $args);
				}
			} elseif ($this->$name !== NULL) {
				throw new UnexpectedValueException("Property $class::$$name must be array or NULL, " . gettype($this->$name) . ' given.');
			}

		} elseif ($isProp && $this->$name instanceof \Closure) { // closure in property
			trigger_error("Invoking closure in property via \$obj->$name() is deprecated" . ObjectMixin::getSource(), E_USER_DEPRECATED);
			return call_user_func_array($this->$name, $args);

		} elseif (($methods = &ObjectMixin::getMethods($class)) && isset($methods[$name]) && is_array($methods[$name])) { // magic @methods
			trigger_error("Magic methods such as $class::$name() are deprecated" . ObjectMixin::getSource(), E_USER_DEPRECATED);
			list($op, $rp, $type) = $methods[$name];
			if (count($args) !== ($op === 'get' ? 0 : 1)) {
				throw new InvalidArgumentException("$class::$name() expects " . ($op === 'get' ? 'no' : '1') . ' argument, ' . count($args) . ' given.');

			} elseif ($type && $args && !ObjectMixin::checkType($args[0], $type)) {
				throw new InvalidArgumentException("Argument passed to $class::$name() must be $type, " . gettype($args[0]) . ' given.');
			}

			if ($op === 'get') {
				return $rp->getValue($this);
			} elseif ($op === 'set') {
				$rp->setValue($this, $args[0]);
			} elseif ($op === 'add') {
				$val = $rp->getValue($this);
				$val[] = $args[0];
				$rp->setValue($this, $val);
			}
			return $this;

		} elseif ($cb = ObjectMixin::getExtensionMethod($class, $name)) { // extension methods
			trigger_error("Extension methods such as $class::$name() are deprecated" . ObjectMixin::getSource(), E_USER_DEPRECATED);
			return Callback::invoke($cb, $this, ...$args);

		} else {
			ObjectMixin::strictCall($class, $name);
		}
	}


	/**
	 * @return void
	 * @throws MemberAccessException
	 */
	public static function __callStatic($name, $args)
	{
		ObjectMixin::strictStaticCall(get_called_class(), $name);
	}


	/**
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name)
	{
		$class = get_class($this);
		$uname = ucfirst($name);

		if ($prop = ObjectMixin::getMagicProperty($class, $name)) { // property getter
			if (!($prop & 0b0001)) {
				throw new MemberAccessException("Cannot read a write-only property $class::\$$name.");
			}
			$m = ($prop & 0b0010 ? 'get' : 'is') . $uname;
			if ($prop & 0b0100) { // return by reference
				return $this->$m();
			} else {
				$val = $this->$m();
				return $val;
			}

		} elseif ($name === '') {
			throw new MemberAccessException("Cannot read a class '$class' property without name.");

		} elseif (($methods = &ObjectMixin::getMethods($class)) && isset($methods[$m = 'get' . $uname]) || isset($methods[$m = 'is' . $uname])) { // old property getter
			trigger_error("Use $m() or add annotation @property for $class::\$$name" . ObjectMixin::getSource(), E_USER_DEPRECATED);
			if ($methods[$m] === 0) {
				$methods[$m] = (new \ReflectionMethod($class, $m))->returnsReference();
			}
			if ($methods[$m] === TRUE) {
				return $this->$m();
			} else {
				$val = $this->$m();
				return $val;
			}

		} elseif (isset($methods[$name])) { // public method as closure getter
			trigger_error("Accessing methods as properties via \$obj->$name is deprecated" . ObjectMixin::getSource(), E_USER_DEPRECATED);
			$val = Callback::closure($this, $name);
			return $val;

		} elseif (isset($methods['set' . $uname])) { // property getter
			throw new MemberAccessException("Cannot read a write-only property $class::\$$name.");

		} else {
			ObjectMixin::strictGet($class, $name);
		}
	}


	/**
	 * @return void
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value)
	{
		$class = get_class($this);
		$uname = ucfirst($name);

		if (ObjectMixin::hasProperty($class, $name)) { // unsetted property
			$this->$name = $value;

		} elseif ($prop = ObjectMixin::getMagicProperty($class, $name)) { // property setter
			if (!($prop & 0b1000)) {
				throw new MemberAccessException("Cannot write to a read-only property $class::\$$name.");
			}
			$this->{'set' . $name}($value);

		} elseif ($name === '') {
			throw new MemberAccessException("Cannot write to a class '$class' property without name.");

		} elseif (($methods = &ObjectMixin::getMethods($class)) && isset($methods[$m = 'set' . $uname])) { // old property setter
			trigger_error("Use $m() or add annotation @property for $class::\$$name" . ObjectMixin::getSource(), E_USER_DEPRECATED);
			$this->$m($value);

		} elseif (isset($methods['get' . $uname]) || isset($methods['is' . $uname])) { // property setter
			throw new MemberAccessException("Cannot write to a read-only property $class::\$$name.");

		} else {
			ObjectMixin::strictSet($class, $name);
		}
	}


	/**
	 * @return void
	 * @throws MemberAccessException
	 */
	public function __unset($name)
	{
		$class = get_class($this);
		if (!ObjectMixin::hasProperty($class, $name)) {
			throw new MemberAccessException("Cannot unset the property $class::\$$name.");
		}
	}


	/**
	 * @return bool
	 */
	public function __isset($name)
	{
		$uname = ucfirst($name);
		return ObjectMixin::getMagicProperty(get_class($this), $name)
			|| ($name !== '' && ($methods = ObjectMixin::getMethods(get_class($this))) && (isset($methods['get' . $uname]) || isset($methods['is' . $uname])));
	}


	/**
	 * @return Reflection\ClassType|\ReflectionClass
	 * @deprecated
	 */
	public static function getReflection()
	{
		trigger_error(get_called_class() . '::getReflection() is deprecated' . ObjectMixin::getSource(), E_USER_DEPRECATED);
		$class = class_exists(Reflection\ClassType::class) ? Reflection\ClassType::class : \ReflectionClass::class;
		return new $class(get_called_class());
	}


	/**
	 * @return mixed
	 * @deprecated use Nette\Utils\ObjectMixin::setExtensionMethod()
	 */
	public static function extensionMethod($name, $callback = NULL)
	{
		if (strpos($name, '::') === FALSE) {
			$class = get_called_class();
		} else {
			list($class, $name) = explode('::', $name);
			$class = (new \ReflectionClass($class))->getName();
		}
		trigger_error("Extension methods such as $class::$name() are deprecated" . ObjectMixin::getSource(), E_USER_DEPRECATED);
		if ($callback === NULL) {
			return ObjectMixin::getExtensionMethod($class, $name);
		} else {
			ObjectMixin::setExtensionMethod($class, $name, $callback);
		}
	}

}
