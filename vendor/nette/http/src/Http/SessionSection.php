<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Http;

use Nette;


/**
 * Session section.
 */
class SessionSection implements \IteratorAggregate, \ArrayAccess
{
	use Nette\SmartObject;

	/** @var Session */
	private $session;

	/** @var string */
	private $name;

	/** @var array  session data storage */
	private $data;

	/** @var array  session metadata storage */
	private $meta = FALSE;

	/** @var bool */
	public $warnOnUndefined = FALSE;


	/**
	 * Do not call directly. Use Session::getSection().
	 */
	public function __construct(Session $session, $name)
	{
		if (!is_string($name)) {
			throw new Nette\InvalidArgumentException('Session namespace must be a string, ' . gettype($name) . ' given.');
		}

		$this->session = $session;
		$this->name = $name;
	}


	private function start()
	{
		if ($this->meta === FALSE) {
			$this->session->start();
			$this->data = &$_SESSION['__NF']['DATA'][$this->name];
			$this->meta = &$_SESSION['__NF']['META'][$this->name];
		}
	}


	/**
	 * Returns an iterator over all section variables.
	 * @return \Iterator
	 */
	public function getIterator()
	{
		$this->start();
		if (isset($this->data)) {
			return new \ArrayIterator($this->data);
		} else {
			return new \ArrayIterator;
		}
	}


	/**
	 * Sets a variable in this session section.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->start();
		$this->data[$name] = $value;
	}


	/**
	 * Gets a variable from this session section.
	 * @param  string    name
	 * @return mixed
	 */
	public function &__get($name)
	{
		$this->start();
		if ($this->warnOnUndefined && !array_key_exists($name, $this->data)) {
			trigger_error("The variable '$name' does not exist in session section");
		}

		return $this->data[$name];
	}


	/**
	 * Determines whether a variable in this session section is set.
	 * @param  string    name
	 * @return bool
	 */
	public function __isset($name)
	{
		if ($this->session->exists()) {
			$this->start();
		}
		return isset($this->data[$name]);
	}


	/**
	 * Unsets a variable in this session section.
	 * @param  string    name
	 * @return void
	 */
	public function __unset($name)
	{
		$this->start();
		unset($this->data[$name], $this->meta[$name]);
	}


	/**
	 * Sets a variable in this session section.
	 * @param  string  name
	 * @param  mixed   value
	 * @return void
	 */
	public function offsetSet($name, $value)
	{
		$this->__set($name, $value);
	}


	/**
	 * Gets a variable from this session section.
	 * @param  string    name
	 * @return mixed
	 */
	public function offsetGet($name)
	{
		return $this->__get($name);
	}


	/**
	 * Determines whether a variable in this session section is set.
	 * @param  string    name
	 * @return bool
	 */
	public function offsetExists($name)
	{
		return $this->__isset($name);
	}


	/**
	 * Unsets a variable in this session section.
	 * @param  string    name
	 * @return void
	 */
	public function offsetUnset($name)
	{
		$this->__unset($name);
	}


	/**
	 * Sets the expiration of the section or specific variables.
	 * @param  string|int|\DateTimeInterface  time
	 * @param  mixed   optional list of variables / single variable to expire
	 * @return static
	 */
	public function setExpiration($time, $variables = NULL)
	{
		$this->start();
		if ($time) {
			$time = Nette\Utils\DateTime::from($time)->format('U');
			$max = (int) ini_get('session.gc_maxlifetime');
			if ($max !== 0 && ($time - time() > $max + 3)) { // 0 - unlimited in memcache handler, 3 - bulgarian constant
				trigger_error("The expiration time is greater than the session expiration $max seconds");
			}
		}

		foreach (is_array($variables) ? $variables : [$variables] as $variable) {
			$this->meta[$variable]['T'] = $time ?: NULL;
		}
		return $this;
	}


	/**
	 * Removes the expiration from the section or specific variables.
	 * @param  mixed   optional list of variables / single variable to expire
	 * @return void
	 */
	public function removeExpiration($variables = NULL)
	{
		$this->start();
		foreach (is_array($variables) ? $variables : [$variables] as $variable) {
			unset($this->meta[$variable]['T']);
		}
	}


	/**
	 * Cancels the current session section.
	 * @return void
	 */
	public function remove()
	{
		$this->start();
		$this->data = NULL;
		$this->meta = NULL;
	}

}
