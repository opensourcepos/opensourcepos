<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * PHP reflection helpers.
 */
class Reflection
{
	use Nette\StaticClass;

	private static $builtinTypes = [
		'string' => 1, 'int' => 1, 'float' => 1, 'bool' => 1, 'array' => 1,
		'callable' => 1, 'iterable' => 1, 'void' => 1
	];


	/**
	 * @param  string
	 * @return bool
	 */
	public static function isBuiltinType($type)
	{
		return isset(self::$builtinTypes[strtolower($type)]);
	}


	/**
	 * @return string|NULL
	 */
	public static function getReturnType(\ReflectionFunctionAbstract $func)
	{
		return PHP_VERSION_ID >= 70000 && $func->hasReturnType()
			? self::normalizeType((string) $func->getReturnType(), $func)
			: NULL;
	}


	/**
	 * @return string|NULL
	 */
	public static function getParameterType(\ReflectionParameter $param)
	{
		if (PHP_VERSION_ID >= 70000) {
			return $param->hasType()
				? self::normalizeType((string) $param->getType(), $param)
				: NULL;
		} elseif ($param->isArray() || $param->isCallable()) {
			return $param->isArray() ? 'array' : 'callable';
		} else {
			try {
				return ($ref = $param->getClass()) ? $ref->getName() : NULL;
			} catch (\ReflectionException $e) {
				if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
					return $m[1];
				}
				throw $e;
			}
		}
	}


	private static function normalizeType($type, $reflection)
	{
		$lower = strtolower($type);
		if ($lower === 'self') {
			return $reflection->getDeclaringClass()->getName();
		} elseif ($lower === 'parent' && $reflection->getDeclaringClass()->getParentClass()) {
			return $reflection->getDeclaringClass()->getParentClass()->getName();
		} else {
			return $type;
		}
	}


	/**
	 * @return mixed
	 * @throws \ReflectionException when default value is not available or resolvable
	 */
	public static function getParameterDefaultValue(\ReflectionParameter $param)
	{
		if ($param->isDefaultValueConstant()) {
			$const = $orig = $param->getDefaultValueConstantName();
			$pair = explode('::', $const);
			if (isset($pair[1]) && strtolower($pair[0]) === 'self') {
				$const = $param->getDeclaringClass()->getName() . '::' . $pair[1];
			}
			if (!defined($const)) {
				$const = substr((string) strrchr($const, '\\'), 1);
				if (isset($pair[1]) || !defined($const)) {
					$name = self::toString($param);
					throw new \ReflectionException("Unable to resolve constant $orig used as default value of $name.");
				}
			}
			return constant($const);
		}
		return $param->getDefaultValue();
	}


	/**
	 * Returns declaring class or trait.
	 * @return \ReflectionClass
	 */
	public static function getPropertyDeclaringClass(\ReflectionProperty $prop)
	{
		foreach ($prop->getDeclaringClass()->getTraits() as $trait) {
			if ($trait->hasProperty($prop->getName())) {
				return self::getPropertyDeclaringClass($trait->getProperty($prop->getName()));
			}
		}
		return $prop->getDeclaringClass();
	}


	/**
	 * Are documentation comments available?
	 * @return bool
	 */
	public static function areCommentsAvailable()
	{
		static $res;
		return $res === NULL
			? $res = (bool) (new \ReflectionMethod(__METHOD__))->getDocComment()
			: $res;
	}


	/**
	 * @return string
	 */
	public static function toString(\Reflector $ref)
	{
		if ($ref instanceof \ReflectionClass) {
			return $ref->getName();
		} elseif ($ref instanceof \ReflectionMethod) {
			return $ref->getDeclaringClass()->getName() . '::' . $ref->getName();
		} elseif ($ref instanceof \ReflectionFunction) {
			return $ref->getName();
		} elseif ($ref instanceof \ReflectionProperty) {
			return self::getPropertyDeclaringClass($ref)->getName() . '::$' . $ref->getName();
		} elseif ($ref instanceof \ReflectionParameter) {
			return '$' . $ref->getName() . ' in ' . self::toString($ref->getDeclaringFunction()) . '()';
		} else {
			throw new Nette\InvalidArgumentException;
		}
	}


	/**
	 * Expands class name into full name.
	 * @param  string
	 * @return string  full name
	 * @throws Nette\InvalidArgumentException
	 */
	public static function expandClassName($name, \ReflectionClass $rc)
	{
		$lower = strtolower($name);
		if (empty($name)) {
			throw new Nette\InvalidArgumentException('Class name must not be empty.');

		} elseif (isset(self::$builtinTypes[$lower])) {
			return $lower;

		} elseif ($lower === 'self') {
			return $rc->getName();

		} elseif ($name[0] === '\\') { // fully qualified name
			return ltrim($name, '\\');
		}

		$uses = self::getUseStatements($rc);
		$parts = explode('\\', $name, 2);
		if (isset($uses[$parts[0]])) {
			$parts[0] = $uses[$parts[0]];
			return implode('\\', $parts);

		} elseif ($rc->inNamespace()) {
			return $rc->getNamespaceName() . '\\' . $name;

		} else {
			return $name;
		}
	}


	/**
	 * @return array of [alias => class]
	 */
	public static function getUseStatements(\ReflectionClass $class)
	{
		static $cache = [];
		if (!isset($cache[$name = $class->getName()])) {
			if ($class->isInternal()) {
				$cache[$name] = [];
			} else {
				$code = file_get_contents($class->getFileName());
				$cache = self::parseUseStatements($code, $name) + $cache;
			}
		}
		return $cache[$name];
	}


	/**
	 * Parses PHP code.
	 * @param  string
	 * @return array of [class => [alias => class, ...]]
	 */
	private static function parseUseStatements($code, $forClass = NULL)
	{
		$tokens = token_get_all($code);
		$namespace = $class = $classLevel = $level = NULL;
		$res = $uses = [];

		while ($token = current($tokens)) {
			next($tokens);
			switch (is_array($token) ? $token[0] : $token) {
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
						$res[$class] = $uses;
						if ($class === $forClass) {
							return $res;
						}
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

}
