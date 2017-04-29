<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * Provides objects to work as array.
 */
class ArrayHash extends \stdClass implements \ArrayAccess, \Countable, \IteratorAggregate
{

	/**
	 * @param  array to wrap
	 * @param  bool
	 * @return static
	 */
	public static function from($arr, $recursive = TRUE)
	{
		$obj = new static;
		foreach ($arr as $key => $value) {
			if ($recursive && is_array($value)) {
				$obj->$key = static::from($value, TRUE);
			} else {
				$obj->$key = $value;
			}
		}
		return $obj;
	}


	/**
	 * Returns an iterator over all items.
	 * @return \RecursiveArrayIterator
	 */
	public function getIterator()
	{
		return new \RecursiveArrayIterator((array) $this);
	}


	/**
	 * Returns items count.
	 * @return int
	 */
	public function count()
	{
		return count((array) $this);
	}


	/**
	 * Replaces or appends a item.
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		if (!is_scalar($key)) { // prevents NULL
			throw new Nette\InvalidArgumentException(sprintf('Key must be either a string or an integer, %s given.', gettype($key)));
		}
		$this->$key = $value;
	}


	/**
	 * Returns a item.
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->$key;
	}


	/**
	 * Determines whether a item exists.
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return isset($this->$key);
	}


	/**
	 * Removes the element from this list.
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->$key);
	}

}
