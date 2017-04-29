<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte\Macros;

use Latte;
use Latte\RuntimeException;


/**
 * Runtime helpers for block macros.
 */
class BlockMacrosRuntime extends Latte\Object
{

	/**
	 * Calls block.
	 * @return void
	 */
	public static function callBlock(\stdClass $context, $name, array $params)
	{
		if (empty($context->blocks[$name])) {
			throw new RuntimeException("Cannot include undefined block '$name'.");
		}
		$block = reset($context->blocks[$name]);
		$block($context, $params);
	}


	/**
	 * Calls parent block.
	 * @return void
	 */
	public static function callBlockParent(\stdClass $context, $name, array $params)
	{
		if (empty($context->blocks[$name]) || ($block = next($context->blocks[$name])) === FALSE) {
			throw new RuntimeException("Cannot include undefined parent block '$name'.");
		}
		$block($context, $params);
		prev($context->blocks[$name]);
	}

}
