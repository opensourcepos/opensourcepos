<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Reflection;

use Nette;
use Nette\Utils\Strings;


/**
 * Annotations support for PHP.
 * @Annotation
 */
class AnnotationsParser
{
	use Nette\StaticClass;

	/** @internal single & double quoted PHP string */
	const RE_STRING = '\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';

	/** @internal identifier */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF-\\\]*';

	/** @var bool */
	public static $useReflection;

	/** @var bool */
	public static $autoRefresh = TRUE;

	/** @var array */
	public static $inherited = ['description', 'param', 'return'];

	/** @var array */
	private static $cache;

	/** @var array */
	private static $timestamps;

	/** @var Nette\Caching\IStorage */
	private static $cacheStorage;


	/**
	 * Returns annotations.
	 * @param  \ReflectionClass|\ReflectionMethod|\ReflectionProperty
	 * @return array
	 */
	public static function getAll(\Reflector $r)
	{
		if ($r instanceof \ReflectionClass) {
			$type = $r->getName();
			$member = 'class';
			$file = $r->getFileName();

		} elseif ($r instanceof \ReflectionMethod) {
			$type = $r->getDeclaringClass()->getName();
			$member = $r->getName();
			$file = $r->getFileName();

		} elseif ($r instanceof \ReflectionFunction) {
			$type = NULL;
			$member = $r->getName();
			$file = $r->getFileName();

		} else {
			$type = $r->getDeclaringClass()->getName();
			$member = '$' . $r->getName();
			$file = $r->getDeclaringClass()->getFileName();
		}

		if (self::$useReflection === NULL) { // detects whether is reflection available
			self::$useReflection = (bool) ClassType::from(__CLASS__)->getDocComment();
		}

		if (!self::$useReflection) { // auto-expire cache
			if ($file && isset(self::$timestamps[$file]) && self::$timestamps[$file] !== filemtime($file)) {
				unset(self::$cache[$type]);
			}
			unset(self::$timestamps[$file]);
		}

		if (isset(self::$cache[$type][$member])) { // is value cached?
			return self::$cache[$type][$member];
		}

		if (self::$useReflection) {
			$annotations = self::parseComment($r->getDocComment());

		} else {
			$outerCache = self::getCache();

			if (self::$cache === NULL) {
				self::$cache = (array) $outerCache->load('list');
				self::$timestamps = isset(self::$cache['*']) ? self::$cache['*'] : [];
			}

			if (!isset(self::$cache[$type]) && $file) {
				self::$cache['*'][$file] = filemtime($file);
				foreach (static::parsePhp(file_get_contents($file)) as $class => $info) {
					foreach ($info as $prop => $comment) {
						if ($prop !== 'use') {
							self::$cache[$class][$prop] = self::parseComment($comment);
						}
					}
				}
				$outerCache->save('list', self::$cache);
			}

			if (isset(self::$cache[$type][$member])) {
				$annotations = self::$cache[$type][$member];
			} else {
				$annotations = [];
			}
		}

		if ($r instanceof \ReflectionMethod && !$r->isPrivate()
			&& (!$r->isConstructor() || !empty($annotations['inheritdoc'][0]))
		) {
			try {
				$inherited = self::getAll(new \ReflectionMethod(get_parent_class($type), $member));
			} catch (\ReflectionException $e) {
				try {
					$inherited = self::getAll($r->getPrototype());
				} catch (\ReflectionException $e) {
					$inherited = [];
				}
			}
			$annotations += array_intersect_key($inherited, array_flip(self::$inherited));
		}

		return self::$cache[$type][$member] = $annotations;
	}


	/**
	 * Expands class name into FQN.
	 * @param  string
	 * @return string  fully qualified class name
	 * @throws Nette\InvalidArgumentException
	 */
	public static function expandClassName($name, \ReflectionClass $reflector)
	{
		if (empty($name)) {
			throw new Nette\InvalidArgumentException('Class name must not be empty.');

		} elseif ($name === 'self') {
			return $reflector->getName();

		} elseif ($name[0] === '\\') { // already fully qualified
			return ltrim($name, '\\');
		}

		$filename = $reflector->getFileName();
		$parsed = static::getCache()->load($filename, function (&$dp) use ($filename) {
			if (self::$autoRefresh) {
				$dp[Nette\Caching\Cache::FILES] = $filename;
			}
			return self::parsePhp(file_get_contents($filename));
		});
		$uses = array_change_key_case((array) $tmp = &$parsed[$reflector->getName()]['use']);
		$parts = explode('\\', $name, 2);
		$parts[0] = strtolower($parts[0]);
		if (isset($uses[$parts[0]])) {
			$parts[0] = $uses[$parts[0]];
			return implode('\\', $parts);

		} elseif ($reflector->inNamespace()) {
			return $reflector->getNamespaceName() . '\\' . $name;

		} else {
			return $name;
		}
	}


	/**
	 * Parses phpDoc comment.
	 * @param  string
	 * @return array
	 */
	private static function parseComment($comment)
	{
		static $tokens = ['true' => TRUE, 'false' => FALSE, 'null' => NULL, '' => TRUE];

		$res = [];
		$comment = preg_replace('#^\s*\*\s?#ms', '', trim($comment, '/*'));
		$parts = preg_split('#^\s*(?=@'.self::RE_IDENTIFIER.')#m', $comment, 2);

		$description = trim($parts[0]);
		if ($description !== '') {
			$res['description'] = [$description];
		}

		$matches = Strings::matchAll(
			isset($parts[1]) ? $parts[1] : '',
			'~
				(?<=\s|^)@('.self::RE_IDENTIFIER.')[ \t]*      ##  annotation
				(
					\((?>'.self::RE_STRING.'|[^\'")@]+)+\)|  ##  (value)
					[^(@\r\n][^@\r\n]*|)                     ##  value
			~xi'
		);

		foreach ($matches as $match) {
			list(, $name, $value) = $match;

			if (substr($value, 0, 1) === '(') {
				$items = [];
				$key = '';
				$val = TRUE;
				$value[0] = ',';
				while ($m = Strings::match(
					$value,
					'#\s*,\s*(?>(' . self::RE_IDENTIFIER . ')\s*=\s*)?(' . self::RE_STRING . '|[^\'"),\s][^\'"),]*)#A')
				) {
					$value = substr($value, strlen($m[0]));
					list(, $key, $val) = $m;
					$val = rtrim($val);
					if ($val[0] === "'" || $val[0] === '"') {
						$val = substr($val, 1, -1);

					} elseif (is_numeric($val)) {
						$val = 1 * $val;

					} else {
						$lval = strtolower($val);
						$val = array_key_exists($lval, $tokens) ? $tokens[$lval] : $val;
					}

					if ($key === '') {
						$items[] = $val;

					} else {
						$items[$key] = $val;
					}
				}

				$value = count($items) < 2 && $key === '' ? $val : $items;

			} else {
				$value = trim($value);
				if (is_numeric($value)) {
					$value = 1 * $value;

				} else {
					$lval = strtolower($value);
					$value = array_key_exists($lval, $tokens) ? $tokens[$lval] : $value;
				}
			}

			$res[$name][] = is_array($value) ? Nette\Utils\ArrayHash::from($value) : $value;
		}

		return $res;
	}


	/**
	 * Parses PHP file.
	 * @param  string
	 * @return array [class => [prop => comment (or 'use' => [alias => class])]
	 * @internal
	 */
	public static function parsePhp($code)
	{
		if (Strings::match($code, '#//nette'.'loader=(\S*)#')) {
			return;
		}

		$tokens = @token_get_all($code);
		$namespace = $class = $classLevel = $level = $docComment = NULL;
		$res = $uses = [];

		while (list(, $token) = each($tokens)) {
			switch (is_array($token) ? $token[0] : $token) {
				case T_DOC_COMMENT:
					$docComment = $token[1];
					break;

				case T_NAMESPACE:
					$namespace = ltrim(self::fetch($tokens, [T_STRING, T_NS_SEPARATOR]) . '\\', '\\');
					$uses = [];
					break;

				case T_CLASS:
				case T_INTERFACE:
				case T_TRAIT:
					if ($name = self::fetch($tokens, T_STRING)) {
						$class = $namespace . $name;
						$classLevel = $level + 1;
						$res[$class] = [];
						if ($docComment) {
							$res[$class]['class'] = $docComment;
						}
						if ($uses) {
							$res[$class]['use'] = $uses;
						}
					}
					break;

				case T_FUNCTION:
					self::fetch($tokens, '&');
					if ($level === $classLevel && $docComment && ($name = self::fetch($tokens, T_STRING))) {
						$res[$class][$name] = $docComment;
					}
					break;

				case T_VAR:
				case T_PUBLIC:
				case T_PROTECTED:
					self::fetch($tokens, T_STATIC);
					if ($level === $classLevel && $docComment && ($name = self::fetch($tokens, T_VARIABLE))) {
						$res[$class][$name] = $docComment;
					}
					break;

				case T_USE:
					while (!$class && ($name = self::fetch($tokens, [T_STRING, T_NS_SEPARATOR]))) {
						$name = ltrim($name, '\\');
						if (self::fetch($tokens, '{')) {
							while ($suffix = self::fetch($tokens, [T_STRING, T_NS_SEPARATOR])) {
								if (self::fetch($tokens, T_AS)) {
									$uses[self::fetch($tokens, T_STRING)] = $name . $suffix;
								} else {
									$tmp = explode('\\', $suffix);
									$uses[end($tmp)] = $name . $suffix;
								}
								if (!self::fetch($tokens, ',')) {
									break;
								}
							}

						} elseif (self::fetch($tokens, T_AS)) {
							$uses[self::fetch($tokens, T_STRING)] = $name;

						} else {
							$tmp = explode('\\', $name);
							$uses[end($tmp)] = $name;
						}
						if (!self::fetch($tokens, ',')) {
							break;
						}
					}
					break;

				case T_CURLY_OPEN:
				case T_DOLLAR_OPEN_CURLY_BRACES:
				case '{':
					$level++;
					break;

				case '}':
					if ($level === $classLevel) {
						$class = $classLevel = NULL;
					}
					$level--;
					// break omitted
				case ';':
					$docComment = NULL;
			}
		}

		return $res;
	}


	private static function fetch(&$tokens, $take)
	{
		$res = NULL;
		while ($token = current($tokens)) {
			list($token, $s) = is_array($token) ? $token : [$token, $token];
			if (in_array($token, (array) $take, TRUE)) {
				$res .= $s;
			} elseif (!in_array($token, [T_DOC_COMMENT, T_WHITESPACE, T_COMMENT], TRUE)) {
				break;
			}
			next($tokens);
		}
		return $res;
	}


	/********************* backend ****************d*g**/


	/**
	 * @return void
	 */
	public static function setCacheStorage(Nette\Caching\IStorage $storage)
	{
		self::$cacheStorage = $storage;
	}


	/**
	 * @return Nette\Caching\IStorage
	 */
	public static function getCacheStorage()
	{
		if (!self::$cacheStorage) {
			self::$cacheStorage = new Nette\Caching\Storages\MemoryStorage();
		}
		return self::$cacheStorage;
	}


	/**
	 * @return Nette\Caching\Cache
	 */
	private static function getCache()
	{
		return new Nette\Caching\Cache(static::getCacheStorage(), 'Nette.Reflection.Annotations');
	}

}
