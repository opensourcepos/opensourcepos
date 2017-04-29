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
use TokenReflection;


class ClassTraitElementsExtractor
{

	/**
	 * @var ReflectionClass
	 */
	private $reflectionClass;

	/**
	 * @var TokenReflection\IReflection|ReflectionClass
	 */
	private $originalReflection;


	public function __construct(ReflectionClass $reflectionClass, TokenReflection\IReflection $originalReflection)
	{
		$this->reflectionClass = $reflectionClass;
		$this->originalReflection = $originalReflection;
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getDirectUsers()
	{
		$users = [];
		$name = $this->reflectionClass->getName();
		foreach ($this->reflectionClass->getParsedClasses() as $class) {
			if ( ! $class->isDocumented()) {
				continue;
			}
			if (in_array($name, $class->getOwnTraitNames())) {
				$users[] = $class;
			}
		}
		uksort($users, 'strcasecmp');
		return $users;
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getIndirectUsers()
	{
		$users = [];
		$name = $this->reflectionClass->getName();
		foreach ($this->reflectionClass->getParsedClasses() as $class) {
			if ( ! $class->isDocumented()) {
				continue;
			}
			if ($class->usesTrait($name) && ! in_array($name, $class->getOwnTraitNames())) {
				$users[] = $class;
			}
		}
		uksort($users, 'strcasecmp');
		return $users;
	}


	/**
	 * @return ReflectionProperty[]|array
	 */
	public function getTraitProperties()
	{
		$properties = [];
		$traitProperties = $this->originalReflection->getTraitProperties($this->reflectionClass->getVisibilityLevel());
		foreach ($traitProperties as $property) {
			$apiProperty = $this->reflectionClass->getReflectionFactory()->createFromReflection($property);
			if ( ! $this->reflectionClass->isDocumented() || $apiProperty->isDocumented()) {
				/** @var ReflectionProperty $property */
				$properties[$property->getName()] = $apiProperty;
			}
		}
		return $properties;
	}


	/**
	 * @return ReflectionMethod[]|array
	 */
	public function getTraitMethods()
	{
		$methods = [];
		foreach ($this->originalReflection->getTraitMethods($this->reflectionClass->getVisibilityLevel()) as $method) {
			$apiMethod = $this->reflectionClass->getReflectionFactory()->createFromReflection($method);
			if ( ! $this->reflectionClass->isDocumented() || $apiMethod->isDocumented()) {
				/** @var ReflectionMethod $method */
				$methods[$method->getName()] = $apiMethod;
			}
		}
		return $methods;
	}


	/**
	 * @return array {[ traitName => ReflectionProperty[] ]}
	 */
	public function getUsedProperties()
	{
		$allProperties = array_flip(array_map(function (ReflectionProperty $property) {
			return $property->getName();
		}, $this->reflectionClass->getOwnProperties()));

		$properties = [];
		foreach ($this->reflectionClass->getTraits() as $trait) {
			if ( ! $trait instanceof ReflectionClass) {
				continue;
			}

			$usedProperties = [];
			foreach ($trait->getOwnProperties() as $property) {
				if ( ! array_key_exists($property->getName(), $allProperties)) {
					$usedProperties[$property->getName()] = $property;
					$allProperties[$property->getName()] = NULL;
				}
			}

			if ( ! empty($usedProperties)) {
				ksort($usedProperties);
				$properties[$trait->getName()] = array_values($usedProperties);
			}
		}
		return $properties;
	}


	/**
	 * @return array {[ traitName => ReflectionMethod[] ]}
	 */
	public function getUsedMethods()
	{
		$usedMethods = [];
		foreach ($this->reflectionClass->getMethods() as $method) {
			if ($method->getDeclaringTraitName() === NULL
				|| $method->getDeclaringTraitName() === $this->reflectionClass->getName()
			) {
				continue;
			}

			$usedMethods[$method->getDeclaringTraitName()][$method->getName()]['method'] = $method;
			if ($method->getOriginalName() !== NULL && $method->getOriginalName() !== $method->getName()) {
				$usedMethods[$method->getDeclaringTraitName()][$method->getName()]['aliases'][$method->getName()] = $method;
			}
		}
		return $usedMethods;
	}

}
