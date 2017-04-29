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
use ApiGen\Reflection\TokenReflection\ReflectionFactory;


class AnnotationPropertyExtractor
{

	const PATTERN_PROPERTY = '~^(?:([\\w\\\\]+(?:\\|[\\w\\\\]+)*)\\s+)?\\$(\\w+)(?:\\s+(.*))?($)~s';

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


	public function extractFromReflection(ReflectionClass $reflectionClass)
	{
		$this->reflectionClass = $reflectionClass;

		$properties = [];
		foreach (['property', 'property-read', 'property-write'] as $annotationName) {
			if ($reflectionClass->hasAnnotation($annotationName)) {
				foreach ($reflectionClass->getAnnotation($annotationName) as $annotation) {
					$properties += $this->processMagicPropertyAnnotation($annotation, $annotationName);
				};
			}
		}

		return $properties;
	}


	/**
	 * @param string $annotation
	 * @param string $annotationName
	 * @return ReflectionPropertyMagic[]|array
	 */
	private function processMagicPropertyAnnotation($annotation, $annotationName)
	{
		if ( ! preg_match(self::PATTERN_PROPERTY, $annotation, $matches)) {
			return [];
		}

		list(, $typeHint, $name, $shortDescription) = $matches;

		$startLine = $this->getStartLine($annotation);
		$properties = [];
		$properties[$name] = $this->reflectionFactory->createPropertyMagic([
			'name' => $name,
			'typeHint' => $typeHint,
			'shortDescription' => str_replace("\n", ' ', $shortDescription),
			'startLine' => $startLine,
			'endLine' => $startLine + substr_count($annotation, "\n"),
			'readOnly' => ($annotationName === 'property-read'),
			'writeOnly' => ($annotationName === 'property-write'),
			'declaringClass' => $this->reflectionClass
		]);
		return $properties;
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

}
