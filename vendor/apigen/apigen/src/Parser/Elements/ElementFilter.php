<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Parser\Elements;

use ApiGen\Reflection\ReflectionElement;


class ElementFilter
{

	/**
	 * @param ReflectionElement[] $elements
	 * @return ReflectionElement[]
	 */
	public function filterForMain(array $elements)
	{
		return array_filter($elements, function (ReflectionElement $element) {
			return $element->isMain();
		});
	}


	/**
	 * @param ReflectionElement[] $elements
	 * @param string $annotation
	 * @return ReflectionElement[]
	 */
	public function filterByAnnotation(array $elements, $annotation)
	{
		return array_filter($elements, function (ReflectionElement $element) use ($annotation) {
			return $element->hasAnnotation($annotation);
		});
	}

}
