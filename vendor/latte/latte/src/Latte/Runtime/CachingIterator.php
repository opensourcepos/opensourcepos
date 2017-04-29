<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte\Runtime;

use Latte;


/**
 * Smarter caching iterator.
 *
 * @property-read bool $first
 * @property-read bool $last
 * @property-read bool $empty
 * @property-read bool $odd
 * @property-read bool $even
 * @property-read int $counter
 * @property-read mixed $nextKey
 * @property-read mixed $nextValue
 * @property-read $innerIterator
 * @property   $flags
 * @property-read $cache
 * @internal
 */
class CachingIterator extends \CachingIterator implements \Countable
{
	/** @var int */
	private $counter = 0;


	public function __construct($iterator)
	{
		if (is_array($iterator) || $iterator instanceof \stdClass) {
			$iterator = new \ArrayIterator($iterator);

		} elseif ($iterator instanceof \IteratorAggregate) {
			do {
				$iterator = $iterator->getIterator();
			} while ($iterator instanceof \IteratorAggregate);

		} elseif ($iterator instanceof \Traversable) {
			if (!$iterator instanceof \Iterator) {
				$iterator = new \IteratorIterator($iterator);
			}
		} else {
			throw new \InvalidArgumentException(sprintf('Invalid argument passed to foreach; array or Traversable expected, %s given.', is_object($iterator) ? get_class($iterator) : gettype($iterator)));
		}

		parent::__construct($iterator, 0);
	}


	/**
	 * Is the current element the first one?
	 * @param  int  grid width
	 * @return bool
	 */
	public function isFirst($width = NULL)
	{
		return $this->counter === 1 || ($width && $this->counter !== 0 && (($this->counter - 1) % $width) === 0);
	}


	/**
	 * Is the current element the last one?
	 * @param  int  grid width
	 * @return bool
	 */
	public function isLast($width = NULL)
	{
		return !$this->hasNext() || ($width && ($this->counter % $width) === 0);
	}


	/**
	 * Is the iterator empty?
	 * @return bool
	 */
	public function isEmpty()
	{
		return $this->counter === 0;
	}


	/**
	 * Is the counter odd?
	 * @return bool
	 */
	public function isOdd()
	{
		return $this->counter % 2 === 1;
	}


	/**
	 * Is the counter even?
	 * @return bool
	 */
	public function isEven()
	{
		return $this->counter % 2 === 0;
	}


	/**
	 * Returns the counter.
	 * @return int
	 */
	public function getCounter()
	{
		return $this->counter;
	}


	/**
	 * Returns the count of elements.
	 * @return int
	 */
	public function count()
	{
		$inner = $this->getInnerIterator();
		if ($inner instanceof \Countable) {
			return $inner->count();

		} else {
			throw new \LogicException('Iterator is not countable.');
		}
	}


	/**
	 * Forwards to the next element.
	 * @return void
	 */
	public function next()
	{
		parent::next();
		if (parent::valid()) {
			$this->counter++;
		}
	}


	/**
	 * Rewinds the Iterator.
	 * @return void
	 */
	public function rewind()
	{
		parent::rewind();
		$this->counter = parent::valid() ? 1 : 0;
	}


	/**
	 * Returns the next key.
	 * @return mixed
	 */
	public function getNextKey()
	{
		return $this->getInnerIterator()->key();
	}


	/**
	 * Returns the next element.
	 * @return mixed
	 */
	public function getNextValue()
	{
		return $this->getInnerIterator()->current();
	}


	/********************* Latte\Object behaviour + property accessor ****************d*g**/


	/**
	 * Call to undefined method.
	 * @throws \LogicException
	 */
	public function __call($name, $args)
	{
		throw new \LogicException(sprintf('Call to undefined method %s::%s().', get_class($this), $name));
	}


	/**
	 * Returns property value.
	 * @throws \LogicException if the property is not defined.
	 */
	public function &__get($name)
	{
		if (method_exists($this, $m = 'get' . $name) || method_exists($this, $m = 'is' . $name)) {
			$ret = $this->$m();
			return $ret;
		}
		throw new \LogicException(sprintf('Cannot read an undeclared property %s::$%s.', get_class($this), $name));
	}


	/**
	 * Access to undeclared property.
	 * @throws \LogicException
	 */
	public function __set($name, $value)
	{
		throw new \LogicException(sprintf('Cannot write to an undeclared property %s::$%s.', get_class($this), $name));
	}


	/**
	 * Is property defined?
	 * @return bool
	 */
	public function __isset($name)
	{
		return method_exists($this, 'get' . $name) || method_exists($this, 'is' . $name);
	}


	/**
	 * Access to undeclared property.
	 * @throws \LogicException
	 */
	public function __unset($name)
	{
		throw new \LogicException(sprintf('Cannot unset the property %s::$%s.', get_class($this), $name));
	}

}
