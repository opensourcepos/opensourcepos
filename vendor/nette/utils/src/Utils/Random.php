<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * Secure random string generator.
 */
class Random
{
	use Nette\StaticClass;

	/**
	 * Generate random string.
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function generate($length = 10, $charlist = '0-9a-z')
	{
		$charlist = count_chars(preg_replace_callback('#.-.#', function (array $m) {
			return implode('', range($m[0][0], $m[0][2]));
		}, $charlist), 3);
		$chLen = strlen($charlist);

		if ($length < 1) {
			throw new Nette\InvalidArgumentException('Length must be greater than zero.');
		} elseif ($chLen < 2) {
			throw new Nette\InvalidArgumentException('Character list must contain as least two chars.');
		}

		$res = '';
		if (PHP_VERSION_ID >= 70000) {
			for ($i = 0; $i < $length; $i++) {
				$res .= $charlist[random_int(0, $chLen - 1)];
			}
			return $res;
		}

		$bytes = '';
		if (function_exists('openssl_random_pseudo_bytes')) {
			$bytes = (string) openssl_random_pseudo_bytes($length, $secure);
			if (!$secure) {
				$bytes = '';
			}
		}
		if (strlen($bytes) < $length && function_exists('mcrypt_create_iv')) {
			$bytes = (string) mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
		}
		if (strlen($bytes) < $length && !defined('PHP_WINDOWS_VERSION_BUILD') && is_readable('/dev/urandom')) {
			$bytes = (string) file_get_contents('/dev/urandom', FALSE, NULL, -1, $length);
		}
		if (strlen($bytes) < $length) {
			$rand3 = md5(serialize($_SERVER), TRUE);
			$charlist = str_shuffle($charlist);
			for ($i = 0; $i < $length; $i++) {
				if ($i % 5 === 0) {
					list($rand1, $rand2) = explode(' ', microtime());
					$rand1 += lcg_value();
				}
				$rand1 *= $chLen;
				$res .= $charlist[($rand1 + $rand2 + ord($rand3[$i % strlen($rand3)])) % $chLen];
				$rand1 -= (int) $rand1;
			}
			return $res;
		}

		for ($i = 0; $i < $length; $i++) {
			$res .= $charlist[($i + ord($bytes[$i])) % $chLen];
		}
		return $res;
	}

}
