<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Http;


/**
 * Extended HTTP URL.
 *
 * <pre>
 * http://nette.org/admin/script.php/pathinfo/?name=param#fragment
 *                 \_______________/\________/
 *                        |              |
 *                   scriptPath       pathInfo
 * </pre>
 *
 * - scriptPath:  /admin/script.php (or simply /admin/ when script is directory index)
 * - pathInfo:    /pathinfo/ (additional path information)
 *
 * @property   string $scriptPath
 * @property-read string $pathInfo
 */
class UrlScript extends Url
{
	/** @var string */
	private $scriptPath;


	public function __construct($url = NULL, $scriptPath = '')
	{
		parent::__construct($url);
		$this->setScriptPath($scriptPath);
	}


	/**
	 * Sets the script-path part of URI.
	 * @param  string
	 * @return static
	 */
	public function setScriptPath($value)
	{
		$this->scriptPath = (string) $value;
		return $this;
	}


	/**
	 * Returns the script-path part of URI.
	 * @return string
	 */
	public function getScriptPath()
	{
		return $this->scriptPath ?: $this->path;
	}


	/**
	 * Returns the base-path.
	 * @return string
	 */
	public function getBasePath()
	{
		$pos = strrpos($this->getScriptPath(), '/');
		return $pos === FALSE ? '' : substr($this->getPath(), 0, $pos + 1);
	}


	/**
	 * Returns the additional path information.
	 * @return string
	 */
	public function getPathInfo()
	{
		return (string) substr($this->getPath(), strlen($this->getScriptPath()));
	}

}
