<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte\Runtime;

use Latte;


/**
 * HTML literal.
 */
class Html extends Latte\Object implements IHtmlString
{
	/** @var string */
	private $value;


	public function __construct($value)
	{
		$this->value = (string) $value;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

}
