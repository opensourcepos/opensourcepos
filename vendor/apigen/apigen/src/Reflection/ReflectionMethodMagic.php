<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection;

use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Reflection\Parts\IsDocumentedMagic;
use ApiGen\Reflection\Parts\StartLineEndLine;
use ApiGen\Reflection\Parts\StartPositionEndPositionMagic;


class ReflectionMethodMagic extends ReflectionMethod
{

	use IsDocumentedMagic;
	use StartLineEndLine;
	use StartPositionEndPositionMagic;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $shortDescription;

	/**
	 * @var bool
	 */
	private $returnsReference;

	/**
	 * @var ReflectionClass
	 */
	private $declaringClass;


	public function __construct(array $settings)
	{
		$this->name = $settings['name'];
		$this->shortDescription = $settings['shortDescription'];
		$this->startLine = $settings['startLine'];
		$this->endLine = $settings['endLine'];
		$this->returnsReference = $settings['returnsReference'];
		$this->declaringClass = $settings['declaringClass'];
		$this->annotations = $settings['annotations'];

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
	public function getShortDescription()
	{
		return $this->shortDescription;
	}


	/**
	 * @return string
	 */
	public function getLongDescription()
	{
		return $this->shortDescription;
	}


	/**
	 * @return bool
	 */
	public function returnsReference()
	{
		return $this->returnsReference;
	}


	/**
	 * @return bool
	 */
	public function isMagic()
	{
		return TRUE;
	}


	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName()
	{
		return $this->name;
	}


	/**
	 * @return bool
	 */
	public function isDeprecated()
	{
		return $this->declaringClass->isDeprecated();
	}


	/**
	 * @return string
	 */
	public function getPackageName()
	{
		return $this->declaringClass->getPackageName();
	}


	/**
	 * @return string
	 */
	public function getNamespaceName()
	{
		return $this->declaringClass->getNamespaceName();
	}


	/**
	 * @return array
	 */
	public function getAnnotations()
	{
		if ($this->annotations === NULL) {
			$this->annotations = [];
		}
		return $this->annotations;
	}


	/**
	 * @return ReflectionClass
	 */
	public function getDeclaringClass()
	{
		return $this->declaringClass;
	}


	/**
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClass->getName();
	}


	/**
	 * @return bool
	 */
	public function isAbstract()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isFinal()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isPrivate()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isProtected()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isPublic()
	{
		return TRUE;
	}


	/**
	 * @return bool
	 */
	public function isStatic()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isConstructor()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isDestructor()
	{
		return FALSE;
	}


	/**
	 * @return ReflectionClass|NULL
	 */
	public function getDeclaringTrait()
	{
		return $this->declaringClass->isTrait() ? $this->declaringClass : NULL;
	}


	/**
	 * @return string|NULL
	 */
	public function getDeclaringTraitName()
	{
		if ($declaringTrait = $this->getDeclaringTrait()) {
			return $declaringTrait->getName();
		}
		return NULL;
	}


	/**
	 * @return ReflectionMethod|NULL
	 */
	public function getImplementedMethod()
	{
		return NULL;
	}


	/**
	 * @return ReflectionMethod|NULL
	 */
	public function getOverriddenMethod()
	{
		$parent = $this->declaringClass->getParentClass();
		if ($parent === NULL) {
			return NULL;
		}

		foreach ($parent->getMagicMethods() as $method) {
			if ($method->getName() === $this->name) {
				return $method;
			}
		}

		return NULL;
	}


	/**
	 * @return string
	 */
	public function getOriginalName()
	{
		return $this->getName();
	}


	/**
	 * @return ReflectionMethod|NULL
	 */
	public function getOriginal()
	{
		return NULL;
	}


	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
	}


	/**
	 * @return int
	 */
	public function getNumberOfParameters()
	{
		return count($this->parameters);
	}


	/**
	 * @return int
	 */
	public function getNumberOfRequiredParameters()
	{
		$count = 0;
		array_walk($this->parameters, function (ReflectionParameter $parameter) use (&$count) {
			if ( ! $parameter->isOptional()) {
				$count++;
			}
		});
		return $count;
	}


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return $this->declaringClass->getNamespaceAliases();
	}


	/**
	 * Returns an property pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return sprintf('%s::%s()', $this->declaringClass->getName(), $this->name);
	}


	/**
	 * @return string
	 */
	public function getFileName()
	{
		return $this->declaringClass->getFileName();
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
	public function getDocComment()
	{
		$docComment = "/**\n";

		if ( ! empty($this->shortDescription)) {
			$docComment .= $this->shortDescription . "\n\n";
		}

		if ($annotations = $this->getAnnotation('param')) {
			foreach ($annotations as $annotation) {
				$docComment .= sprintf("@param %s\n", $annotation);
			}
		}

		if ($annotations = $this->getAnnotation('return')) {
			foreach ($annotations as $annotation) {
				$docComment .= sprintf("@return %s\n", $annotation);
			}
		}

		$docComment .= "*/\n";

		return $docComment;
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		$annotations = $this->getAnnotations();
		return array_key_exists($name, $annotations);
	}


	/**
	 * @param string $name
	 * @return string|array|NULL
	 */
	public function getAnnotation($name)
	{
		$annotations = $this->getAnnotations();
		if (array_key_exists($name, $annotations)) {
			return $annotations[$name];
		}
		return NULL;
	}

}
