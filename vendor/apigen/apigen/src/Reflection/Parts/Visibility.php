<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection\Parts;

use TokenReflection\IReflectionMethod;
use TokenReflection\IReflectionProperty;


/**
 * @property-read IReflectionMethod|IReflectionProperty $reflection
 */
trait Visibility
{

	/**
	 * @return bool
	 */
	public function isPrivate()
	{
		return $this->reflection->isPrivate();
	}


	/**
	 * @return bool
	 */
	public function isProtected()
	{
		return $this->reflection->isProtected();
	}


	/**
	 * @return bool
	 */
	public function isPublic()
	{
		return $this->reflection->isPublic();
	}

}
