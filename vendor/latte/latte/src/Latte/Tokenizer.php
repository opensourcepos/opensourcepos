<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * Simple lexical analyser.
 * @internal
 */
class Tokenizer extends Object
{
	const VALUE = 0,
		OFFSET = 1,
		TYPE = 2;

	/** @var string */
	private $re;

	/** @var array */
	private $types;


	/**
	 * @param  array of [(int) symbol type => pattern]
	 * @param  string  regular expression flag
	 */
	public function __construct(array $patterns, $flags = '')
	{
		$this->re = '~(' . implode(')|(', $patterns) . ')~A' . $flags;
		$this->types = array_keys($patterns);
	}


	/**
	 * Tokenizes string.
	 * @param  string
	 * @return array
	 */
	public function tokenize($input)
	{
		preg_match_all($this->re, $input, $tokens, PREG_SET_ORDER);
		if (preg_last_error()) {
			throw new RegexpException(NULL, preg_last_error());
		}
		$len = 0;
		$count = count($this->types);
		foreach ($tokens as & $match) {
			$type = NULL;
			for ($i = 1; $i <= $count; $i++) {
				if (!isset($match[$i])) {
					break;
				} elseif ($match[$i] != NULL) {
					$type = $this->types[$i - 1];
					break;
				}
			}
			$match = array(self::VALUE => $match[0], self::OFFSET => $len, self::TYPE => $type);
			$len += strlen($match[self::VALUE]);
		}
		if ($len !== strlen($input)) {
			list($line, $col) = $this->getCoordinates($input, $len);
			$token = str_replace("\n", '\n', substr($input, $len, 10));
			throw new CompileException("Unexpected '$token' on line $line, column $col.");
		}
		return $tokens;
	}


	/**
	 * Returns position of token in input string.
	 * @param  string
	 * @param  int
	 * @return array of [line, column]
	 */
	public static function getCoordinates($text, $offset)
	{
		$text = substr($text, 0, $offset);
		return array(substr_count($text, "\n") + 1, $offset - strrpos("\n" . $text, "\n") + 1);
	}

}
