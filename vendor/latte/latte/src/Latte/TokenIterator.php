<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * Traversing helper.
 * @internal
 */
class TokenIterator extends Object
{
	/** @var array */
	public $tokens;

	/** @var int */
	public $position = -1;

	/** @var array */
	public $ignored = array();


	/**
	 * @param array[]
	 */
	public function __construct(array $tokens)
	{
		$this->tokens = $tokens;
	}


	/**
	 * Returns current token.
	 * @return array|NULL
	 */
	public function currentToken()
	{
		return isset($this->tokens[$this->position])
			? $this->tokens[$this->position]
			: NULL;
	}


	/**
	 * Returns current token value.
	 * @return string|NULL
	 */
	public function currentValue()
	{
		return isset($this->tokens[$this->position])
			? $this->tokens[$this->position][Tokenizer::VALUE]
			: NULL;
	}


	/**
	 * Returns next token.
	 * @param  int|string  (optional) desired token type or value
	 * @return array|NULL
	 */
	public function nextToken()
	{
		return $this->scan(func_get_args(), TRUE, TRUE); // onlyFirst, advance
	}


	/**
	 * Returns next token value.
	 * @param  int|string  (optional) desired token type or value
	 * @return string|NULL
	 */
	public function nextValue()
	{
		return $this->scan(func_get_args(), TRUE, TRUE, TRUE); // onlyFirst, advance, strings
	}


	/**
	 * Returns all next tokens.
	 * @param  int|string  (optional) desired token type or value
	 * @return array[]
	 */
	public function nextAll()
	{
		return $this->scan(func_get_args(), FALSE, TRUE); // advance
	}


	/**
	 * Returns all next tokens until it sees a given token type or value.
	 * @param  int|string  token type or value to stop before
	 * @return array[]
	 */
	public function nextUntil($arg)
	{
		return $this->scan(func_get_args(), FALSE, TRUE, FALSE, TRUE); // advance, until
	}


	/**
	 * Returns concatenation of all next token values.
	 * @param  int|string  (optional) token type or value to be joined
	 * @return string
	 */
	public function joinAll()
	{
		return $this->scan(func_get_args(), FALSE, TRUE, TRUE); // advance, strings
	}


	/**
	 * Returns concatenation of all next tokens until it sees a given token type or value.
	 * @param  int|string  token type or value to stop before
	 * @return string
	 */
	public function joinUntil($arg)
	{
		return $this->scan(func_get_args(), FALSE, TRUE, TRUE, TRUE); // advance, strings, until
	}


	/**
	 * Checks the current token.
	 * @param  int|string  token type or value
	 * @return bool
	 */
	public function isCurrent($arg)
	{
		if (!isset($this->tokens[$this->position])) {
			return FALSE;
		}
		$args = func_get_args();
		$token = $this->tokens[$this->position];
		return in_array($token[Tokenizer::VALUE], $args, TRUE)
			|| (isset($token[Tokenizer::TYPE]) && in_array($token[Tokenizer::TYPE], $args, TRUE));
	}


	/**
	 * Checks the next token existence.
	 * @param  int|string  (optional) token type or value
	 * @return bool
	 */
	public function isNext()
	{
		return (bool) $this->scan(func_get_args(), TRUE, FALSE); // onlyFirst
	}


	/**
	 * Checks the previous token existence.
	 * @param  int|string  (optional) token type or value
	 * @return bool
	 */
	public function isPrev()
	{
		return (bool) $this->scan(func_get_args(), TRUE, FALSE, FALSE, FALSE, TRUE); // onlyFirst, prev
	}


	/**
	 * @return self
	 */
	public function reset()
	{
		$this->position = -1;
		return $this;
	}


	/**
	 * Moves cursor to next token.
	 */
	protected function next()
	{
		$this->position++;
	}


	/**
	 * Looks for (first) (not) wanted tokens.
	 * @param  array of desired token types or values
	 * @param  bool
	 * @param  bool
	 * @param  bool
	 * @param  bool
	 * @param  bool
	 * @return mixed
	 */
	protected function scan($wanted, $onlyFirst, $advance, $strings = FALSE, $until = FALSE, $prev = FALSE)
	{
		$res = $onlyFirst ? NULL : ($strings ? '' : array());
		$pos = $this->position + ($prev ? -1 : 1);
		do {
			if (!isset($this->tokens[$pos])) {
				if (!$wanted && $advance && !$prev && $pos <= count($this->tokens)) {
					$this->next();
				}
				return $res;
			}

			$token = $this->tokens[$pos];
			$type = isset($token[Tokenizer::TYPE]) ? $token[Tokenizer::TYPE] : NULL;
			if (!$wanted || (in_array($token[Tokenizer::VALUE], $wanted, TRUE) || in_array($type, $wanted, TRUE)) ^ $until) {
				while ($advance && !$prev && $pos > $this->position) {
					$this->next();
				}

				if ($onlyFirst) {
					return $strings ? $token[Tokenizer::VALUE] : $token;
				} elseif ($strings) {
					$res .= $token[Tokenizer::VALUE];
				} else {
					$res[] = $token;
				}

			} elseif ($until || !in_array($type, $this->ignored, TRUE)) {
				return $res;
			}
			$pos += $prev ? -1 : 1;
		} while (TRUE);
	}

}
