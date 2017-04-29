<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


/**
 * Finder allows searching through directory trees using iterator.
 *
 * <code>
 * Finder::findFiles('*.php')
 *     ->size('> 10kB')
 *     ->from('.')
 *     ->exclude('temp');
 * </code>
 */
class Finder implements \IteratorAggregate, \Countable
{
	use Nette\SmartObject;

	/** @var array */
	private $paths = [];

	/** @var array of filters */
	private $groups;

	/** @var array filter for recursive traversing */
	private $exclude = [];

	/** @var int */
	private $order = RecursiveIteratorIterator::SELF_FIRST;

	/** @var int */
	private $maxDepth = -1;

	/** @var array */
	private $cursor;


	/**
	 * Begins search for files matching mask and all directories.
	 * @param  mixed
	 * @return self
	 */
	public static function find(...$masks)
	{
		$masks = is_array($masks[0]) ? $masks[0] : $masks;
		return (new static)->select($masks, 'isDir')->select($masks, 'isFile');
	}


	/**
	 * Begins search for files matching mask.
	 * @param  mixed
	 * @return self
	 */
	public static function findFiles(...$masks)
	{
		return (new static)->select(is_array($masks[0]) ? $masks[0] : $masks, 'isFile');
	}


	/**
	 * Begins search for directories matching mask.
	 * @param  mixed
	 * @return self
	 */
	public static function findDirectories(...$masks)
	{
		return (new static)->select(is_array($masks[0]) ? $masks[0] : $masks, 'isDir');
	}


	/**
	 * Creates filtering group by mask & type selector.
	 * @param  array
	 * @param  string
	 * @return self
	 */
	private function select($masks, $type)
	{
		$this->cursor = & $this->groups[];
		$pattern = self::buildPattern($masks);
		if ($type || $pattern) {
			$this->filter(function (RecursiveDirectoryIterator $file) use ($type, $pattern) {
				return !$file->isDot()
					&& (!$type || $file->$type())
					&& (!$pattern || preg_match($pattern, '/' . strtr($file->getSubPathName(), '\\', '/')));
			});
		}
		return $this;
	}


	/**
	 * Searchs in the given folder(s).
	 * @param  string|array
	 * @return self
	 */
	public function in(...$paths)
	{
		$this->maxDepth = 0;
		return $this->from(...$paths);
	}


	/**
	 * Searchs recursively from the given folder(s).
	 * @param  string|array
	 * @return self
	 */
	public function from(...$paths)
	{
		if ($this->paths) {
			throw new Nette\InvalidStateException('Directory to search has already been specified.');
		}
		$this->paths = is_array($paths[0]) ? $paths[0] : $paths;
		$this->cursor = & $this->exclude;
		return $this;
	}


	/**
	 * Shows folder content prior to the folder.
	 * @return self
	 */
	public function childFirst()
	{
		$this->order = RecursiveIteratorIterator::CHILD_FIRST;
		return $this;
	}


	/**
	 * Converts Finder pattern to regular expression.
	 * @param  array
	 * @return string
	 */
	private static function buildPattern($masks)
	{
		$pattern = [];
		foreach ($masks as $mask) {
			$mask = rtrim(strtr($mask, '\\', '/'), '/');
			$prefix = '';
			if ($mask === '') {
				continue;

			} elseif ($mask === '*') {
				return NULL;

			} elseif ($mask[0] === '/') { // absolute fixing
				$mask = ltrim($mask, '/');
				$prefix = '(?<=^/)';
			}
			$pattern[] = $prefix . strtr(preg_quote($mask, '#'),
				['\*\*' => '.*', '\*' => '[^/]*', '\?' => '[^/]', '\[\!' => '[^', '\[' => '[', '\]' => ']', '\-' => '-']);
		}
		return $pattern ? '#/(' . implode('|', $pattern) . ')\z#i' : NULL;
	}


	/********************* iterator generator ****************d*g**/


	/**
	 * Get the number of found files and/or directories.
	 * @return int
	 */
	public function count()
	{
		return iterator_count($this->getIterator());
	}


	/**
	 * Returns iterator.
	 * @return \Iterator
	 */
	public function getIterator()
	{
		if (!$this->paths) {
			throw new Nette\InvalidStateException('Call in() or from() to specify directory to search.');

		} elseif (count($this->paths) === 1) {
			return $this->buildIterator($this->paths[0]);

		} else {
			$iterator = new \AppendIterator();
			$iterator->append($workaround = new \ArrayIterator(['workaround PHP bugs #49104, #63077']));
			foreach ($this->paths as $path) {
				$iterator->append($this->buildIterator($path));
			}
			unset($workaround[0]);
			return $iterator;
		}
	}


	/**
	 * Returns per-path iterator.
	 * @param  string
	 * @return \Iterator
	 */
	private function buildIterator($path)
	{
		$iterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::FOLLOW_SYMLINKS);

		if ($this->exclude) {
			$iterator = new \RecursiveCallbackFilterIterator($iterator, function ($foo, $bar, RecursiveDirectoryIterator $file) {
				if (!$file->isDot() && !$file->isFile()) {
					foreach ($this->exclude as $filter) {
						if (!call_user_func($filter, $file)) {
							return FALSE;
						}
					}
				}
				return TRUE;
			});
		}

		if ($this->maxDepth !== 0) {
			$iterator = new RecursiveIteratorIterator($iterator, $this->order);
			$iterator->setMaxDepth($this->maxDepth);
		}

		$iterator = new \CallbackFilterIterator($iterator, function ($foo, $bar, \Iterator $file) {
			while ($file instanceof \OuterIterator) {
				$file = $file->getInnerIterator();
			}

			foreach ($this->groups as $filters) {
				foreach ($filters as $filter) {
					if (!call_user_func($filter, $file)) {
						continue 2;
					}
				}
				return TRUE;
			}
			return FALSE;
		});

		return $iterator;
	}


	/********************* filtering ****************d*g**/


	/**
	 * Restricts the search using mask.
	 * Excludes directories from recursive traversing.
	 * @param  mixed
	 * @return self
	 */
	public function exclude(...$masks)
	{
		$pattern = self::buildPattern(is_array($masks[0]) ? $masks[0] : $masks);
		if ($pattern) {
			$this->filter(function (RecursiveDirectoryIterator $file) use ($pattern) {
				return !preg_match($pattern, '/' . strtr($file->getSubPathName(), '\\', '/'));
			});
		}
		return $this;
	}


	/**
	 * Restricts the search using callback.
	 * @param  callable  function (RecursiveDirectoryIterator $file)
	 * @return self
	 */
	public function filter($callback)
	{
		$this->cursor[] = $callback;
		return $this;
	}


	/**
	 * Limits recursion level.
	 * @param  int
	 * @return self
	 */
	public function limitDepth($depth)
	{
		$this->maxDepth = $depth;
		return $this;
	}


	/**
	 * Restricts the search by size.
	 * @param  string  "[operator] [size] [unit]" example: >=10kB
	 * @param  int
	 * @return self
	 */
	public function size($operator, $size = NULL)
	{
		if (func_num_args() === 1) { // in $operator is predicate
			if (!preg_match('#^(?:([=<>!]=?|<>)\s*)?((?:\d*\.)?\d+)\s*(K|M|G|)B?\z#i', $operator, $matches)) {
				throw new Nette\InvalidArgumentException('Invalid size predicate format.');
			}
			list(, $operator, $size, $unit) = $matches;
			static $units = ['' => 1, 'k' => 1e3, 'm' => 1e6, 'g' => 1e9];
			$size *= $units[strtolower($unit)];
			$operator = $operator ? $operator : '=';
		}
		return $this->filter(function (RecursiveDirectoryIterator $file) use ($operator, $size) {
			return self::compare($file->getSize(), $operator, $size);
		});
	}


	/**
	 * Restricts the search by modified time.
	 * @param  string  "[operator] [date]" example: >1978-01-23
	 * @param  mixed
	 * @return self
	 */
	public function date($operator, $date = NULL)
	{
		if (func_num_args() === 1) { // in $operator is predicate
			if (!preg_match('#^(?:([=<>!]=?|<>)\s*)?(.+)\z#i', $operator, $matches)) {
				throw new Nette\InvalidArgumentException('Invalid date predicate format.');
			}
			list(, $operator, $date) = $matches;
			$operator = $operator ? $operator : '=';
		}
		$date = DateTime::from($date)->format('U');
		return $this->filter(function (RecursiveDirectoryIterator $file) use ($operator, $date) {
			return self::compare($file->getMTime(), $operator, $date);
		});
	}


	/**
	 * Compares two values.
	 * @param  mixed
	 * @param  mixed
	 * @return bool
	 */
	public static function compare($l, $operator, $r)
	{
		switch ($operator) {
			case '>':
				return $l > $r;
			case '>=':
				return $l >= $r;
			case '<':
				return $l < $r;
			case '<=':
				return $l <= $r;
			case '=':
			case '==':
				return $l == $r;
			case '!':
			case '!=':
			case '<>':
				return $l != $r;
			default:
				throw new Nette\InvalidArgumentException("Unknown operator $operator.");
		}
	}


	/********************* extension methods ****************d*g**/


	public function __call($name, $args)
	{
		if ($callback = Nette\Utils\ObjectMixin::getExtensionMethod(__CLASS__, $name)) {
			return $callback($this, ...$args);
		}
		Nette\Utils\ObjectMixin::strictCall(__CLASS__, $name);
	}


	public static function extensionMethod($name, $callback)
	{
		Nette\Utils\ObjectMixin::setExtensionMethod(__CLASS__, $name, $callback);
	}

}
