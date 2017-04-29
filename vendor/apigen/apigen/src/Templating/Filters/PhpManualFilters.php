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
use ApiGen\Reflection\ReflectionExtension;
use ApiGen\Reflection\ReflectionMethod;
use ApiGen\Reflection\ReflectionProperty;


/**
 * Builds links to a element documentation at php.net
 */
class PhpManualFilters extends Filters
{

	const PHP_MANUAL_URL = 'http://php.net/manual/en';

	/**
	 * @var array [ className => callback ]
	 */
	private $assignments = [];


	public function __construct()
	{
		$this->prepareAssignments();
	}


	/**
	 * @param ReflectionElement|ReflectionExtension|ReflectionMethod $element
	 * @return string
	 */
	public function manualUrl($element)
	{
		if ($element instanceof ReflectionExtension) {
			return $this->createExtensionUrl($element);
		}

		$class = $this->detectClass($element);
		if ($class && $this->isReservedClass($class)) {
			return self::PHP_MANUAL_URL . '/reserved.classes.php';
		}

		$className = get_class($element);
		if (isset($this->assignments[$className])) {
			return $this->assignments[$className]($element, $class);
		}
		return '';
	}


	/**
	 * @return string
	 */
	private function createExtensionUrl(ReflectionExtension $reflectionExtension)
	{
		$extensionName = strtolower($reflectionExtension->getName());
		if ($extensionName === 'core') {
			return self::PHP_MANUAL_URL;
		}

		if ($extensionName === 'date') {
			$extensionName = 'datetime';
		}

		return self::PHP_MANUAL_URL . '/book.' . $extensionName . '.php';
	}


	/**
	 * @return array
	 */
	private function prepareAssignments()
	{
		$this->assignments['ApiGen\Reflection\ReflectionClass'] = function ($element, $class) {
			return $this->createClassUrl($class);
		};
		$this->assignments['ApiGen\Reflection\ReflectionMethod'] = function ($element, $class) {
			return $this->createMethodUrl($element, $class);
		};
		$this->assignments['ApiGen\Reflection\ReflectionFunction'] = function ($element, $class) {
			return $this->createFunctionUrl($element);
		};
		$this->assignments['ApiGen\Reflection\ReflectionProperty'] = function ($element, $class) {
			return $this->createPropertyUrl($element, $class);
		};
		$this->assignments['ApiGen\Reflection\ReflectionConstant'] = function ($element, $class) {
			return $this->createConstantUrl($element, $class);
		};
	}


	/**
	 * @return string
	 */
	private function createClassUrl(ReflectionClass $classReflection)
	{
		return self::PHP_MANUAL_URL . '/class.' . strtolower($classReflection->getName()) . '.php';
	}


	/**
	 * @return string
	 */
	private function createConstantUrl(ReflectionConstant $reflectionConstant, ReflectionClass $classReflection)
	{
		return $this->createClassUrl($classReflection) . '#' . strtolower($classReflection->getName()) .
			'.constants.' . $this->getElementName($reflectionConstant);
	}


	/**
	 * @return string
	 */
	private function createPropertyUrl(ReflectionProperty $reflectionProperty, ReflectionClass $classReflection)
	{
		return $this->createClassUrl($classReflection) . '#' . strtolower($classReflection->getName()) .
			'.props.' . $this->getElementName($reflectionProperty);
	}


	/**
	 * @return string
	 */
	private function createMethodUrl(ReflectionMethod $reflectionMethod, ReflectionClass $reflectionClass)
	{
		return self::PHP_MANUAL_URL . '/' . strtolower($reflectionClass->getName()) . '.' .
			$this->getElementName($reflectionMethod) . '.php';
	}


	/**
	 * @return string
	 */
	private function createFunctionUrl(ReflectionElement $reflectionElement)
	{
		return self::PHP_MANUAL_URL . '/function.' . strtolower($reflectionElement->getName()) . '.php';
	}


	/**
	 * @return bool
	 */
	private function isReservedClass(ReflectionClass $class)
	{
		$reservedClasses = ['stdClass', 'Closure', 'Directory'];
		return (in_array($class->getName(), $reservedClasses));
	}


	/**
	 * @return string
	 */
	private function getElementName(ReflectionElement $element)
	{
		return strtolower(strtr(ltrim($element->getName(), '_'), '_', '-'));
	}


	/**
	 * @param ReflectionElement|string $element
	 * @return string
	 */
	private function detectClass($element)
	{
		if ($element instanceof ReflectionClass) {
			return $element;
		}

		if ($element instanceof ReflectionMethod || $element instanceof ReflectionProperty
			|| $element instanceof ReflectionConstant
		) {
			return $element->getDeclaringClass();
		}

		return '';
	}

}
