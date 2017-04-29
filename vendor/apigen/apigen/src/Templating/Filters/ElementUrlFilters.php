<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters;

use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionFunction;
use ApiGen\Reflection\ReflectionMethod;
use ApiGen\Reflection\ReflectionProperty;
use ApiGen\Templating\Filters\Helpers\ElementUrlFactory;


class ElementUrlFilters extends Filters
{

	/**
	 * @var ElementUrlFactory
	 */
	private $elementUrlFactory;


	public function __construct(ElementUrlFactory $elementUrlFactory)
	{
		$this->elementUrlFactory = $elementUrlFactory;
	}


	/**
	 * @return string
	 */
	public function elementUrl(ReflectionElement $element)
	{
		return $this->elementUrlFactory->createForElement($element);
	}


	/**
	 * @param string|ReflectionClass $class
	 * @return string
	 */
	public function classUrl($class)
	{
		return $this->elementUrlFactory->createForClass($class);
	}


	/**
	 * @return string
	 */
	public function methodUrl(ReflectionMethod $method, ReflectionClass $class = NULL)
	{
		return $this->elementUrlFactory->createForMethod($method, $class);
	}


	/**
	 * @return string
	 */
	public function propertyUrl(ReflectionProperty $property, ReflectionClass $class = NULL)
	{
		return $this->elementUrlFactory->createForProperty($property, $class);
	}


	/**
	 * @return string
	 */
	public function constantUrl(ReflectionConstant $constant)
	{
		return $this->elementUrlFactory->createForConstant($constant);
	}


	/**
	 * @return string
	 */
	public function functionUrl(ReflectionFunction $function)
	{
		return $this->elementUrlFactory->createForFunction($function);
	}

}
