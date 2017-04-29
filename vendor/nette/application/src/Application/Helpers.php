<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Application helpers.
 */
class Helpers
{
	use Nette\StaticClass;

	/**
	 * Splits name into [module, presenter] or [presenter, action]
	 * @return array
	 */
	public static function splitName($name)
	{
		$pos = strrpos($name, ':');
		return $pos === FALSE
			? ['', $name, '']
			: [substr($name, 0, $pos), (string) substr($name, $pos + 1), ':'];
	}

}
