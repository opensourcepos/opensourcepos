<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\DI\Config;


/**
 * Adapter for reading and writing configuration files.
 */
interface IAdapter
{

	/**
	 * Reads configuration from file.
	 * @param  string  file name
	 * @return array
	 */
	function load($file);

	/**
	 * Generates configuration string.
	 * @return string
	 */
	function dump(array $data);

}
