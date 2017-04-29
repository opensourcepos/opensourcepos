<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Console;


interface IOInterface
{

	/**
	 * @param string $message
	 */
	function writeln($message);


	/**
	 * @param string $question
	 * @param NULL|string $default
	 * @return string
	 */
	function ask($question, $default = NULL);

}
