<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Neon;


/**
 * Representation of 'foo(bar=1)' literal
 */
class Entity extends \stdClass
{
	/** @var mixed */
	public $value;

	/** @var array */
	public $attributes;


	public function __construct($value = NULL, array $attrs = NULL)
	{
		$this->value = $value;
		$this->attributes = (array) $attrs;
	}

	public static function __set_state(array $properties)
	{
		return new self($properties['value'], $properties['attributes']);
	}

}
