<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Parser;

use ApiGen\Parser\Elements\Elements;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionElement;
use ArrayObject;


class ParserResult
{

	/**
	 * @var ArrayObject
	 */
	private $classes;

	/**
	 * @var ArrayObject
	 */
	private $constants;

	/**
	 * @var ArrayObject
	 */
	private $functions;

	/**
	 * @var ArrayObject
	 */
	private $internalClasses;

	/**
	 * @var ArrayObject
	 */
	private $tokenizedClasses;

	/**
	 * @var array
	 */
	private $types = [Elements::CLASSES, Elements::CONSTANTS, Elements::FUNCTIONS];


	public function __construct()
	{
		$this->classes = new ArrayObject;
		$this->constants = new ArrayObject;
		$this->functions = new ArrayObject;
		$this->internalClasses = new ArrayObject;
		$this->tokenizedClasses = new ArrayObject;
	}


	/**
	 * @param string $type
	 * @return ArrayObject
	 */
	public function getElementsByType($type)
	{
		if ($type === Elements::CLASSES) {
			return $this->classes;

		} elseif ($type === Elements::CONSTANTS) {
			return $this->constants;

		} elseif ($type === Elements::FUNCTIONS) {
			return $this->functions;
		}

		throw new \Exception("'$type' is not supported element type");
	}


	/**
	 * @return array
	 */
	public function getDocumentedStats()
	{
		return [
			'classes' => $this->getDocumentedElementsCount($this->tokenizedClasses),
			'constants' => $this->getDocumentedElementsCount($this->constants),
			'functions' => $this->getDocumentedElementsCount($this->functions),
			'internalClasses' => $this->getDocumentedElementsCount($this->internalClasses)
		];
	}


	/**
	 * @return ArrayObject
	 */
	public function getClasses()
	{
		return $this->classes;
	}


	/**
	 * @return ArrayObject
	 */
	public function getConstants()
	{
		return $this->constants;
	}


	/**
	 * @return ArrayObject
	 */
	public function getFunctions()
	{
		return $this->functions;
	}


	/**
	 * @return array
	 */
	public function getTypes()
	{
		return $this->types;
	}


	public function setClasses(ArrayObject $classes)
	{
		$this->classes = $classes;
	}


	public function setConstants(ArrayObject $constants)
	{
		$this->constants = $constants;
	}


	public function setFunctions(ArrayObject $functions)
	{
		$this->functions = $functions;
	}


	public function setInternalClasses(ArrayObject $internalClasses)
	{
		$this->internalClasses = $internalClasses;
	}


	public function setTokenizedClasses(ArrayObject $tokenizedClasses)
	{
		$this->tokenizedClasses = $tokenizedClasses;
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getDirectImplementersOfInterface(ReflectionClass $reflectionClass)
	{
		$implementers = [];
		foreach ($this->classes as $class) {
			if ($this->isAllowedDirectImplementer($class, $reflectionClass->getName())) {
				$implementers[] = $class;
			}
		}
		uksort($implementers, 'strcasecmp');
		return $implementers;
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getIndirectImplementersOfInterface(ReflectionClass $reflectionClass)
	{
		$implementers = [];
		foreach ($this->classes as $class) {
			if ($this->isAllowedIndirectImplementer($class, $reflectionClass->getName())) {
				$implementers[] = $class;
			}
		}
		uksort($implementers, 'strcasecmp');
		return $implementers;
	}


	/**
	 * @param ReflectionClass $class
	 * @param string $name
	 * @return bool
	 */
	private function isAllowedDirectImplementer(ReflectionClass $class, $name)
	{
		if ($class->isDocumented() && in_array($name, $class->getOwnInterfaceNames())) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @param ReflectionClass $class
	 * @param string $name
	 * @return bool
	 */
	private function isAllowedIndirectImplementer(ReflectionClass $class, $name)
	{
		if ($class->isDocumented() && $class->implementsInterface($name)
			&& ! in_array($name, $class->getOwnInterfaceNames())
		) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @param ReflectionElement[]|ArrayObject $result
	 * @return int
	 */
	private function getDocumentedElementsCount(ArrayObject $result)
	{
		$count = 0;
		foreach ($result as $element) {
			$count += (int) $element->isDocumented();
		}
		return $count;
	}

}
