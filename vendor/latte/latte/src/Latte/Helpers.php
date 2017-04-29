<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * Latte helpers.
 * @internal
 */
class Helpers
{
	/** @var array  empty (void) HTML elements */
	public static $emptyElements = array(
		'img' => 1,'hr' => 1,'br' => 1,'input' => 1,'meta' => 1,'area' => 1,'embed' => 1,'keygen' => 1,'source' => 1,'base' => 1,
		'col' => 1,'link' => 1,'param' => 1,'basefont' => 1,'frame' => 1,'isindex' => 1,'wbr' => 1,'command' => 1,'track' => 1,
	);


	/**
	 * Checks callback.
	 * @return callable
	 */
	public static function checkCallback($callable)
	{
		if (!is_callable($callable, FALSE, $text)) {
			throw new \InvalidArgumentException("Callback '$text' is not callable.");
		}
		return $callable;
	}


	/**
	 * Removes unnecessary blocks of PHP code.
	 * @param  string
	 * @return string
	 */
	public static function optimizePhp($source, $lineLength = 80)
	{
		$res = $php = '';
		$lastChar = ';';
		$tokens = new \ArrayIterator(token_get_all($source));
		foreach ($tokens as $n => $token) {
			if (is_array($token)) {
				if ($token[0] === T_INLINE_HTML) {
					$lastChar = '';
					$res .= $token[1];

				} elseif ($token[0] === T_CLOSE_TAG) {
					$next = isset($tokens[$n + 1]) ? $tokens[$n + 1] : NULL;
					if (substr($res, -1) !== '<' && preg_match('#^<\?php\s*\z#', $php)) {
						$php = ''; // removes empty (?php ?), but retains ((?php ?)?php

					} elseif (is_array($next) && $next[0] === T_OPEN_TAG && (!isset($tokens[$n + 2][1]) || $tokens[$n + 2][1] !== 'xml')) { // remove ?)(?php
						if (!strspn($lastChar, ';{}:/')) {
							$php .= $lastChar = ';';
						}
						if (substr($next[1], -1) === "\n") {
							$php .= "\n";
						}
						$tokens->next();

					} elseif ($next) {
						$res .= preg_replace('#;?(\s)*\z#', '$1', $php) . $token[1]; // remove last semicolon before ?)
						if (strlen($res) - strrpos($res, "\n") > $lineLength
							&& (!is_array($next) || strpos($next[1], "\n") === FALSE)
						) {
							$res .= "\n";
						}
						$php = '';

					} else { // remove last ?)
						if (!strspn($lastChar, '};')) {
							$php .= ';';
						}
					}

				} elseif ($token[0] === T_ELSE || $token[0] === T_ELSEIF) {
					if ($tokens[$n + 1] === ':' && $lastChar === '}') {
						$php .= ';'; // semicolon needed in if(): ... if() ... else:
					}
					$lastChar = '';
					$php .= $token[1];

				} elseif ($token[0] === T_OPEN_TAG && $token[1] === '<?' && isset($tokens[$n + 1][1]) && $tokens[$n + 1][1] === 'xml') {
					$lastChar = '';
					$res .= '<<?php ?>?';
					for ($tokens->next(); $tokens->valid(); $tokens->next()) {
						$token = $tokens->current();
						$res .= is_array($token) ? $token[1] : $token;
						if ($token[0] === T_CLOSE_TAG) {
							break;
						}
					}

				} else {
					if (!in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG), TRUE)) {
						$lastChar = '';
					}
					$php .= $token[1];
				}
			} else {
				$php .= $lastChar = $token;
			}
		}
		return $res . $php;
	}

}
