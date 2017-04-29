<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte\Runtime;

use Latte;


/**
 * Template filters.
 * @internal
 */
class Filters
{
	/** @deprecated */
	public static $dateFormat = '%x';

	/** @internal @var bool  use XHTML syntax? */
	public static $xhtml = FALSE;


	/**
	 * Escapes string for use inside HTML template.
	 * @param  mixed  UTF-8 encoding
	 * @param  int    optional attribute quotes
	 * @return string
	 */
	public static function escapeHtml($s, $quotes = ENT_QUOTES)
	{
		if ($s instanceof IHtmlString || $s instanceof \Nette\Utils\IHtmlString) {
			return $s->__toString(TRUE);
		}
		$s = (string) $s;
		if ($quotes !== ENT_NOQUOTES && strpos($s, '`') !== FALSE && strpbrk($s, ' <>"\'') === FALSE) {
			$s .= ' ';
		}
		return htmlSpecialChars($s, $quotes, 'UTF-8');
	}


	/**
	 * Escapes string for use inside HTML comments.
	 * @param  string  UTF-8 encoding
	 * @return string
	 */
	public static function escapeHtmlComment($s)
	{
		$s = (string) $s;
		if ($s && ($s[0] === '-' || $s[0] === '>' || $s[0] === '!')) {
			$s = ' ' . $s;
		}
		return str_replace('-', '- ', $s); // dash is very problematic character in comments
	}


	/**
	 * Escapes string for use inside XML 1.0 template.
	 * @param  string UTF-8 encoding
	 * @return string
	 */
	public static function escapeXML($s)
	{
		// XML 1.0: \x09 \x0A \x0D and C1 allowed directly, C0 forbidden
		// XML 1.1: \x00 forbidden directly and as a character reference,
		//   \x09 \x0A \x0D \x85 allowed directly, C0, C1 and \x7F allowed as character references
		return htmlSpecialChars(preg_replace('#[\x00-\x08\x0B\x0C\x0E-\x1F]+#', '', $s), ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Escapes string for use inside CSS template.
	 * @param  string UTF-8 encoding
	 * @return string
	 */
	public static function escapeCss($s)
	{
		// http://www.w3.org/TR/2006/WD-CSS21-20060411/syndata.html#q6
		return addcslashes($s, "\x00..\x1F!\"#$%&'()*+,./:;<=>?@[\\]^`{|}~");
	}


	/**
	 * Escapes variables for use inside <script>.
	 * @param  mixed  UTF-8 encoding
	 * @return string
	 */
	public static function escapeJs($s)
	{
		if ($s instanceof IHtmlString || $s instanceof \Nette\Utils\IHtmlString) {
			$s = $s->__toString(TRUE);
		}

		$json = json_encode($s, PHP_VERSION_ID >= 50400 ? JSON_UNESCAPED_UNICODE : 0);
		if ($error = json_last_error()) {
			throw new \RuntimeException(PHP_VERSION_ID >= 50500 ? json_last_error_msg() : 'JSON encode error', $error);
		}

		return str_replace(array("\xe2\x80\xa8", "\xe2\x80\xa9", ']]>', '<!'), array('\u2028', '\u2029', ']]\x3E', '\x3C!'), $json);
	}


	/**
	 * Escapes string for use inside iCal template.
	 * @param  mixed  UTF-8 encoding
	 * @return string
	 */
	public static function escapeICal($s)
	{
		// http://www.ietf.org/rfc/rfc5545.txt
		return addcslashes(preg_replace('#[\x00-\x08\x0B\x0C-\x1F]+#', '', $s), "\";\\,:\n");
	}


	/**
	 * Sanitizes string for use inside href attribute.
	 * @param  string
	 * @return string
	 */
	public static function safeUrl($s)
	{
		return preg_match('~^(?:(?:https?|ftp)://[^@]+(?:/.*)?|mailto:.+|[/?#].*|[^:]+)\z~i', $s) ? $s : '';
	}


	/**
	 * Replaces all repeated white spaces with a single space.
	 * @param  string UTF-8 encoding or 8-bit
	 * @return string
	 */
	public static function strip($s)
	{
		return preg_replace_callback(
			'#(</textarea|</pre|</script|^).*?(?=<textarea|<pre|<script|\z)#si',
			function ($m) {
				return trim(preg_replace('#[ \t\r\n]+#', ' ', $m[0]));
			},
			$s
		);
	}


	/**
	 * Indents the HTML content from the left.
	 * @param  string UTF-8 encoding or 8-bit
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function indent($s, $level = 1, $chars = "\t")
	{
		if ($level >= 1) {
			$s = preg_replace_callback('#<(textarea|pre).*?</\\1#si', function ($m) {
				return strtr($m[0], " \t\r\n", "\x1F\x1E\x1D\x1A");
			}, $s);
			if (preg_last_error()) {
				throw new Latte\RegexpException(NULL, preg_last_error());
			}
			$s = preg_replace('#(?:^|[\r\n]+)(?=[^\r\n])#', '$0' . str_repeat($chars, $level), $s);
			$s = strtr($s, "\x1F\x1E\x1D\x1A", " \t\r\n");
		}
		return $s;
	}


	/**
	 * Date/time formatting.
	 * @param  string|int|DateTime|DateInterval
	 * @param  string
	 * @return string
	 */
	public static function date($time, $format = NULL)
	{
		if ($time == NULL) { // intentionally ==
			return NULL;
		}

		if (!isset($format)) {
			$format = self::$dateFormat;
		}

		if ($time instanceof \DateInterval) {
			return $time->format($format);

		} elseif (is_numeric($time)) {
			$time = new \DateTime('@' . $time);
			$time->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

		} elseif (!$time instanceof \DateTime && !$time instanceof \DateTimeInterface) {
			$time = new \DateTime($time);
		}
		return strpos($format, '%') === FALSE
			? $time->format($format) // formats using date()
			: strftime($format, $time->format('U')); // formats according to locales
	}


	/**
	 * Converts to human readable file size.
	 * @param  int
	 * @param  int
	 * @return string
	 */
	public static function bytes($bytes, $precision = 2)
	{
		$bytes = round($bytes);
		$units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
		foreach ($units as $unit) {
			if (abs($bytes) < 1024 || $unit === end($units)) {
				break;
			}
			$bytes = $bytes / 1024;
		}
		return round($bytes, $precision) . ' ' . $unit;
	}


	/**
	 * Performs a search and replace.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function replace($subject, $search, $replacement = '')
	{
		return str_replace($search, $replacement, $subject);
	}


	/**
	 * Perform a regular expression search and replace.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function replaceRe($subject, $pattern, $replacement = '')
	{
		$res = preg_replace($pattern, $replacement, $subject);
		if (preg_last_error()) {
			throw new Latte\RegexpException(NULL, preg_last_error());
		}
		return $res;
	}


	/**
	 * The data: URI generator.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function dataStream($data, $type = NULL)
	{
		if ($type === NULL) {
			$type = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $data);
		}
		return 'data:' . ($type ? "$type;" : '') . 'base64,' . base64_encode($data);
	}


	/**
	 * @param  string
	 * @return string
	 */
	public static function nl2br($value)
	{
		return nl2br($value, self::$xhtml);
	}


	/**
	 * Returns a part of UTF-8 string.
	 * @param  string
	 * @param  int
	 * @param  int
	 * @return string
	 */
	public static function substring($s, $start, $length = NULL)
	{
		if ($length === NULL) {
			$length = self::length($s);
		}
		if (function_exists('mb_substr')) {
			return mb_substr($s, $start, $length, 'UTF-8'); // MB is much faster
		}
		return iconv_substr($s, $start, $length, 'UTF-8');
	}


	/**
	 * Truncates string to maximal length.
	 * @param  string  UTF-8 encoding
	 * @param  int
	 * @param  string  UTF-8 encoding
	 * @return string
	 */
	public static function truncate($s, $maxLen, $append = "\xE2\x80\xA6")
	{
		if (self::length($s) > $maxLen) {
			$maxLen = $maxLen - self::length($append);
			if ($maxLen < 1) {
				return $append;

			} elseif (preg_match('#^.{1,'.$maxLen.'}(?=[\s\x00-/:-@\[-`{-~])#us', $s, $matches)) {
				return $matches[0] . $append;

			} else {
				return self::substring($s, 0, $maxLen) . $append;
			}
		}
		return $s;
	}


	/**
	 * Convert to lower case.
	 * @return string
	 */
	public static function lower($s)
	{
		return mb_strtolower($s, 'UTF-8');
	}


	/**
	 * Convert to upper case.
	 * @return string
	 */
	public static function upper($s)
	{
		return mb_strtoupper($s, 'UTF-8');
	}


	/**
	 * Convert first character to upper case.
	 * @return string
	 */
	public static function firstUpper($s)
	{
		return self::upper(self::substring($s, 0, 1)) . self::substring($s, 1);
	}


	/**
	 * Capitalize string.
	 * @return string
	 */
	public static function capitalize($s)
	{
		return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
	}


	/**
	 * Returns UTF-8 string length.
	 * @return int
	 */
	public static function length($s)
	{
		return strlen(utf8_decode($s)); // fastest way
	}


	/**
	 * Strips whitespace.
	 * @param  string  UTF-8 encoding
	 * @param  string
	 * @return string
	 */
	public static function trim($s, $charlist = " \t\n\r\0\x0B\xC2\xA0")
	{
		$charlist = preg_quote($charlist, '#');
		$s = preg_replace('#^['.$charlist.']+|['.$charlist.']+\z#u', '', $s);
		if (preg_last_error()) {
			throw new Latte\RegexpException(NULL, preg_last_error());
		}
		return $s;
	}


	/**
	 * Returns element's attributes.
	 * @return string
	 */
	public static function htmlAttributes($attrs)
	{
		if (!is_array($attrs)) {
			return '';
		}

		$s = '';
		foreach ($attrs as $key => $value) {
			if ($value === NULL || $value === FALSE) {
				continue;

			} elseif ($value === TRUE) {
				if (static::$xhtml) {
					$s .= ' ' . $key . '="' . $key . '"';
				} else {
					$s .= ' ' . $key;
				}
				continue;

			} elseif (is_array($value)) {
				$tmp = NULL;
				foreach ($value as $k => $v) {
					if ($v != NULL) { // intentionally ==, skip NULLs & empty string
						//  composite 'style' vs. 'others'
						$tmp[] = $v === TRUE ? $k : (is_string($k) ? $k . ':' . $v : $v);
					}
				}
				if ($tmp === NULL) {
					continue;
				}

				$value = implode($key === 'style' || !strncmp($key, 'on', 2) ? ';' : ' ', $tmp);

			} else {
				$value = (string) $value;
			}

			$q = strpos($value, '"') === FALSE ? '"' : "'";
			$s .= ' ' . $key . '=' . $q
				. str_replace(
					array('&', $q, '<'),
					array('&amp;', $q === '"' ? '&quot;' : '&#39;', self::$xhtml ? '&lt;' : '<'),
					$value
				)
				. (strpos($value, '`') !== FALSE && strpbrk($value, ' <>"\'') === FALSE ? ' ' : '')
				. $q;
		}
		return $s;
	}

}
