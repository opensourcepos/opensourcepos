<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * JSON encoder and decoder.
 */
class Json
{
	use Nette\StaticClass;

	const FORCE_ARRAY = 0b0001;
	const PRETTY = 0b0010;


	/**
	 * Returns the JSON representation of a value.
	 * @param  mixed
	 * @param  int  accepts Json::PRETTY
	 * @return string
	 */
	public static function encode($value, $options = 0)
	{
		$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
			| ($options & self::PRETTY ? JSON_PRETTY_PRINT : 0)
			| (defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0); // since PHP 5.6.6 & PECL JSON-C 1.3.7

		$json = json_encode($value, $flags);
		if ($error = json_last_error()) {
			throw new JsonException(json_last_error_msg(), $error);
		}

		if (PHP_VERSION_ID < 70100) {
			$json = str_replace(["\xe2\x80\xa8", "\xe2\x80\xa9"], ['\u2028', '\u2029'], $json);
		}

		return $json;
	}


	/**
	 * Decodes a JSON string.
	 * @param  string
	 * @param  int  accepts Json::FORCE_ARRAY
	 * @return mixed
	 */
	public static function decode($json, $options = 0)
	{
		$forceArray = (bool) ($options & self::FORCE_ARRAY);
		$flags = JSON_BIGINT_AS_STRING;

		if (PHP_VERSION_ID < 70000) {
			$json = (string) $json;
			if ($json === '') {
				throw new JsonException('Syntax error');
			} elseif (!$forceArray && preg_match('#(?<=[^\\\\]")\\\\u0000(?:[^"\\\\]|\\\\.)*+"\s*+:#', $json)) {
				throw new JsonException('The decoded property name is invalid'); // fatal error when object key starts with \u0000
			} elseif (defined('JSON_C_VERSION') && !preg_match('##u', $json)) {
				throw new JsonException('Invalid UTF-8 sequence', 5);
			} elseif (defined('JSON_C_VERSION') && PHP_INT_SIZE === 8) {
				$flags &= ~JSON_BIGINT_AS_STRING; // not implemented in PECL JSON-C 1.3.2 for 64bit systems
			}
		}

		$value = json_decode($json, $forceArray, 512, $flags);
		if ($error = json_last_error()) {
			throw new JsonException(json_last_error_msg(), $error);
		}

		return $value;
	}

}
