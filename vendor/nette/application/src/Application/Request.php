<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Presenter request.
 *
 * @property string $presenterName
 * @property array $parameters
 * @property array $post
 * @property array $files
 * @property string|NULL $method
 */
class Request
{
	use Nette\SmartObject;

	/** method */
	const FORWARD = 'FORWARD';

	/** flag */
	const SECURED = 'secured';

	/** flag */
	const RESTORED = 'restored';

	/** @var string|NULL */
	private $method;

	/** @var array */
	private $flags = [];

	/** @var string */
	private $name;

	/** @var array */
	private $params;

	/** @var array */
	private $post;

	/** @var array */
	private $files;


	/**
	 * @param  string  fully qualified presenter name (module:module:presenter)
	 * @param  string  method
	 * @param  array   variables provided to the presenter usually via URL
	 * @param  array   variables provided to the presenter via POST
	 * @param  array   all uploaded files
	 * @param  array   flags
	 */
	public function __construct($name, $method = NULL, array $params = [], array $post = [], array $files = [], array $flags = [])
	{
		$this->name = $name;
		$this->method = $method;
		$this->params = $params;
		$this->post = $post;
		$this->files = $files;
		$this->flags = $flags;
	}


	/**
	 * Sets the presenter name.
	 * @param  string
	 * @return static
	 */
	public function setPresenterName($name)
	{
		$this->name = $name;
		return $this;
	}


	/**
	 * Retrieve the presenter name.
	 * @return string
	 */
	public function getPresenterName()
	{
		return $this->name;
	}


	/**
	 * Sets variables provided to the presenter.
	 * @return static
	 */
	public function setParameters(array $params)
	{
		$this->params = $params;
		return $this;
	}


	/**
	 * Returns all variables provided to the presenter (usually via URL).
	 * @return array
	 */
	public function getParameters()
	{
		return $this->params;
	}


	/**
	 * Returns a parameter provided to the presenter.
	 * @param  string
	 * @return mixed
	 */
	public function getParameter($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : NULL;
	}


	/**
	 * Sets variables provided to the presenter via POST.
	 * @return static
	 */
	public function setPost(array $params)
	{
		$this->post = $params;
		return $this;
	}


	/**
	 * Returns a variable provided to the presenter via POST.
	 * If no key is passed, returns the entire array.
	 * @param  string
	 * @return mixed
	 */
	public function getPost($key = NULL)
	{
		if (func_num_args() === 0) {
			return $this->post;

		} elseif (isset($this->post[$key])) {
			return $this->post[$key];

		} else {
			return NULL;
		}
	}


	/**
	 * Sets all uploaded files.
	 * @return static
	 */
	public function setFiles(array $files)
	{
		$this->files = $files;
		return $this;
	}


	/**
	 * Returns all uploaded files.
	 * @return array
	 */
	public function getFiles()
	{
		return $this->files;
	}


	/**
	 * Sets the method.
	 * @param  string|NULL
	 * @return static
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}


	/**
	 * Returns the method.
	 * @return string|NULL
	 */
	public function getMethod()
	{
		return $this->method;
	}


	/**
	 * Checks if the method is the given one.
	 * @param  string
	 * @return bool
	 */
	public function isMethod($method)
	{
		return strcasecmp($this->method, $method) === 0;
	}


	/**
	 * Sets the flag.
	 * @param  string
	 * @param  bool
	 * @return static
	 */
	public function setFlag($flag, $value = TRUE)
	{
		$this->flags[$flag] = (bool) $value;
		return $this;
	}


	/**
	 * Checks the flag.
	 * @param  string
	 * @return bool
	 */
	public function hasFlag($flag)
	{
		return !empty($this->flags[$flag]);
	}

}
