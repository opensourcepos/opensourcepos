<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection\Extractors;

use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionPropertyMagic;


class MagicPropertyExtractor
{

	/**
	 * @return ReflectionPropertyMagic[]|array
	 */
	public function extractFromClass(ReflectionClass $reflectionClass)
	{
		$properties = [];
		if ($parentClass = $reflectionClass->getParentClass()) {
			$properties += $this->extractFromParentClass($parentClass, $reflectionClass->isDocumented());
		}

		if ($traits = $reflectionClass->getTraits()) {
			$properties += $this->extractFromTraits($traits, $reflectionClass->isDocumented());
		}
		return $properties;
	}


	/**
	 * @param ReflectionClass $parent
	 * @param bool $isDocumented
	 * @return ReflectionPropertyMagic[]
	 */
	private function extractFromParentClass(ReflectionClass $parent, $isDocumented)
	{
		$properties = [];
		while ($parent) {
			$properties = $this->extractOwnFromClass($parent, $isDocumented, $properties);
			$parent = $parent->getParentClass();
		}
		return $properties;
	}


	/**
	 * @param array $traits
	 * @param bool $isDocumented
	 * @return ReflectionPropertyMagic[]
	 */
	private function extractFromTraits($traits, $isDocumented)
	{
		$properties = [];
		foreach ($traits as $trait) {
			if ( ! $trait instanceof ReflectionClass) {
				continue;
			}
			$properties = $this->extractOwnFromClass($trait, $isDocumented, $properties);
		}
		return $properties;
	}


	/**
	 * @param ReflectionClass $reflectionClass
	 * @param bool $isDocumented
	 * @param array $properties
	 * @return ReflectionPropertyMagic[]
	 */
	private function extractOwnFromClass(ReflectionClass $reflectionClass, $isDocumented, array $properties)
	{
		foreach ($reflectionClass->getOwnMagicProperties() as $property) {
			if ($this->canBeExtracted($isDocumented, $properties, $property)) {
				$properties[$property->getName()] = $property;
			}
		}
		return $properties;
	}


	/**
	 * @param bool $isDocumented
	 * @param array $properties
	 * @param ReflectionPropertyMagic $property
	 * @return bool
	 */
	private function canBeExtracted($isDocumented, array $properties, ReflectionPropertyMagic $property)
	{
		if (isset($properties[$property->getName()])) {
			return FALSE;
		}
		if ($isDocumented && ! $property->isDocumented()) {
			return FALSE;
		}
		return TRUE;
	}

}
