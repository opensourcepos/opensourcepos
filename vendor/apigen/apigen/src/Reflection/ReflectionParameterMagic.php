<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection;

use TokenReflection;


class ReflectionParameterMagic extends ReflectionParameter
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $typeHint;

	/**
	 * @var int
	 */
	private $position;

	/**
	 * @var bool
	 */
	private $defaultValueDefinition;

	/**
	 * @var bool
	 */
	private $unlimited;

	/**
	 * @var bool
	 */
	private $passedByReference;

	/**
	 * @var ReflectionMethodMagic
	 */
	private $declaringFunction;


	public function __construct(array $settings)
	{
		$this->name = $settings['name'];
		$this->position = $settings['position'];
		$this->typeHint = $settings['typeHint'];
		$this->defaultValueDefinition = $settings['defaultValueDefinition'];
		$this->unlimited = $settings['unlimited'];
		$this->passedByReference = $settings['passedByReference'];
		$this->declaringFunction = $settings['declaringFunction'];

		$this->reflectionType = get_class($this);
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return string
	 */
	public function getTypeHint()
	{
		return $this->typeHint;
	}


	/**
	 * @return string
	 */
	public function getFileName()
	{
		return $this->declaringFunction->getFileName();
	}


	/**
	 * @return bool
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * @return string
	 */
	public function getPrettyName()
	{
		return str_replace('()', '($' . $this->name . ')', $this->declaringFunction->getPrettyName());
	}


	/**
	 * @return ReflectionClass
	 */
	public function getDeclaringClass()
	{
		return $this->declaringFunction->getDeclaringClass();
	}


	/**
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringFunction->getDeclaringClassName();
	}


	/**
	 * @return ReflectionMethod
	 */
	public function getDeclaringFunction()
	{
		return $this->declaringFunction;
	}


	/**
	 * @return string
	 */
	public function getDeclaringFunctionName()
	{
		return $this->declaringFunction->getName();
	}


	/**
	 * @return int
	 */
	public function getStartLine()
	{
		return $this->declaringFunction->getStartLine();
	}


	/**
	 * @return int
	 */
	public function getEndLine()
	{
		return $this->declaringFunction->getEndLine();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDocComment()
	{
		return '';
	}


	/**
	 * @return string
	 */
	public function getDefaultValueDefinition()
	{
		return $this->defaultValueDefinition;
	}


	/**
	 * @return bool
	 */
	public function isDefaultValueAvailable()
	{
		return (bool) $this->defaultValueDefinition;
	}


	/**
	 * @return int
	 */
	public function getPosition()
	{
		return $this->position;
	}


	/**
	 * @return bool
	 */
	public function isArray()
	{
		return TokenReflection\ReflectionParameter::ARRAY_TYPE_HINT === $this->typeHint;
	}


	/**
	 * @return bool
	 */
	public function isCallable()
	{
		return TokenReflection\ReflectionParameter::CALLABLE_TYPE_HINT === $this->typeHint;
	}


	/**
	 * @return ReflectionClass|NULL
	 */
	public function getClass()
	{
		$className = $this->getClassName();
		return $className === NULL ? NULL : $this->getParsedClasses()[$className];
	}


	/**
	 * @return string|NULL
	 */
	public function getClassName()
	{
		if ($this->isArray() || $this->isCallable()) {
			return NULL;
		}
		if (isset($this->getParsedClasses()[$this->typeHint])) {
			return $this->typeHint;
		}

		return NULL;
	}


	/**
	 * @return bool
	 */
	public function allowsNull()
	{
		if ($this->isArray() || $this->isCallable()) {
			return strtolower($this->defaultValueDefinition) === 'null';
		}

		return ! empty($this->defaultValueDefinition);
	}


	/**
	 * @return bool
	 */
	public function isOptional()
	{
		return $this->isDefaultValueAvailable();
	}


	/**
	 * @return bool
	 */
	public function isPassedByReference()
	{
		return $this->passedByReference;
	}


	/**
	 * @return bool
	 */
	public function canBePassedByValue()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isUnlimited()
	{
		return $this->unlimited;
	}

}
