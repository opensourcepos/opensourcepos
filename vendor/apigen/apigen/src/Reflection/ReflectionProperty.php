<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection;

use ApiGen\Reflection\Parts\Visibility;


class ReflectionProperty extends ReflectionElement
{

	use Visibility;

	/**
	 * @return bool
	 */
	public function isReadOnly()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isWriteOnly()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isMagic()
	{
		return FALSE;
	}


	/**
	 * @return string
	 */
	public function getTypeHint()
	{
		if ($annotations = $this->getAnnotation('var')) {
			list($types) = preg_split('~\s+|$~', $annotations[0], 2);
			if ( ! empty($types) && $types[0] !== '$') {
				return $types;
			}
		}

		try {
			$type = gettype($this->getDefaultValue());
			if (strtolower($type) !== 'null') {
				return $type;
			}

		} catch (\Exception $e) {
			return;
		}
	}


	/**
	 * @return ReflectionClass|null
	 */
	public function getDeclaringClass()
	{
		$className = $this->reflection->getDeclaringClassName();
		return $className === NULL ? NULL : $this->getParsedClasses()[$className];
	}


	/**
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->reflection->getDeclaringClassName();
	}


	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->reflection->getDefaultValue();
	}


	/**
	 * @return string
	 */
	public function getDefaultValueDefinition()
	{
		return $this->reflection->getDefaultValueDefinition();
	}


	/**
	 * @return bool
	 */
	public function isDefault()
	{
		return $this->reflection->isDefault();
	}


	/**
	 * @return bool
	 */
	public function isStatic()
	{
		return $this->reflection->isStatic();
	}


	/**
	 * @return ReflectionClass|null
	 */
	public function getDeclaringTrait()
	{
		$traitName = $this->reflection->getDeclaringTraitName();
		return $traitName === NULL ? NULL : $this->getParsedClasses()[$traitName];
	}


	/**
	 * @return string|null
	 */
	public function getDeclaringTraitName()
	{
		return $this->reflection->getDeclaringTraitName();
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
