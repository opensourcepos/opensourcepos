<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Neon;


/**
 * Parser for Nette Object Notation.
 * @internal
 */
class Decoder
{
	/** @deprecated */
	public static $patterns = self::PATTERNS;

	const PATTERNS = [
		'
			\'\'\'\n (?:(?: [^\n] | \n(?![\t\ ]*+\'\'\') )*+ \n)?[\t\ ]*+\'\'\' |
			"""\n (?:(?: [^\n] | \n(?![\t\ ]*+""") )*+ \n)?[\t\ ]*+""" |
			\'[^\'\n]*+\' |
			" (?: \\\\. | [^"\\\\\n] )*+ "
		', // string
		'
			(?: [^#"\',:=[\]{}()\x00-\x20!`-] | [:-][^"\',\]})\s] )
			(?:
				[^,:=\]})(\x00-\x20]++ |
				:(?! [\s,\]})] | $ ) |
				[\ \t]++ [^#,:=\]})(\x00-\x20]
			)*+
		', // literal / boolean / integer / float
		'
			[,:=[\]{}()-]
		', // symbol
		'?:\#.*+', // comment
		'\n[\t\ ]*+', // new line + indent
		'?:[\t\ ]++', // whitespace
	];

	const PATTERN_DATETIME = '#\d\d\d\d-\d\d?-\d\d?(?:(?:[Tt]| ++)\d\d?:\d\d:\d\d(?:\.\d*+)? *+(?:Z|[-+]\d\d?(?::?\d\d)?)?)?\z#A';

	const PATTERN_HEX = '#0x[0-9a-fA-F]++\z#A';

	const PATTERN_OCTAL = '#0o[0-7]++\z#A';

	const PATTERN_BINARY = '#0b[0-1]++\z#A';

	const SIMPLE_TYPES = [
		'true' => 'TRUE', 'True' => 'TRUE', 'TRUE' => 'TRUE', 'yes' => 'TRUE', 'Yes' => 'TRUE', 'YES' => 'TRUE', 'on' => 'TRUE', 'On' => 'TRUE', 'ON' => 'TRUE',
		'false' => 'FALSE', 'False' => 'FALSE', 'FALSE' => 'FALSE', 'no' => 'FALSE', 'No' => 'FALSE', 'NO' => 'FALSE', 'off' => 'FALSE', 'Off' => 'FALSE', 'OFF' => 'FALSE',
		'null' => 'NULL', 'Null' => 'NULL', 'NULL' => 'NULL',
	];

	const ESCAPE_SEQUENCES = [
		't' => "\t", 'n' => "\n", 'r' => "\r", 'f' => "\x0C", 'b' => "\x08", '"' => '"', '\\' => '\\', '/' => '/', '_' => "\xc2\xa0",
	];

	const BRACKETS = [
		'[' => ']',
		'{' => '}',
		'(' => ')',
	];

	/** @var string */
	private $input;

	/** @var array */
	private $tokens;

	/** @var int */
	private $pos;



	/**
	 * Decodes a NEON string.
	 * @param  string
	 * @return mixed
	 */
	public function decode($input)
	{
		if (!is_string($input)) {
			throw new \InvalidArgumentException(sprintf('Argument must be a string, %s given.', gettype($input)));

		} elseif (substr($input, 0, 3) === "\xEF\xBB\xBF") { // BOM
			$input = substr($input, 3);
		}
		$this->input = "\n" . str_replace("\r", '', $input); // \n forces indent detection

		$pattern = '~(' . implode(')|(', self::PATTERNS) . ')~Amix';
		$this->tokens = preg_split($pattern, $this->input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE);

		$last = end($this->tokens);
		if ($this->tokens && !preg_match($pattern, $last[0])) {
			$this->pos = count($this->tokens) - 1;
			$this->error();
		}

		$this->pos = 0;
		$res = $this->parse(NULL);

		while (isset($this->tokens[$this->pos])) {
			if ($this->tokens[$this->pos][0][0] === "\n") {
				$this->pos++;
			} else {
				$this->error();
			}
		}
		return $res;
	}


	/**
	 * @param  string  indentation (for block-parser)
	 * @param  mixed
	 * @return array
	 */
	private function parse($indent, $result = NULL, $key = NULL, $hasKey = FALSE)
	{
		$inlineParser = $indent === FALSE;
		$value = NULL;
		$hasValue = FALSE;
		$tokens = $this->tokens;
		$n = &$this->pos;
		$count = count($tokens);
		$mainResult = &$result;

		for (; $n < $count; $n++) {
			$t = $tokens[$n][0];

			if ($t === ',') { // ArrayEntry separator
				if ((!$hasKey && !$hasValue) || !$inlineParser) {
					$this->error();
				}
				$this->addValue($result, $hasKey ? $key : NULL, $hasValue ? $value : NULL);
				$hasKey = $hasValue = FALSE;

			} elseif ($t === ':' || $t === '=') { // KeyValuePair separator
				if ($hasValue && (is_array($value) || is_object($value))) {
					$this->error('Unacceptable key');

				} elseif ($hasKey && $key === NULL && $hasValue && !$inlineParser) {
					$n++;
					$result[] = $this->parse($indent . '  ', [], $value, TRUE);
					$newIndent = isset($tokens[$n], $tokens[$n + 1]) ? (string) substr($tokens[$n][0], 1) : ''; // not last
					if (strlen($newIndent) > strlen($indent)) {
						$n++;
						$this->error('Bad indentation');
					} elseif (strlen($newIndent) < strlen($indent)) {
						return $mainResult; // block parser exit point
					}
					$hasKey = $hasValue = FALSE;

				} elseif ($hasKey || !$hasValue) {
					$this->error();

				} else {
					$key = (string) $value;
					$hasKey = TRUE;
					$hasValue = FALSE;
					$result = &$mainResult;
				}

			} elseif ($t === '-') { // BlockArray bullet
				if ($hasKey || $hasValue || $inlineParser) {
					$this->error();
				}
				$key = NULL;
				$hasKey = TRUE;

			} elseif (($tmp = self::BRACKETS) && isset($tmp[$t])) { // Opening bracket [ ( {
				if ($hasValue) {
					if ($t !== '(') {
						$this->error();
					}
					$n++;
					if ($value instanceof Entity && $value->value === Neon::CHAIN) {
						end($value->attributes)->attributes = $this->parse(FALSE, []);
					} else {
						$value = new Entity($value, $this->parse(FALSE, []));
					}
				} else {
					$n++;
					$value = $this->parse(FALSE, []);
				}
				$hasValue = TRUE;
				if (!isset($tokens[$n]) || $tokens[$n][0] !== self::BRACKETS[$t]) { // unexpected type of bracket or block-parser
					$this->error();
				}

			} elseif ($t === ']' || $t === '}' || $t === ')') { // Closing bracket ] ) }
				if (!$inlineParser) {
					$this->error();
				}
				break;

			} elseif ($t[0] === "\n") { // Indent
				if ($inlineParser) {
					if ($hasKey || $hasValue) {
						$this->addValue($result, $hasKey ? $key : NULL, $hasValue ? $value : NULL);
						$hasKey = $hasValue = FALSE;
					}

				} else {
					while (isset($tokens[$n + 1]) && $tokens[$n + 1][0][0] === "\n") {
						$n++; // skip to last indent
					}
					if (!isset($tokens[$n + 1])) {
						break;
					}

					$newIndent = (string) substr($tokens[$n][0], 1);
					if ($indent === NULL) { // first iteration
						$indent = $newIndent;
					}
					$minlen = min(strlen($newIndent), strlen($indent));
					if ($minlen && (string) substr($newIndent, 0, $minlen) !== (string) substr($indent, 0, $minlen)) {
						$n++;
						$this->error('Invalid combination of tabs and spaces');
					}

					if (strlen($newIndent) > strlen($indent)) { // open new block-array or hash
						if ($hasValue || !$hasKey) {
							$n++;
							$this->error('Bad indentation');
						}
						$this->addValue($result, $key, $this->parse($newIndent));
						$newIndent = isset($tokens[$n], $tokens[$n + 1]) ? (string) substr($tokens[$n][0], 1) : ''; // not last
						if (strlen($newIndent) > strlen($indent)) {
							$n++;
							$this->error('Bad indentation');
						}
						$hasKey = FALSE;

					} else {
						if ($hasValue && !$hasKey) { // block items must have "key"; NULL key means list item
							break;

						} elseif ($hasKey) {
							$this->addValue($result, $key, $hasValue ? $value : NULL);
							if ($key !== NULL && !$hasValue && $newIndent === $indent && isset($tokens[$n + 1]) && $tokens[$n + 1][0] === '-') {
								$result = &$result[$key];
							}
							$hasKey = $hasValue = FALSE;
						}
					}

					if (strlen($newIndent) < strlen($indent)) { // close block
						return $mainResult; // block parser exit point
					}
				}

			} else { // Value
				if ($t[0] === '"' || $t[0] === "'") {
					if (preg_match('#^...\n++([\t ]*+)#', $t, $m)) {
						$converted = substr($t, 3, -3);
						$converted = str_replace("\n" . $m[1], "\n", $converted);
						$converted = preg_replace('#^\n|\n[\t ]*+\z#', '', $converted);
					} else {
						$converted = substr($t, 1, -1);
					}
					if ($t[0] === '"') {
						$converted = preg_replace_callback('#\\\\(?:ud[89ab][0-9a-f]{2}\\\\ud[c-f][0-9a-f]{2}|u[0-9a-f]{4}|x[0-9a-f]{2}|.)#i', [$this, 'cbString'], $converted);
					}
				} elseif (($fix56 = self::SIMPLE_TYPES) && isset($fix56[$t]) && (!isset($tokens[$n + 1][0]) || ($tokens[$n + 1][0] !== ':' && $tokens[$n + 1][0] !== '='))) {
					$converted = constant(self::SIMPLE_TYPES[$t]);
				} elseif (is_numeric($t)) {
					$converted = $t * 1;
				} elseif (preg_match(self::PATTERN_HEX, $t)) {
					$converted = hexdec($t);
				} elseif (preg_match(self::PATTERN_OCTAL, $t)) {
					$converted = octdec($t);
				} elseif (preg_match(self::PATTERN_BINARY, $t)) {
					$converted = bindec($t);
				} elseif (preg_match(self::PATTERN_DATETIME, $t)) {
					$converted = new \DateTimeImmutable($t);
				} else { // literal
					$converted = $t;
				}
				if ($hasValue) {
					if ($value instanceof Entity) { // Entity chaining
						if ($value->value !== Neon::CHAIN) {
							$value = new Entity(Neon::CHAIN, [$value]);
						}
						$value->attributes[] = new Entity($converted);
					} else {
						$this->error();
					}
				} else {
					$value = $converted;
					$hasValue = TRUE;
				}
			}
		}

		if ($inlineParser) {
			if ($hasKey || $hasValue) {
				$this->addValue($result, $hasKey ? $key : NULL, $hasValue ? $value : NULL);
			}
		} else {
			if ($hasValue && !$hasKey) { // block items must have "key"
				if ($result === NULL) {
					$result = $value; // simple value parser
				} else {
					$this->error();
				}
			} elseif ($hasKey) {
				$this->addValue($result, $key, $hasValue ? $value : NULL);
			}
		}
		return $mainResult;
	}


	private function addValue(&$result, $key, $value)
	{
		if ($key === NULL) {
			$result[] = $value;
		} elseif ($result && array_key_exists($key, $result)) {
			$this->error("Duplicated key '$key'");
		} else {
			$result[$key] = $value;
		}
	}


	private function cbString($m)
	{
		$sq = $m[0];
		if (($fix56 = self::ESCAPE_SEQUENCES) && isset($fix56[$sq[1]])) { // workaround for PHP 5.6
			return self::ESCAPE_SEQUENCES[$sq[1]];
		} elseif ($sq[1] === 'u' && strlen($sq) >= 6) {
			$lead = hexdec(substr($sq, 2, 4));
			$tail = hexdec(substr($sq, 8, 4));
			$code = $tail ? (0x2400 + (($lead - 0xD800) << 10) + $tail) : $lead;
			if ($code >= 0xD800 && $code <= 0xDFFF) {
				$this->error("Invalid UTF-8 (lone surrogate) $sq");
			}
			return iconv('UTF-32BE', 'UTF-8//IGNORE', pack('N', $code));
		} elseif ($sq[1] === 'x' && strlen($sq) === 4) {
			return chr(hexdec(substr($sq, 2)));
		} else {
			$this->error("Invalid escaping sequence $sq");
		}
	}


	private function error($message = "Unexpected '%s'")
	{
		$last = isset($this->tokens[$this->pos]) ? $this->tokens[$this->pos] : NULL;
		$offset = $last ? $last[1] : strlen($this->input);
		$text = substr($this->input, 0, $offset);
		$line = substr_count($text, "\n");
		$col = $offset - strrpos("\n" . $text, "\n") + 1;
		$token = $last ? str_replace("\n", '<new line>', substr($last[0], 0, 40)) : 'end';
		throw new Exception(str_replace('%s', $token, $message) . " on line $line, column $col.");
	}

}
