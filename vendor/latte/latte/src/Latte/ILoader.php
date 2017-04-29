<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * Template loader.
 */
interface ILoader
{

	/**
	 * Returns template source code.
	 * @return string
	 */
	function getContent($name);

	/**
	 * Checks whether template is expired.
	 * @return bool
	 */
	function isExpired($name, $time);

	/**
	 * Returns fully qualified template name.
	 * @return string
	 */
	function getChildName($name, $parent = NULL);

}
