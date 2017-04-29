<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Parser\Elements;

use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionFunction;
use ApiGen\Reflection\ReflectionMethod;
use ApiGen\Reflection\ReflectionProperty;


class ElementSorter
{

	/**
	 * @param ReflectionConstant[]|ReflectionFunction[]|ReflectionMethod[]|ReflectionProperty[] $elements
	 * @return ReflectionConstant[]|ReflectionFunction[]|ReflectionMethod[]|ReflectionProperty[]
	 */
	public function sortElementsByFqn(array $elements)
	{
		if (count($elements)) {
			$firstElement = array_values($elements)[0];
			if ($firstElement instanceof ReflectionConstant) {
				return $this->sortConstantsByFqn($elements);

			} elseif ($firstElement instanceof ReflectionFunction) {
				return $this->sortFunctionsByFqn($elements);

			} elseif ($firstElement instanceof ReflectionMethod || $firstElement instanceof ReflectionProperty) {
				return $this->sortPropertiesOrMethodsByFqn($elements);
			}
		}
		return $elements;
	}


	/**
	 * @param ReflectionConstant[] $reflectionConstants
	 * @return ReflectionConstant[]
	 */
	private function sortConstantsByFqn($reflectionConstants)
	{
		usort($reflectionConstants, function ($a, $b) {
			return $this->compareConstantsByFqn($a, $b);
		});
		return $reflectionConstants;
	}


	/**
	 * @param ReflectionFunction[] $reflectionFunctions
	 * @return ReflectionFunction[]
	 */
	private function sortFunctionsByFqn($reflectionFunctions)
	{
		usort($reflectionFunctions, function ($a, $b) {
			return $this->compareFunctionsByFqn($a, $b);
		});
		return $reflectionFunctions;
	}


	/**
	 * @param ReflectionMethod[]|ReflectionProperty[] $reflectionElements
	 * @return ReflectionMethod[]
	 */
	private function sortPropertiesOrMethodsByFqn($reflectionElements)
	{
		usort($reflectionElements, function ($a, $b) {
			return $this->compareMethodsOrPropertiesByFqn($a, $b);
		});
		return $reflectionElements;
	}


	/**
	 * @return int
	 */
	private function compareConstantsByFqn(ReflectionConstant $reflection1, ReflectionConstant $reflection2)
	{
		return strcasecmp($this->getConstantFqnName($reflection1), $this->getConstantFqnName($reflection2));
	}


	/**
	 * @return int
	 */
	private function compareFunctionsByFqn(ReflectionFunction $reflection1, ReflectionFunction $reflection2)
	{
		return strcasecmp($this->getFunctionFqnName($reflection1), $this->getFunctionFqnName($reflection2));
	}


	/**
	 * @param ReflectionMethod|ReflectionProperty $reflection1
	 * @param ReflectionMethod|ReflectionProperty $reflection2
	 * @return int
	 */
	private function compareMethodsOrPropertiesByFqn($reflection1, $reflection2)
	{
		return strcasecmp(
			$this->getPropertyOrMethodFqnName($reflection1),
			$this->getPropertyOrMethodFqnName($reflection2)
		);
	}


	/**
	 * @return string
	 */
	private function getConstantFqnName(ReflectionConstant $reflection)
	{
		$class = $reflection->getDeclaringClassName() ?: $reflection->getNamespaceName();
		return $class . '\\' . $reflection->getName();
	}


	/**
	 * @return string
	 */
	private function getFunctionFqnName(ReflectionFunction $reflection)
	{
		return $reflection->getNamespaceName() . '\\' . $reflection->getName();
	}


	/**
	 * @param ReflectionMethod|ReflectionProperty $reflection
	 * @return string
	 */
	private function getPropertyOrMethodFqnName(ReflectionElement $reflection)
	{
		return $reflection->getDeclaringClassName() . '::' . $reflection->getName();
	}

}
