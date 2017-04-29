<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection\Parts;

use ApiGen\Reflection\ReflectionClass;


/**
 * @property-read ReflectionClass $declaringClass
 */
trait StartPositionEndPositionMagic
{

	/**
	 * @return int
	 */
	public function getStartPosition()
	{
		return $this->declaringClass->getStartPosition();
	}


	/**
	 * @return int
	 */
	public function getEndPosition()
	{
		return $this->declaringClass->getEndPosition();
	}

}
