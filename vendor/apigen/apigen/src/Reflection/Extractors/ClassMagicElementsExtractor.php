<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection\Extractors;

use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionMethod;
use ApiGen\Reflection\ReflectionMethodMagic;
use ApiGen\Reflection\ReflectionProperty;
use ApiGen\Reflection\ReflectionPropertyMagic;


class ClassMagicElementsExtractor
{

	/**
	 * @var ReflectionClass
	 */
	private $reflectionClass;

	/**
	 * @var ReflectionPropertyMagic[]
	 */
	private $ownMagicProperties;

	/**
	 * @var ReflectionMethodMagic[]
	 */
	private $ownMagicMethods;


	public function __construct(ReflectionClass $reflectionClass)
	{
		$this->reflectionClass = $reflectionClass;
	}


	/**
	 * @return ReflectionPropertyMagic[]
	 */
	public function getMagicProperties()
	{
		return $this->getOwnMagicProperties() + (new MagicPropertyExtractor)->extractFromClass($this->reflectionClass);
	}


	/**
	 * @return ReflectionMethodMagic[]
	 */
	public function getMagicMethods()
	{
		return $this->getOwnMagicMethods() + (new MagicMethodExtractor)->extractFromClass($this->reflectionClass);
	}


	/**
	 * @return ReflectionPropertyMagic[]|array
	 */
	public function getOwnMagicProperties()
	{
		if ($this->ownMagicProperties === NULL) {
			$this->ownMagicProperties = [];

			if ($this->reflectionClass->isVisibilityLevelPublic() && $this->reflectionClass->getDocComment()) {
				$extractor = new AnnotationPropertyExtractor($this->reflectionClass->getReflectionFactory());
				$this->ownMagicProperties += $extractor->extractFromReflection($this->reflectionClass);
			}
		}

		return $this->ownMagicProperties;
	}


	/**
	 * @return ReflectionMethodMagic[]
	 */
	public function getOwnMagicMethods()
	{
		if ($this->ownMagicMethods === NULL) {
			$this->ownMagicMethods = [];

			if ($this->reflectionClass->isVisibilityLevelPublic() && $this->reflectionClass->getDocComment()) {
				$extractor = new AnnotationMethodExtractor($this->reflectionClass->getReflectionFactory());
				$this->ownMagicMethods += $extractor->extractFromReflection($this->reflectionClass);
			}
		}
		return $this->ownMagicMethods;
	}


	/**
	 * @return array {[ declaringClassName => ReflectionPropertyMagic[] ]}
	 */
	public function getInheritedMagicProperties()
	{
		$properties = [];
		$allProperties = array_flip(array_map(function (ReflectionProperty $property) {
			return $property->getName();
		}, $this->getOwnMagicProperties()));

		foreach ($this->reflectionClass->getParentClasses() as $class) {
			$inheritedProperties = $this->getUsedElements($class->getOwnMagicProperties(), $allProperties);
			$properties = $this->sortElements($inheritedProperties, $properties, $class);
		}

		return $properties;
	}


	/**
	 * @return array {[ declaringClassName => ReflectionMethodMagic[] ]}
	 */
	public function getInheritedMagicMethods()
	{
		$methods = [];
		$allMethods = array_flip(array_map(function (ReflectionMethod $method) {
			return $method->getName();
		}, $this->getOwnMagicMethods()));

		/** @var ReflectionClass[] $parentClassesAndInterfaces */
		$parentClassesAndInterfaces = array_merge(
			$this->reflectionClass->getParentClasses(), $this->reflectionClass->getInterfaces()
		);
		foreach ($parentClassesAndInterfaces as $class) {
			$inheritedMethods = $this->getUsedElements($class->getOwnMagicMethods(), $allMethods);
			$methods = $this->sortElements($inheritedMethods, $methods, $class);
		}

		return $methods;
	}


	/**
	 * @return array {[ declaringTraitName => ReflectionPropertyMagic[] ]}
	 */
	public function getUsedMagicProperties()
	{
		$properties = [];
		$allProperties = array_flip(array_map(function (ReflectionProperty $property) {
			return $property->getName();
		}, $this->getOwnMagicProperties()));

		foreach ($this->reflectionClass->getTraits() as $trait) {
			if ( ! $trait instanceof ReflectionClass) {
				continue;
			}
			$usedProperties = $this->getUsedElements($trait->getOwnMagicProperties(), $allProperties);
			$properties = $this->sortElements($usedProperties, $properties, $trait);
		}

		return $properties;
	}


	/**
	 * @return ReflectionMethodMagic[]|array
	 */
	public function getUsedMagicMethods()
	{
		$usedMethods = [];
		foreach ($this->getMagicMethods() as $method) {
			$declaringTraitName = $method->getDeclaringTraitName();
			if ($declaringTraitName === NULL || $declaringTraitName === $this->reflectionClass->getName()) {
				continue;
			}
			$usedMethods[$declaringTraitName][$method->getName()]['method'] = $method;
		}
		return $usedMethods;
	}


	/**
	 * @param ReflectionElement[] $elementsToCheck
	 * @param array $allElements
	 * @return array
	 */
	private function getUsedElements(array $elementsToCheck, array &$allElements)
	{
		$elements = [];
		foreach ($elementsToCheck as $property) {
			if ( ! array_key_exists($property->getName(), $allElements)) {
				$elements[$property->getName()] = $property;
				$allElements[$property->getName()] = NULL;
			}
		}
		return $elements;
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
