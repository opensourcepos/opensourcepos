<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters\Helpers;

use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionFunction;
use ApiGen\Reflection\ReflectionMethod;
use ApiGen\Reflection\ReflectionProperty;
use Nette\Utils\Html;
use UnexpectedValueException;


class ElementLinkFactory
{

	/**
	 * @var ElementUrlFactory
	 */
	private $elementUrlFactory;

	/**
	 * @var LinkBuilder
	 */
	private $linkBuilder;


	public function __construct(ElementUrlFactory $elementUrlFactory, LinkBuilder $linkBuilder)
	{
		$this->elementUrlFactory = $elementUrlFactory;
		$this->linkBuilder = $linkBuilder;
	}


	/**
	 * @return string
	 */
	public function createForElement(ReflectionElement $element, array $classes = [])
	{
		if ($element instanceof ReflectionClass) {
			return $this->createForClass($element, $classes);

		} elseif ($element instanceof ReflectionMethod) {
			return $this->createForMethod($element, $classes);

		} elseif ($element instanceof ReflectionProperty) {
			return $this->createForProperty($element, $classes);

		} elseif ($element instanceof ReflectionConstant) {
			return $this->createForConstant($element, $classes);

		} elseif ($element instanceof ReflectionFunction) {
			return $this->createForFunction($element, $classes);
		}

		throw new UnexpectedValueException(
			'Descendant of ApiGen\Reflection\Reflection class expected. Got "'
			. get_class($element) . ' class".'
		);
	}


	/**
	 * @return string
	 */
	private function createForClass(ReflectionClass $reflectionClass, array $classes)
	{
		return $this->linkBuilder->build(
			$this->elementUrlFactory->createForClass($reflectionClass),
			$reflectionClass->getName(),
			TRUE,
			$classes
		);
	}


	/**
	 * @return string
	 */
	private function createForMethod(ReflectionMethod $reflectionMethod, array $classes)
	{
		return $this->linkBuilder->build(
			$this->elementUrlFactory->createForMethod($reflectionMethod),
			$reflectionMethod->getDeclaringClassName() . '::' . $reflectionMethod->getName() . '()',
			FALSE,
			$classes
		);
	}


	/**
	 * @return string
	 */
	private function createForProperty(ReflectionProperty $reflectionProperty, array $classes)
	{
		$text = $reflectionProperty->getDeclaringClassName() . '::' .
			Html::el('var')->setText('$' . $reflectionProperty->getName());

		return $this->linkBuilder->build(
			$this->elementUrlFactory->createForProperty($reflectionProperty),
			$text,
			FALSE,
			$classes
		);
	}


	/**
	 * @return string
	 */
	private function createForConstant(ReflectionConstant $reflectionConstant, array $classes)
	{
		$url = $this->elementUrlFactory->createForConstant($reflectionConstant);

		if ($reflectionConstant->getDeclaringClassName()) {
			$text = $reflectionConstant->getDeclaringClassName() . '::' .
				Html::el('b')->setText($reflectionConstant->getName());

		} else {
			$text = $this->getGlobalConstantName($reflectionConstant);
		}

		return $this->linkBuilder->build($url, $text, FALSE, $classes);
	}


	/**
	 * @return string
	 */
	private function createForFunction(ReflectionFunction $reflectionFunction, array $classes)
	{
		return $this->linkBuilder->build(
			$this->elementUrlFactory->createForFunction($reflectionFunction),
			$reflectionFunction->getName() . '()',
			TRUE,
			$classes
		);
	}


	/**
	 * @return string
	 */
	private function getGlobalConstantName(ReflectionConstant $reflectionConstant)
	{
		if ($reflectionConstant->inNamespace()) {
			return $reflectionConstant->getNamespaceName() . '\\' .
				Html::el('b')->setText($reflectionConstant->getShortName());

		} else {
			return Html::el('b')->setText($reflectionConstant->getName());
		}
	}

}
