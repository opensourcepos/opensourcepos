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
use ApiGen\Reflection\TokenReflection\ReflectionFactory;


class AnnotationMethodExtractor
{

	const PATTERN_METHOD = '~^(?:([\\w\\\\]+(?:\\|[\\w\\\\]+)*)\\s+)?(&)?\\s*(\\w+)\\s*\\(\\s*(.*)\\s*\\)\\s*(.*|$)~s';
	const PATTERN_PARAMETER = '~^(?:([\\w\\\\]+(?:\\|[\\w\\\\]+)*)\\s+)?(&)?\\s*\\$(\\w+)(?:\\s*=\\s*(.*))?($)~s';

	/**
	 * @var ReflectionFactory
	 */
	private $reflectionFactory;

	/**
	 * @var ReflectionClass
	 */
	private $reflectionClass;


	public function __construct(ReflectionFactory $reflectionFactory)
	{
		$this->reflectionFactory = $reflectionFactory;
	}


	/**
	 * @param ReflectionClass $reflectionClass
	 * @return ReflectionMethodMagic[]|array
	 */
	public function extractFromReflection(ReflectionClass $reflectionClass)
	{
		$this->reflectionClass = $reflectionClass;

		$methods = [];
		if ($reflectionClass->hasAnnotation('method')) {
			foreach ($reflectionClass->getAnnotation('method') as $annotation) {
				$methods += $this->processMagicMethodAnnotation($annotation);
			};
		}

		return $methods;
	}


	/**
	 * @param string $annotation
	 * @return ReflectionMethodMagic[]|array
	 */
	private function processMagicMethodAnnotation($annotation)
	{
		if ( ! preg_match(self::PATTERN_METHOD, $annotation, $matches)) {
			return [];
		}

		list(, $returnTypeHint, $returnsReference, $name, $args, $shortDescription) = $matches;

		$startLine = $this->getStartLine($annotation);
		$endLine = $startLine + substr_count($annotation, "\n");

		$methods = [];
		$methods[$name] = $method = $this->reflectionFactory->createMethodMagic([
			'name' => $name,
			'shortDescription' => str_replace("\n", ' ', $shortDescription),
			'startLine' => $startLine,
			'endLine' => $endLine,
			'returnsReference' => ($returnsReference === '&'),
			'declaringClass' => $this->reflectionClass,
			'annotations' => ['return' => [0 => $returnTypeHint]]
		]);
		$this->attachMethodParameters($method, $args);
		return $methods;
	}


	/**
	 * @param string $annotation
	 * @return int
	 */
	private function getStartLine($annotation)
	{
		$doc = $this->reflectionClass->getDocComment();
		$tmp = $annotation;
		if ($delimiter = strpos($annotation, "\n")) {
			$tmp = substr($annotation, 0, $delimiter);
		}
		return $this->reflectionClass->getStartLine() + substr_count(substr($doc, 0, strpos($doc, $tmp)), "\n");
	}


	/**
	 * @param ReflectionMethodMagic $method
	 * @param string $args
	 */
	private function attachMethodParameters(ReflectionMethodMagic $method, $args)
	{
		$parameters = [];
		foreach (array_filter(preg_split('~\\s*,\\s*~', $args)) as $position => $arg) {
			if ( ! preg_match(self::PATTERN_PARAMETER, $arg, $matches)) {
				// Wrong annotation format
				continue;
			}

			list(, $typeHint, $passedByReference, $name, $defaultValueDefinition) = $matches;

			$parameters[$name] = $this->reflectionFactory->createParameterMagic([
				'name' => $name,
				'position' => $position,
				'typeHint' => $typeHint,
				'defaultValueDefinition' => $defaultValueDefinition,
				'unlimited' => FALSE,
				'passedByReference' => ($passedByReference === '&'),
				'declaringFunction' => $method
			]);
			$method->addAnnotation('param', ltrim(sprintf('%s $%s', $typeHint, $name)));
		}
		$method->setParameters($parameters);
	}

}
