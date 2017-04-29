<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator\Markups;


interface Markup
{

	/**
	 * @param string $text
	 * @return string
	 */
	function line($text);


	/**
	 * @param string $text
	 * @return string
	 */
	function block($text);

}
