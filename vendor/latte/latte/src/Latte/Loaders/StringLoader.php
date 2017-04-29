<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte\Loaders;

use Latte;


/**
 * Template loader.
 */
class StringLoader extends Latte\Object implements Latte\ILoader
{

	/**
	 * Returns template source code.
	 * @return string
	 */
	public function getContent($content)
	{
		return $content;
	}


	/**
	 * @return bool
	 */
	public function isExpired($content, $time)
	{
		return FALSE;
	}


	/**
	 * Returns fully qualified template name.
	 * @return string
	 */
	public function getChildName($content, $parent = NULL)
	{
		return $content;
	}

}
