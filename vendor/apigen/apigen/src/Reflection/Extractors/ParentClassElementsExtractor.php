<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection\Extractors;

use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionMethod;
use ApiGen\Reflection\ReflectionProperty;


class ParentClassElementsExtractor
{

	/**
	 * @var ReflectionClass
	 */
	private $reflectionClass;


	public function __construct(ReflectionClass $reflectionClass)
	{
		$this->reflectionClass = $reflectionClass;
	}


	/**
	 * @return array
	 */
	public function getInheritedConstants()
	{
		return array_filter(
			array_map(
				function (ReflectionClass $class) {
					$reflections = $class->getOwnConstants();
					ksort($reflections);
					return $reflections;
				},
				$this->getParentClassesAndInterfaces()
			)
		);
	}


	/**
	 * @return array {[ className => ReflectionProperties[] ]}
	 */
	public function getInheritedProperties()
	{
		$properties = [];
		$allProperties = array_flip(array_map(function (ReflectionProperty $property) {
			return $property->getName();
		}, $this->reflectionClass->getOwnProperties()));

		foreach ($this->reflectionClass->getParentClasses() as $class) {
			$inheritedProperties = [];
			foreach ($class->getOwnProperties() as $property) {
				if ( ! array_key_exists($property->getName(), $allProperties) && ! $property->isPrivate()) {
					$inheritedProperties[$property->getName()] = $property;
					$allProperties[$property->getName()] = NULL;
				}
			}
			$properties = $this->sortElements($inheritedProperties, $properties, $class);
		}

		return $properties;

	}


	/**
	 * @return array {[ className => ReflectionMethod[] ]}
	 */
	public function getInheritedMethods()
	{
		$methods = [];
		$allMethods = array_flip(array_map(function (ReflectionMethod $method) {
			return $method->getName();
		}, $this->reflectionClass->getOwnMethods()));

		foreach ($this->getParentClassesAndInterfaces() as $class) {
			$inheritedMethods = [];
			foreach ($class->getOwnMethods() as $method) {
				if ( ! array_key_exists($method->getName(), $allMethods) && ! $method->isPrivate()) {
					$inheritedMethods[$method->getName()] = $method;
					$allMethods[$method->getName()] = NULL;
				}
			}
			$methods = $this->sortElements($inheritedMethods, $methods, $class);
		}

		return $methods;
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	private function getParentClassesAndInterfaces()
	{
		return array_merge($this->reflectionClass->getParentClasses(), $this->reflectionClass->getInterfaces());
	}


	/**
	 * @return array
	 */
	private function sortElements(array $elements, array $allElements, ReflectionClass $reflectionClass)
	{
		if ( ! empty($elements)) {
			ksort($elements);
			$allElements[$reflectionClass->getName()] = array_values($elements);
		}
		return $allElements;
	}

}
