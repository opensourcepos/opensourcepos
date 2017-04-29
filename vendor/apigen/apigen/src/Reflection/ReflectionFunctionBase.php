<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection;

use InvalidArgumentException;
use TokenReflection\IReflectionParameter;


abstract class ReflectionFunctionBase extends ReflectionElement
{

	/**
	 * @var string Matches "array $arg"
	 */
	const PARAM_ANNOTATION = '~^(?:([\\w\\\\]+(?:\\|[\\w\\\\]+)*)\\s+)?\\$(\\w+)(?:\\s+(.*))?($)~s';

	/**
	 * @var array
	 */
	protected $parameters;


	/**
	 * @return string
	 */
	public function getShortName()
	{
		return $this->reflection->getShortName();
	}


	/**
	 * @return bool
	 */
	public function returnsReference()
	{
		return $this->reflection->returnsReference();
	}


	/**
	 * @return ReflectionParameter[]
	 */
	public function getParameters()
	{
		if ($this->parameters === NULL) {
			$this->parameters = array_map(function (IReflectionParameter $parameter) {
				return $this->reflectionFactory->createFromReflection($parameter);
			}, $this->reflection->getParameters());

			$annotations = (array) $this->getAnnotation('param');
			foreach ($annotations as $position => $annotation) {
				if (isset($this->parameters[$position])) {
					// Standard parameter
					continue;
				}

				$this->processAnnotation($annotation, $position);
			}
		}

		return $this->parameters;
	}


	/**
	 * @param int|string $key
	 * @return ReflectionParameter
	 */
	public function getParameter($key)
	{
		$parameters = $this->getParameters();

		if (isset($parameters[$key])) {
			return $parameters[$key];
		}

		foreach ($parameters as $parameter) {
			if ($parameter->getName() === $key) {
				return $parameter;
			}
		}

		throw new InvalidArgumentException(sprintf(
			'There is no parameter with name/position "%s" in function/method "%s"', $key, $this->getName()
		));
	}


	/**
	 * @return int
	 */
	public function getNumberOfParameters()
	{
		return count($this->getParameters());
	}


	/**
	 * @return int
	 */
	public function getNumberOfRequiredParameters()
	{
		return $this->reflection->getNumberOfRequiredParameters();
	}


	/**
	 * @param string $annotation
	 * @param int $position
	 */
	private function processAnnotation($annotation, $position)
	{
		if ( ! preg_match(self::PARAM_ANNOTATION, $annotation, $matches)) {
			return;
		}

		list(, $typeHint, $name) = $matches;

		$this->parameters[$position] = $this->reflectionFactory->createParameterMagic([
			'name' => $name,
			'position' => $position,
			'typeHint' => $typeHint,
			'defaultValueDefinition' => NULL,
			'unlimited' => TRUE,
			'passedByReference' => FALSE,
			'declaringFunction' => $this
		]);
	}

}
