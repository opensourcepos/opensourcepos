<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;
use Nette\Reflection\Method;


/**
 * @internal
 */
class MethodReflection extends \ReflectionMethod
{
	use Nette\SmartObject;

	/**
	 * Has method specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		return (bool) ComponentReflection::parseAnnotation($this, $name);
	}


	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return mixed
	 */
	public function getAnnotation($name)
	{
		$res = ComponentReflection::parseAnnotation($this, $name);
		return $res ? end($res) : NULL;
	}


	public function __toString()
	{
		trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
		return parent::getDeclaringClass()->getName() . '::' . $this->getName() . '()';
	}


	public function __get($name)
	{
		trigger_error("getMethod('{$this->getName()}')->$name is deprecated.", E_USER_DEPRECATED);
		return (new Method(parent::getDeclaringClass()->getName(), $this->getName()))->$name;
	}


	public function __call($name, $args)
	{
		trigger_error("getMethod('{$this->getName()}')->$name() is deprecated, use Nette\\Reflection\\Method::from(\$presenter, '{$this->getName()}')->$name()", E_USER_DEPRECATED);
		return call_user_func_array([new Method(parent::getDeclaringClass()->getName(), $this->getName()), $name], $args);
	}

}
