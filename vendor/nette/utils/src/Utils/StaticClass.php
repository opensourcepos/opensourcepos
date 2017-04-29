<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette;


/**
 * Static class.
 */
trait StaticClass
{

	/**
	 * @throws \LogicException
	 */
	final public function __construct()
	{
		throw new \LogicException('Class ' . get_class($this) . ' is static and cannot be instantiated.');
	}


	/**
	 * Call to undefined static method.
	 * @throws MemberAccessException
	 */
	public static function __callStatic($name, $args)
	{
		Utils\ObjectMixin::strictStaticCall(get_called_class(), $name);
	}

}
