<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Caching\Storages;

use Nette;


/**
 * Memory cache storage.
 */
class MemoryStorage implements Nette\Caching\IStorage
{
	use Nette\SmartObject;

	/** @var array */
	private $data = [];


	/**
	 * Read from cache.
	 * @param  string
	 * @return mixed
	 */
	public function read($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : NULL;
	}


	/**
	 * Prevents item reading and writing. Lock is released by write() or remove().
	 * @param  string
	 * @return void
	 */
	public function lock($key)
	{
	}


	/**
	 * Writes item into the cache.
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function write($key, $data, array $dependencies)
	{
		$this->data[$key] = $data;
	}


	/**
	 * Removes item from the cache.
	 * @param  string
	 * @return void
	 */
	public function remove($key)
	{
		unset($this->data[$key]);
	}


	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conditions)
	{
		if (!empty($conditions[Nette\Caching\Cache::ALL])) {
			$this->data = [];
		}
	}

}
