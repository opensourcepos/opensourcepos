<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Parser\Elements;

use ApiGen\Reflection\ReflectionBase;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionFunction;


class AutocompleteElements
{

	/**
	 * @var ElementStorage
	 */
	private $elementStorage;

	/**
	 * @var array
	 */
	private $elements = [];


	public function __construct(ElementStorage $elementStorage)
	{
		$this->elementStorage = $elementStorage;
	}


	/**
	 * @return array
	 */
	public function getElements()
	{
		foreach ($this->elementStorage->getElements() as $type => $elementList) {
			foreach ($elementList as $element) {
				$this->processElement($element);
			}
		}

		$this->sortElements();

		return $this->elements;
	}


	private function processElement(ReflectionBase $element)
	{
		if ($element instanceof ReflectionConstant) {
			$this->elements[] = ['co', $element->getPrettyName()];

		} elseif ($element instanceof ReflectionFunction) {
			$this->elements[] = ['f', $element->getPrettyName()];

		} elseif ($element instanceof ReflectionClass) {
			$this->elements[] = ['c', $element->getPrettyName()];
		}
	}


	private function sortElements()
	{
		usort($this->elements, function ($one, $two) {
			return strcasecmp($one[1], $two[1]);
		});
	}

}
