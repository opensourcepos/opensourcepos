<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * The exception occured during Latte compilation.
 */
class CompileException extends \Exception
{
	/** @var string */
	public $sourceCode;

	/** @var string */
	public $sourceName;

	/** @var int */
	public $sourceLine;


	public function setSource($code, $line, $name = NULL)
	{
		$this->sourceCode = (string) $code;
		$this->sourceLine = (int) $line;
		$this->sourceName = (string) $name;
		if (@is_file($name)) { // @ - may trigger error
			$this->message = rtrim($this->message, '.')
				. ' in ' . str_replace(dirname(dirname($name)), '...', $name) . ($line ? ":$line" : '');
		}
		return $this;
	}

}


/**
 * The exception that indicates error of the last Regexp execution.
 */
class RegexpException extends \Exception
{
	public static $messages = array(
		PREG_INTERNAL_ERROR => 'Internal error',
		PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit was exhausted',
		PREG_RECURSION_LIMIT_ERROR => 'Recursion limit was exhausted',
		PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 data',
		5 => 'Offset didn\'t correspond to the begin of a valid UTF-8 code point', // PREG_BAD_UTF8_OFFSET_ERROR
	);

	public function __construct($message, $code = NULL)
	{
		parent::__construct($message ?: (isset(self::$messages[$code]) ? self::$messages[$code] : 'Unknown error'), $code);
	}

}


/**
 * The exception that indicates error during rendering template.
 */
class RuntimeException extends \Exception
{
}
