<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Tracy;

use Tracy;


/**
 * FireLogger console logger.
 *
 * @see http://firelogger.binaryage.com
 */
class FireLogger implements ILogger
{
	/** @var int  */
	public $maxDepth = 3;

	/** @var int  */
	public $maxLength = 150;

	/** @var array  */
	private $payload = ['logs' => []];


	/**
	 * Sends message to FireLogger console.
	 * @param  mixed
	 * @return bool    was successful?
	 */
	public function log($message, $priority = self::DEBUG)
	{
		if (!isset($_SERVER['HTTP_X_FIRELOGGER']) || headers_sent()) {
			return FALSE;
		}

		$item = [
			'name' => 'PHP',
			'level' => $priority,
			'order' => count($this->payload['logs']),
			'time' => str_pad(number_format((microtime(TRUE) - Debugger::$time) * 1000, 1, '.', ' '), 8, '0', STR_PAD_LEFT) . ' ms',
			'template' => '',
			'message' => '',
			'style' => 'background:#767ab6',
		];

		$args = func_get_args();
		if (isset($args[0]) && is_string($args[0])) {
			$item['template'] = array_shift($args);
		}

		if (isset($args[0]) && ($args[0] instanceof \Exception || $args[0] instanceof \Throwable)) {
			$e = array_shift($args);
			$trace = $e->getTrace();
			if (isset($trace[0]['class']) && $trace[0]['class'] === 'Tracy\Debugger'
				&& ($trace[0]['function'] === 'shutdownHandler' || $trace[0]['function'] === 'errorHandler')
			) {
				unset($trace[0]);
			}

			$file = str_replace(dirname(dirname(dirname($e->getFile()))), "\xE2\x80\xA6", $e->getFile());
			$item['template'] = ($e instanceof \ErrorException ? '' : Helpers::getClass($e) . ': ')
				. $e->getMessage() . ($e->getCode() ? ' #' . $e->getCode() : '') . ' in ' . $file . ':' . $e->getLine();
			$item['pathname'] = $e->getFile();
			$item['lineno'] = $e->getLine();

		} else {
			$trace = debug_backtrace();
			if (isset($trace[1]['class']) && $trace[1]['class'] === 'Tracy\Debugger'
				&& ($trace[1]['function'] === 'fireLog')
			) {
				unset($trace[0]);
			}

			foreach ($trace as $frame) {
				if (isset($frame['file']) && is_file($frame['file'])) {
					$item['pathname'] = $frame['file'];
					$item['lineno'] = $frame['line'];
					break;
				}
			}
		}

		$item['exc_info'] = ['', '', []];
		$item['exc_frames'] = [];

		foreach ($trace as $frame) {
			$frame += ['file' => NULL, 'line' => NULL, 'class' => NULL, 'type' => NULL, 'function' => NULL, 'object' => NULL, 'args' => NULL];
			$item['exc_info'][2][] = [$frame['file'], $frame['line'], "$frame[class]$frame[type]$frame[function]", $frame['object']];
			$item['exc_frames'][] = $frame['args'];
		}

		if (isset($args[0]) && in_array($args[0], [self::DEBUG, self::INFO, self::WARNING, self::ERROR, self::CRITICAL], TRUE)) {
			$item['level'] = array_shift($args);
		}

		$item['args'] = $args;

		$this->payload['logs'][] = $this->jsonDump($item, -1);
		foreach (str_split(base64_encode(json_encode($this->payload)), 4990) as $k => $v) {
			header("FireLogger-de11e-$k:$v");
		}
		return TRUE;
	}


	/**
	 * Dump implementation for JSON.
	 * @param  mixed  variable to dump
	 * @param  int    current recursion level
	 * @return string
	 */
	private function jsonDump(&$var, $level = 0)
	{
		if (is_bool($var) || is_null($var) || is_int($var) || is_float($var)) {
			return $var;

		} elseif (is_string($var)) {
			return Dumper::encodeString($var, $this->maxLength);

		} elseif (is_array($var)) {
			static $marker;
			if ($marker === NULL) {
				$marker = uniqid("\x00", TRUE);
			}
			if (isset($var[$marker])) {
				return "\xE2\x80\xA6RECURSION\xE2\x80\xA6";

			} elseif ($level < $this->maxDepth || !$this->maxDepth) {
				$var[$marker] = TRUE;
				$res = [];
				foreach ($var as $k => &$v) {
					if ($k !== $marker) {
						$res[$this->jsonDump($k)] = $this->jsonDump($v, $level + 1);
					}
				}
				unset($var[$marker]);
				return $res;

			} else {
				return " \xE2\x80\xA6 ";
			}

		} elseif (is_object($var)) {
			$arr = (array) $var;
			static $list = [];
			if (in_array($var, $list, TRUE)) {
				return "\xE2\x80\xA6RECURSION\xE2\x80\xA6";

			} elseif ($level < $this->maxDepth || !$this->maxDepth) {
				$list[] = $var;
				$res = ["\x00" => '(object) ' . Helpers::getClass($var)];
				foreach ($arr as $k => &$v) {
					if (isset($k[0]) && $k[0] === "\x00") {
						$k = substr($k, strrpos($k, "\x00") + 1);
					}
					$res[$this->jsonDump($k)] = $this->jsonDump($v, $level + 1);
				}
				array_pop($list);
				return $res;

			} else {
				return " \xE2\x80\xA6 ";
			}

		} elseif (is_resource($var)) {
			return 'resource ' . get_resource_type($var);

		} else {
			return 'unknown type';
		}
	}

}
