<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters\Helpers;


class Strings
{

	/**
	 * @param string $value
	 * @return array
	 */
	public static function split($value)
	{
		return preg_split('~\s+|$~', $value, 2);
	}

}
