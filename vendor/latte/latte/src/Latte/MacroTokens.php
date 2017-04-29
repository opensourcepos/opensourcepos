<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * Macro tag tokenizer.
 */
class MacroTokens extends TokenIterator
{
	const T_WHITESPACE = 1,
		T_COMMENT = 2,
		T_SYMBOL = 3,
		T_NUMBER = 4,
		T_VARIABLE = 5,
		T_STRING = 6,
		T_CAST = 7,
		T_KEYWORD = 8,
		T_CHAR = 9;

	/** @var Tokenizer */
	private static $tokenizer;

	/** @var int */
	public $depth = 0;


	public function __construct($input = NULL)
	{
		parent::__construct(is_array($input) ? $input : $this->parse($input));
		$this->ignored = array(self::T_COMMENT, self::T_WHITESPACE);
	}


	public function parse($s)
	{
		self::$tokenizer = self::$tokenizer ?: new Tokenizer(array(
			self::T_WHITESPACE => '\s+',
			self::T_COMMENT => '(?s)/\*.*?\*/',
			self::T_STRING => Parser::RE_STRING,
			self::T_KEYWORD => '(?:true|false|null|and|or|xor|clone|new|instanceof|return|continue|break|[A-Z_][A-Z0-9_]{2,})(?![\w\pL_])', // keyword or const
			self::T_CAST => '\((?:expand|string|array|int|integer|float|bool|boolean|object)\)', // type casting
			self::T_VARIABLE => '\$[\w\pL_]+',
			self::T_NUMBER => '[+-]?[0-9]+(?:\.[0-9]+)?(?:e[0-9]+)?',
			self::T_SYMBOL => '[\w\pL_]+(?:-[\w\pL_]+)*',
			self::T_CHAR => '::|=>|->|\+\+|--|<<|>>|<=|>=|===|!==|==|!=|<>|&&|\|\||[^"\']', // =>, any char except quotes
		), 'u');
		return self::$tokenizer->tokenize($s);
	}


	/**
	 * Appends simple token or string (will be parsed).
	 * @return self
	 */
	public function append($val, $position = NULL)
	{
		if ($val != NULL) { // intentionally @
			array_splice(
				$this->tokens,
				$position === NULL ? count($this->tokens) : $position, 0,
				is_array($val) ? array($val) : $this->parse($val)
			);
		}
		return $this;
	}


	/**
	 * Prepends simple token or string (will be parsed).
	 * @return self
	 */
	public function prepend($val)
	{
		if ($val != NULL) { // intentionally @
			array_splice($this->tokens, 0, 0, is_array($val) ? array($val) : $this->parse($val));
		}
		return $this;
	}


	/**
	 * Reads single token (optionally delimited by comma) from string.
	 * @param  string
	 * @return string
	 */
	public function fetchWord()
	{
		$words = $this->fetchWords();
		return $words ? implode(':', $words) : FALSE;
	}


	/**
	 * Reads single tokens delimited by colon from string.
	 * @param  string
	 * @return array
	 */
	public function fetchWords()
	{
		do {
			$words[] = $this->joinUntil(self::T_WHITESPACE, ',', ':');
		} while ($this->nextToken(':'));

		if (count($words) === 1 && ($space = $this->nextValue(self::T_WHITESPACE))
			&& (($dot = $this->nextValue('.')) || $this->isPrev('.')))
		{
			$words[0] .= $space . $dot . $this->joinUntil(',');
		}
		$this->nextToken(',');
		$this->nextAll(self::T_WHITESPACE, self::T_COMMENT);
		return $words === array('') ? array() : $words;
	}


	public function reset()
	{
		$this->depth = 0;
		return parent::reset();
	}


	protected function next()
	{
		parent::next();
		if ($this->isCurrent('[', '(', '{')) {
			$this->depth++;
		} elseif ($this->isCurrent(']', ')', '}')) {
			$this->depth--;
		}
	}

}
