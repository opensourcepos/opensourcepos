<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection\Extractors;

use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionMethodMagic;


class MagicMethodExtractor
{

	/**
	 * @return ReflectionMethodMagic[]|array
	 */
	public function extractFromClass(ReflectionClass $reflectionClass)
	{
		$methods = [];

		if ($parentClass = $reflectionClass->getParentClass()) {
			$methods += $this->extractFromParentClass($parentClass, $reflectionClass->isDocumented());
		}

		if ($traits = $reflectionClass->getTraits()) {
			$methods += $this->extractFromTraits($traits, $reflectionClass->isDocumented());
		}
		return $methods;
	}


	/**
	 * @param ReflectionClass $parent
	 * @param bool $isDocumented
	 * @return ReflectionMethodMagic[]
	 */
	private function extractFromParentClass(ReflectionClass $parent, $isDocumented)
	{
		$methods = [];
		while ($parent) {
			$methods = $this->extractOwnFromClass($parent, $isDocumented, $methods);
			$parent = $parent->getParentClass();
		}
		return $methods;
	}


	/**
	 * @param array $traits
	 * @param bool $isDocumented
	 * @return ReflectionMethodMagic[]
	 */
	private function extractFromTraits($traits, $isDocumented)
	{
		$methods = [];
		foreach ($traits as $trait) {
			if ( ! $trait instanceof ReflectionClass) {
				continue;
			}
			$methods = $this->extractOwnFromClass($trait, $isDocumented, $methods);
		}
		return $methods;
	}


	/**
	 * @param ReflectionClass $reflectionClass
	 * @param bool $isDocumented
	 * @param array $methods
	 * @return ReflectionMethodMagic[]
	 */
	private function extractOwnFromClass(ReflectionClass $reflectionClass, $isDocumented, array $methods)
	{
		foreach ($reflectionClass->getOwnMagicMethods() as $method) {
			if ($this->canBeExtracted($isDocumented, $methods, $method)) {
				$methods[$method->getName()] = $method;
			}
		}
		return $methods;
	}


	/**
	 * @param bool $isDocumented
	 * @param array $methods
	 * @param ReflectionMethodMagic $method
	 * @return bool
	 */
	private function canBeExtracted($isDocumented, array $methods, ReflectionMethodMagic $method)
	{
		if (isset($methods[$method->getName()])) {
			return FALSE;
		}
		if ($isDocumented && ! $method->isDocumented()) {
			return FALSE;
		}
		return TRUE;
	}

}
