<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * Object is the ultimate ancestor of all instantiable classes.
 */
abstract class Object
{

	/**
	 * Call to undefined method.
	 * @throws \LogicException
	 */
	public function __call($name, $args)
	{
		$class = method_exists($this, $name) ? 'parent' : get_class($this);
		throw new \LogicException(sprintf('Call to undefined method %s::%s().', $class, $name));
	}


	/**
	 * Access to undeclared property.
	 * @throws \LogicException
	 */
	public function &__get($name)
	{
		throw new \LogicException(sprintf('Cannot read an undeclared property %s::$%s.', get_class($this), $name));
	}


	/**
	 * Access to undeclared property.
	 * @throws \LogicException
	 */
	public function __set($name, $value)
	{
		throw new \LogicException(sprintf('Cannot write to an undeclared property %s::$%s.', get_class($this), $name));
	}


	/**
	 * @return bool
	 */
	public function __isset($name)
	{
		return FALSE;
	}


	/**
	 * Access to undeclared property.
	 * @throws \LogicException
	 */
	public function __unset($name)
	{
		throw new \LogicException(sprintf('Cannot unset the property %s::$%s.', get_class($this), $name));
	}

}
