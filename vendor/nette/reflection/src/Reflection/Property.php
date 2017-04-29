<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Reflection;

use Nette;


/**
 * Reports information about a classes variable.
 * @property-read ClassType $declaringClass
 * @property-read IAnnotation[][] $annotations
 * @property-read string $description
 * @property-read string $name
 * @property  mixed $value
 * @property-read bool $public
 * @property-read bool $private
 * @property-read bool $protected
 * @property-read bool $static
 * @property-read bool $default
 * @property-read int $modifiers
 * @property-read string $docComment
 * @property-write bool $accessible
 */
class Property extends \ReflectionProperty
{
	use Nette\SmartObject;

	public function __toString()
	{
		return parent::getDeclaringClass()->getName() . '::$' . $this->getName();
	}


	/********************* Reflection layer ****************d*g**/


	/**
	 * @return ClassType
	 */
	public function getDeclaringClass()
	{
		return new ClassType(parent::getDeclaringClass()->getName());
	}


	/********************* Nette\Annotations support ****************d*g**/


	/**
	 * Has property specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return !empty($res[$name]);
	}


	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return IAnnotation
	 */
	public function getAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return isset($res[$name]) ? end($res[$name]) : NULL;
	}


	/**
	 * Returns all annotations.
	 * @return IAnnotation[][]
	 */
	public function getAnnotations()
	{
		return AnnotationsParser::getAll($this);
	}


	/**
	 * Returns value of annotation 'description'.
	 * @return string
	 */
	public function getDescription()
	{
		return $this->getAnnotation('description');
	}

}
