<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Configuration\Readers;


class ReaderFactory
{

	/**
	 * @var string
	 */
	const NEON = 'neon';


	/**
	 * @param string $path
	 * @return ReaderInterface
	 */
	public static function getReader($path)
	{
		$fileExtension = pathinfo($path, PATHINFO_EXTENSION);
		return ($fileExtension === self::NEON) ? new NeonFile($path) : new YamlFile($path);
	}

}
