<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Parser\Elements;

use ApiGen\Reflection\ReflectionClass;
use Nette;


class ElementExtractor extends Nette\Object
{

	/**
	 * @var Elements
	 */
	private $elements;

	/**
	 * @var ElementFilter
	 */
	private $elementFilter;

	/**
	 * @var ElementStorage
	 */
	private $elementStorage;

	/**
	 * @var ElementSorter
	 */
	private $elementSorter;


	public function __construct(
		Elements $elements,
		ElementFilter $elementFilter,
		ElementStorage $elementStorage,
		ElementSorter $elementSorter
	) {
		$this->elements = $elements;
		$this->elementFilter = $elementFilter;
		$this->elementStorage = $elementStorage;
		$this->elementSorter = $elementSorter;
	}


	/**
	 * @param string $annotation
	 * @param callable $skipClassCallback
	 * @return array[]
	 */
	public function extractElementsByAnnotation($annotation, callable $skipClassCallback = NULL)
	{
		$elements = $this->elements->getEmptyList();
		$elements[Elements::METHODS] = [];
		$elements[Elements::PROPERTIES] = [];

		foreach ($this->elementStorage->getElements() as $type => $elementList) {
			$elementsForMain = $this->elementFilter->filterForMain($elementList);
			$elements[$type] += $this->elementFilter->filterByAnnotation($elementsForMain, $annotation);

			if ($type === Elements::CONSTANTS || $type === Elements::FUNCTIONS) {
				continue;
			}

			foreach ($elementList as $class) {
				/** @var ReflectionClass $class */
				if ( ! $class->isMain()) {
					continue;
				}

				if ($skipClassCallback && $skipClassCallback($class)) { // in case when class is prior to it's elements
					continue;
				}

				$elements[Elements::METHODS] = $this->extractByAnnotationAndMerge(
					$class->getOwnMethods(), $annotation, $elements[Elements::METHODS]
				);
				$elements[Elements::CONSTANTS] = $this->extractByAnnotationAndMerge(
					$class->getOwnConstants(), $annotation, $elements[Elements::CONSTANTS]
				);
				$elements[Elements::PROPERTIES] = $this->extractByAnnotationAndMerge(
					$class->getOwnProperties(), $annotation, $elements[Elements::PROPERTIES]
				);
			}
		}

		return $this->sortElements($elements);
	}


	/**
	 * @param array $elements
	 * @param string $annotation
	 * @param array[] $storage
	 * @return array[]
	 */
	private function extractByAnnotationAndMerge($elements, $annotation, $storage)
	{
		$foundElements = $this->elementFilter->filterByAnnotation($elements, $annotation);
		return array_merge($storage, array_values($foundElements));
	}


	/**
	 * @param array { key => elementList[] } $elements
	 * @return array
	 */
	private function sortElements($elements)
	{
		foreach ($elements as $key => $elementList) {
			$this->elementSorter->sortElementsByFqn($elementList);
		}
		return $elements;
	}

}
