<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters;

use ApiGen\Generator\Resolvers\ElementResolver;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionElement;


class ResolverFilters extends Filters
{

	/**
	 * @var ElementResolver
	 */
	private $elementResolver;


	public function __construct(ElementResolver $elementResolver)
	{
		$this->elementResolver = $elementResolver;
	}


	/**
	 * @param string $className
	 * @param string|NULL $namespace
	 * @return ReflectionClass|FALSE
	 */
	public function getClass($className, $namespace = NULL)
	{
		$reflection = $this->elementResolver->getClass($className, $namespace);
		if ($reflection) {
			return $reflection;
		}
		return FALSE;
	}


	/**
	 * @param string $definition
	 * @param ReflectionElement $context
	 * @param NULL $expectedName
	 * @return ReflectionElement|bool|NULL
	 */
	public function resolveElement($definition, $context, &$expectedName = NULL)
	{
		$reflection = $this->elementResolver->resolveElement($definition, $context, $expectedName);
		if ($reflection) {
			return $reflection;
		}
		return FALSE;
	}

}
