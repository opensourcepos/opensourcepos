<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection;

use ApiGen\Reflection\Parts\Visibility;


class ReflectionMethod extends ReflectionFunctionBase
{

	use Visibility;

	/**
	 * @return bool
	 */
	public function isMagic()
	{
		return FALSE;
	}


	/**
	 * @return ReflectionClass|NULL
	 */
	public function getDeclaringClass()
	{
		$className = $this->reflection->getDeclaringClassName();
		return $className === NULL ? NULL : $this->getParsedClasses()[$className];
	}


	/**
	 * @return string|NULL
	 */
	public function getDeclaringClassName()
	{
		return $this->reflection->getDeclaringClassName();
	}


	/**
	 * @return bool
	 */
	public function isAbstract()
	{
		return $this->reflection->isAbstract();
	}


	/**
	 * @return bool
	 */
	public function isFinal()
	{
		return $this->reflection->isFinal();
	}


	/**
	 * @return bool
	 */
	public function isStatic()
	{
		return $this->reflection->isStatic();
	}


	/**
	 * @return bool
	 */
	public function isConstructor()
	{
		return $this->reflection->isConstructor();
	}


	/**
	 * @return bool
	 */
	public function isDestructor()
	{
		return $this->reflection->isDestructor();
	}


	/**
	 * @return ReflectionClass|NULL
	 */
	public function getDeclaringTrait()
	{
		$traitName = $this->reflection->getDeclaringTraitName();
		return $traitName === NULL ? NULL : $this->getParsedClasses()[$traitName];
	}


	/**
	 * @return string|NULL
	 */
	public function getDeclaringTraitName()
	{
		return $this->reflection->getDeclaringTraitName();
	}


	/**
	 * @return ReflectionMethod|NULL
	 */
	public function getImplementedMethod()
	{
		foreach ($this->getDeclaringClass()->getOwnInterfaces() as $interface) {
			if ($interface->hasMethod($this->getName())) {
				return $interface->getMethod($this->getName());
			}
		}
		return NULL;
	}


	/**
	 * @return ReflectionMethod|NULL
	 */
	public function getOverriddenMethod()
	{
		$parent = $this->getDeclaringClass()->getParentClass();
		if ($parent === NULL) {
			return NULL;
		}

		foreach ($parent->getMethods() as $method) {
			if ($method->getName() === $this->getName()) {
				if ( ! $method->isPrivate() && ! $method->isAbstract()) {
					return $method;

				} else {
					return NULL;
				}
			}
		}

		return NULL;
	}


	/**
	 * @return ReflectionMethod|NULL
	 */
	public function getOriginal()
	{
		$originalName = $this->reflection->getOriginalName();
		if ($originalName === NULL) {
			return NULL;
		}
		$originalDeclaringClassName = $this->reflection->getOriginal()->getDeclaringClassName();
		return $this->getParsedClasses()[$originalDeclaringClassName]->getMethod($originalName);
	}


	/**
	 * @return string|NULL
	 */
	public function getOriginalName()
	{
		return $this->reflection->getOriginalName();
	}


	/**
	 * @return bool
	 */
	public function isValid()
	{
		if ($class = $this->getDeclaringClass()) {
			return $class->isValid();
		}

		return TRUE;
	}

}
