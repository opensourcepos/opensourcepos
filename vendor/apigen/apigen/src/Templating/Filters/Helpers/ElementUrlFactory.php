<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters\Helpers;

use ApiGen\Configuration\Configuration;
use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Configuration\Theme\ThemeConfigOptions as TCO;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionFunction;
use ApiGen\Reflection\ReflectionMethod;
use ApiGen\Reflection\ReflectionProperty;
use ApiGen\Templating\Filters\Filters;


class ElementUrlFactory
{

	/**
	 * @var Configuration
	 */
	private $configuration;


	public function __construct(Configuration $configuration)
	{
		$this->configuration = $configuration;
	}


	/**
	 * @param ReflectionElement|string $element
	 * @return string|NULL
	 */
	public function createForElement($element)
	{
		if ($element instanceof ReflectionClass) {
			return $this->createForClass($element);

		} elseif ($element instanceof ReflectionMethod) {
			return $this->createForMethod($element);

		} elseif ($element instanceof ReflectionProperty) {
			return $this->createForProperty($element);

		} elseif ($element instanceof ReflectionConstant) {
			return $this->createForConstant($element);

		} elseif ($element instanceof ReflectionFunction) {
			return $this->createForFunction($element);
		}

		return NULL;
	}


	/**
	 * @param string|ReflectionClass $class
	 * @return string
	 */
	public function createForClass($class)
	{
		$className = $class instanceof ReflectionClass ? $class->getName() : $class;
		return sprintf(
			$this->configuration->getOption(CO::TEMPLATE)['templates']['class']['filename'],
			Filters::urlize($className)
		);
	}


	/**
	 * @return string
	 */
	public function createForMethod(ReflectionMethod $method, ReflectionClass $class = NULL)
	{
		$className = $class !== NULL ? $class->getName() : $method->getDeclaringClassName();
		return $this->createForClass($className) . '#' . ($method->isMagic() ? 'm' : '') . '_'
		. ($method->getOriginalName() ?: $method->getName());
	}


	/**
	 * @return string
	 */
	public function createForProperty(ReflectionProperty $property, ReflectionClass $class = NULL)
	{
		$className = $class !== NULL ? $class->getName() : $property->getDeclaringClassName();
		return $this->createForClass($className) . '#' . ($property->isMagic() ? 'm' : '') . '$' . $property->getName();
	}


	/**
	 * @return string
	 */
	public function createForConstant(ReflectionConstant $constant)
	{
		// Class constant
		if ($className = $constant->getDeclaringClassName()) {
			return $this->createForClass($className) . '#' . $constant->getName();
		}

		// Constant in namespace or global space
		return sprintf(
			$this->configuration->getOption(CO::TEMPLATE)['templates']['constant']['filename'],
			Filters::urlize($constant->getName())
		);
	}


	/**
	 * @return string
	 */
	public function createForFunction(ReflectionFunction $function)
	{
		return sprintf(
			$this->configuration->getOption(CO::TEMPLATE)['templates']['function']['filename'],
			Filters::urlize($function->getName())
		);
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function createForAnnotationGroup($name)
	{
		return sprintf(
			$this->configuration->getOption(CO::TEMPLATE)['templates'][TCO::ANNOTATION_GROUP]['filename'],
			Filters::urlize($name)
		);
	}

}
