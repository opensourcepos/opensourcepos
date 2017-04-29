<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Neon;


/**
 * Simple generator for Nette Object Notation.
 */
class Encoder
{
	const BLOCK = 1;


	/**
	 * Returns the NEON representation of a value.
	 * @param  mixed
	 * @param  int
	 * @return string
	 */
	public function encode($var, $options = NULL)
	{
		if ($var instanceof \DateTimeInterface) {
			return $var->format('Y-m-d H:i:s O');

		} elseif ($var instanceof Entity) {
			if ($var->value === Neon::CHAIN) {
				return implode('', array_map([$this, 'encode'], $var->attributes));
			}
			return $this->encode($var->value) . '('
				. (is_array($var->attributes) ? substr($this->encode($var->attributes), 1, -1) : '') . ')';
		}

		if (is_object($var)) {
			$obj = $var;
			$var = [];
			foreach ($obj as $k => $v) {
				$var[$k] = $v;
			}
		}

		if (is_array($var)) {
			$isList = !$var || array_keys($var) === range(0, count($var) - 1);
			$s = '';
			if ($options & self::BLOCK) {
				if (count($var) === 0) {
					return '[]';
				}
				foreach ($var as $k => $v) {
					$v = $this->encode($v, self::BLOCK);
					$s .= ($isList ? '-' : $this->encode($k) . ':')
						. (strpos($v, "\n") === FALSE
							? ' ' . $v . "\n"
							: "\n" . preg_replace('#^(?=.)#m', "\t", $v) . (substr($v, -2, 1) === "\n" ? '' : "\n"));
				}
				return $s;

			} else {
				foreach ($var as $k => $v) {
					$s .= ($isList ? '' : $this->encode($k) . ': ') . $this->encode($v) . ', ';
				}
				return ($isList ? '[' : '{') . substr($s, 0, -2) . ($isList ? ']' : '}');
			}

		} elseif (is_string($var) && !is_numeric($var)
			&& !preg_match('~[\x00-\x1F]|^\d{4}|^(true|false|yes|no|on|off|null)\z~i', $var)
			&& preg_match('~^' . Decoder::PATTERNS[1] . '\z~x', $var) // 1 = literals
		) {
			return $var;

		} elseif (is_float($var)) {
			$var = json_encode($var);
			return strpos($var, '.') === FALSE ? $var . '.0' : $var;

		} else {
			return json_encode($var, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}
	}

}
