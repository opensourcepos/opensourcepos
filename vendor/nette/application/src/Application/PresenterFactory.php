<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Default presenter loader.
 */
class PresenterFactory implements IPresenterFactory
{
	use Nette\SmartObject;

	/** @var array[] of module => splited mask */
	private $mapping = [
		'*' => ['', '*Module\\', '*Presenter'],
		'Nette' => ['NetteModule\\', '*\\', '*Presenter'],
	];

	/** @var array */
	private $cache = [];

	/** @var callable */
	private $factory;


	/**
	 * @param  callable  function (string $class): IPresenter
	 */
	public function __construct(callable $factory = NULL)
	{
		$this->factory = $factory ?: function ($class) { return new $class; };
	}


	/**
	 * Creates new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		return call_user_func($this->factory, $this->getPresenterClass($name));
	}


	/**
	 * Generates and checks presenter class name.
	 * @param  string  presenter name
	 * @return string  class name
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(&$name)
	{
		if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		if (!is_string($name) || !Nette\Utils\Strings::match($name, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*\z#')) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$class = $this->formatPresenterClass($name);
		if (!class_exists($class)) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found.");
		}

		$reflection = new \ReflectionClass($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface(IPresenter::class)) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		} elseif ($reflection->isAbstract()) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		$this->cache[$name] = $class;

		if ($name !== ($realName = $this->unformatPresenterClass($class))) {
			trigger_error("Case mismatch on presenter name '$name', correct name is '$realName'.", E_USER_WARNING);
			$name = $realName;
		}

		return $class;
	}


	/**
	 * Sets mapping as pairs [module => mask]
	 * @return static
	 */
	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			if (is_string($mask)) {
				if (!preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*\w*)\z#', $mask, $m)) {
					throw new Nette\InvalidStateException("Invalid mapping mask '$mask'.");
				}
				$this->mapping[$module] = [$m[1], $m[2] ?: '*Module\\', $m[3]];
			} elseif (is_array($mask) && count($mask) === 3) {
				$this->mapping[$module] = [$mask[0] ? $mask[0] . '\\' : '', $mask[1] . '\\', $mask[2]];
			} else {
				throw new Nette\InvalidStateException("Invalid mapping mask for module $module.");
			}
		}
		return $this;
	}


	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 * @internal
	 */
	public function formatPresenterClass($presenter)
	{
		$parts = explode(':', $presenter);
		$mapping = isset($parts[1], $this->mapping[$parts[0]])
			? $this->mapping[array_shift($parts)]
			: $this->mapping['*'];

		while ($part = array_shift($parts)) {
			$mapping[0] .= str_replace('*', $part, $mapping[$parts ? 1 : 2]);
		}
		return $mapping[0];
	}


	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string|NULL
	 * @internal
	 */
	public function unformatPresenterClass($class)
	{
		foreach ($this->mapping as $module => $mapping) {
			$mapping = str_replace(['\\', '*'], ['\\\\', '(\w+)'], $mapping);
			if (preg_match("#^\\\\?$mapping[0]((?:$mapping[1])*)$mapping[2]\\z#i", $class, $matches)) {
				return ($module === '*' ? '' : $module . ':')
					. preg_replace("#$mapping[1]#iA", '$1:', $matches[1]) . $matches[3];
			}
		}
		return NULL;
	}

}
