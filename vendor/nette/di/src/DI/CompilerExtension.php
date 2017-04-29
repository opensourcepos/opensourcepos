<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\DI;

use Nette;


/**
 * Configurator compiling extension.
 */
abstract class CompilerExtension
{
	use Nette\SmartObject;

	/** @var Compiler */
	protected $compiler;

	/** @var string */
	protected $name;

	/** @var array */
	protected $config = [];


	/**
	 * @return static
	 */
	public function setCompiler(Compiler $compiler, $name)
	{
		$this->compiler = $compiler;
		$this->name = $name;
		return $this;
	}


	/**
	 * @return static
	 */
	public function setConfig(array $config)
	{
		$this->config = $config;
		return $this;
	}


	/**
	 * Returns extension configuration.
	 * @return array
	 */
	public function getConfig()
	{
		if (func_num_args()) { // deprecated
			return Config\Helpers::merge($this->config, $this->getContainerBuilder()->expand(func_get_arg(0)));
		}
		return $this->config;
	}


	/**
	 * Checks whether $config contains only $expected items and returns combined array.
	 * @return array
	 * @throws Nette\InvalidStateException
	 */
	public function validateConfig(array $expected, array $config = NULL, $name = NULL)
	{
		if (func_num_args() === 1) {
			return $this->config = $this->validateConfig($expected, $this->config);
		}
		if ($extra = array_diff_key((array) $config, $expected)) {
			$name = $name ?: $this->name;
			$hint = Nette\Utils\ObjectMixin::getSuggestion(array_keys($expected), key($extra));
			$extra = $hint ? key($extra) : implode(", $name.", array_keys($extra));
			throw new Nette\InvalidStateException("Unknown configuration option $name.$extra" . ($hint ? ", did you mean $name.$hint?" : '.'));
		}
		return Config\Helpers::merge($config, $expected);
	}


	/**
	 * @return ContainerBuilder
	 */
	public function getContainerBuilder()
	{
		return $this->compiler->getContainerBuilder();
	}


	/**
	 * Reads configuration from file.
	 * @param  string  file name
	 * @return array
	 */
	public function loadFromFile($file)
	{
		$loader = new Config\Loader;
		$res = $loader->load($file);
		$this->compiler->addDependencies($loader->getDependencies());
		return $res;
	}


	/**
	 * Prepend extension name to identifier or service name.
	 * @param  string
	 * @return string
	 */
	public function prefix($id)
	{
		return substr_replace($id, $this->name . '.', substr($id, 0, 1) === '@' ? 1 : 0, 0);
	}


	/**
	 * Processes configuration data. Intended to be overridden by descendant.
	 * @return void
	 */
	public function loadConfiguration()
	{
	}


	/**
	 * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
	 * @return void
	 */
	public function beforeCompile()
	{
	}


	/**
	 * Adjusts DI container compiled to PHP class. Intended to be overridden by descendant.
	 * @return void
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
	}

}
