<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Reflection;

use Nette;


/**
 * Reports information about a class.
 * @property-read Method $constructor
 * @property-read Extension $extension
 * @property-read ClassType[] $interfaces
 * @property-read Method[] $methods
 * @property-read ClassType $parentClass
 * @property-read Property[] $properties
 * @property-read IAnnotation[][] $annotations
 * @property-read string $description
 * @property-read string $name
 * @property-read bool $internal
 * @property-read bool $userDefined
 * @property-read bool $instantiable
 * @property-read string $fileName
 * @property-read int $startLine
 * @property-read int $endLine
 * @property-read string $docComment
 * @property-read mixed[] $constants
 * @property-read string[] $interfaceNames
 * @property-read bool $interface
 * @property-read bool $abstract
 * @property-read bool $final
 * @property-read int $modifiers
 * @property-read array $staticProperties
 * @property-read array $defaultProperties
 * @property-read bool $iterateable
 * @property-read string $extensionName
 * @property-read string $namespaceName
 * @property-read string $shortName
 */
class ClassType extends \ReflectionClass
{
	use Nette\SmartObject;

	/**
	 * @param  string|object
	 * @return static
	 */
	public static function from($class)
	{
		return new static($class);
	}


	public function __toString()
	{
		return $this->getName();
	}


	/**
	 * @param  string
	 * @return bool
	 */
	public function is($type)
	{
		return is_a($this->getName(), $type, TRUE);
	}


	/********************* Reflection layer ****************d*g**/


	/**
	 * @return Method|NULL
	 */
	public function getConstructor()
	{
		return ($ref = parent::getConstructor()) ? Method::from($this->getName(), $ref->getName()) : NULL;
	}


	/**
	 * @return Extension|NULL
	 */
	public function getExtension()
	{
		return ($name = $this->getExtensionName()) ? new Extension($name) : NULL;
	}


	/**
	 * @return static[]
	 */
	public function getInterfaces()
	{
		$res = [];
		foreach (parent::getInterfaceNames() as $val) {
			$res[$val] = new static($val);
		}
		return $res;
	}


	/**
	 * @return Method
	 */
	public function getMethod($name)
	{
		return new Method($this->getName(), $name);
	}


	/**
	 * @return Method[]
	 */
	public function getMethods($filter = -1)
	{
		foreach ($res = parent::getMethods($filter) as $key => $val) {
			$res[$key] = new Method($this->getName(), $val->getName());
		}
		return $res;
	}


	/**
	 * @return static|NULL
	 */
	public function getParentClass()
	{
		return ($ref = parent::getParentClass()) ? new static($ref->getName()) : NULL;
	}


	/**
	 * @return Property[]
	 */
	public function getProperties($filter = -1)
	{
		foreach ($res = parent::getProperties($filter) as $key => $val) {
			$res[$key] = new Property($this->getName(), $val->getName());
		}
		return $res;
	}


	/**
	 * @return Property
	 */
	public function getProperty($name)
	{
		return new Property($this->getName(), $name);
	}


	/********************* Nette\Annotations support ****************d*g**/


	/**
	 * Has class specified annotation?
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
