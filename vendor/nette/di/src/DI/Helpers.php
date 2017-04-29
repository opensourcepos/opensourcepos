<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\DI;

use Nette;
use Nette\PhpGenerator\PhpLiteral;
use Nette\Utils\Reflection;


/**
 * The DI helpers.
 * @internal
 */
class Helpers
{
	use Nette\StaticClass;

	/**
	 * Expands %placeholders%.
	 * @param  mixed
	 * @param  array
	 * @param  bool|array
	 * @return mixed
	 * @throws Nette\InvalidArgumentException
	 */
	public static function expand($var, array $params, $recursive = FALSE)
	{
		if (is_array($var)) {
			$res = [];
			foreach ($var as $key => $val) {
				$res[$key] = self::expand($val, $params, $recursive);
			}
			return $res;

		} elseif ($var instanceof Statement) {
			return new Statement(self::expand($var->getEntity(), $params, $recursive), self::expand($var->arguments, $params, $recursive));

		} elseif (!is_string($var)) {
			return $var;
		}

		$parts = preg_split('#%([\w.-]*)%#i', $var, -1, PREG_SPLIT_DELIM_CAPTURE);
		$res = [];
		$php = FALSE;
		foreach ($parts as $n => $part) {
			if ($n % 2 === 0) {
				$res[] = $part;

			} elseif ($part === '') {
				$res[] = '%';

			} elseif (isset($recursive[$part])) {
				throw new Nette\InvalidArgumentException(sprintf('Circular reference detected for variables: %s.', implode(', ', array_keys($recursive))));

			} else {
				try {
					$val = Nette\Utils\Arrays::get($params, explode('.', $part));
				} catch (Nette\InvalidArgumentException $e) {
					throw new Nette\InvalidArgumentException("Missing parameter '$part'.", 0, $e);
				}
				if ($recursive) {
					$val = self::expand($val, $params, (is_array($recursive) ? $recursive : []) + [$part => 1]);
				}
				if (strlen($part) + 2 === strlen($var)) {
					return $val;
				}
				if ($val instanceof PhpLiteral) {
					$php = TRUE;
				} elseif (!is_scalar($val)) {
					throw new Nette\InvalidArgumentException("Unable to concatenate non-scalar parameter '$part' into '$var'.");
				}
				$res[] = $val;
			}
		}
		if ($php) {
			$res = array_filter($res, function ($val) { return $val !== ''; });
			$res = array_map(function ($val) { return $val instanceof PhpLiteral ? "($val)" : var_export((string) $val, TRUE); }, $res);
			return new PhpLiteral(implode(' . ', $res));
		}
		return implode('', $res);
	}


	/**
	 * Generates list of arguments using autowiring.
	 * @return array
	 */
	public static function autowireArguments(\ReflectionFunctionAbstract $method, array $arguments, $container)
	{
		$optCount = 0;
		$num = -1;
		$res = [];
		$methodName = Reflection::toString($method) . '()';

		foreach ($method->getParameters() as $num => $parameter) {
			if (!$parameter->isVariadic() && array_key_exists($parameter->getName(), $arguments)) {
				$res[$num] = $arguments[$parameter->getName()];
				unset($arguments[$parameter->getName()], $arguments[$num]);
				$optCount = 0;

			} elseif (array_key_exists($num, $arguments)) {
				$res[$num] = $arguments[$num];
				unset($arguments[$num]);
				$optCount = 0;

			} elseif (($class = Reflection::getParameterType($parameter)) && !Reflection::isBuiltinType($class)) {
				$res[$num] = $container->getByType($class, FALSE);
				if ($res[$num] === NULL) {
					if ($parameter->allowsNull()) {
						$optCount++;
					} elseif (class_exists($class) || interface_exists($class)) {
						if ($class !== ($hint = (new \ReflectionClass($class))->getName())) {
							throw new ServiceCreationException("Service of type $class needed by $methodName not found, did you mean $hint?");
						}
						throw new ServiceCreationException("Service of type $class needed by $methodName not found. Did you register it in configuration file?");
					} else {
						throw new ServiceCreationException("Class $class needed by $methodName not found. Check type hint and 'use' statements.");
					}
				} else {
					if ($container instanceof ContainerBuilder) {
						$res[$num] = '@' . $res[$num];
					}
					$optCount = 0;
				}

			} elseif (($class && $parameter->allowsNull()) || $parameter->isOptional() || $parameter->isDefaultValueAvailable()) {
				// !optional + defaultAvailable = func($a = NULL, $b) since 5.4.7
				// optional + !defaultAvailable = i.e. Exception::__construct, mysqli::mysqli, ...
				$res[$num] = $parameter->isDefaultValueAvailable() ? Reflection::getParameterDefaultValue($parameter) : NULL;
				$optCount++;

			} else {
				throw new ServiceCreationException("Parameter \${$parameter->getName()} in $methodName has no class type hint or default value, so its value must be specified.");
			}
		}

		// extra parameters
		while (array_key_exists(++$num, $arguments)) {
			$res[$num] = $arguments[$num];
			unset($arguments[$num]);
			$optCount = 0;
		}
		if ($arguments) {
			throw new ServiceCreationException("Unable to pass specified arguments to $methodName.");
		}

		return $optCount ? array_slice($res, 0, -$optCount) : $res;
	}


	/**
	 * Removes ... and process constants recursively.
	 * @return array
	 */
	public static function filterArguments(array $args)
	{
		foreach ($args as $k => $v) {
			if ($v === '...') {
				unset($args[$k]);
			} elseif (is_string($v) && preg_match('#^[\w\\\\]*::[A-Z][A-Z0-9_]*\z#', $v, $m)) {
				$args[$k] = constant(ltrim($v, ':'));
			} elseif (is_array($v)) {
				$args[$k] = self::filterArguments($v);
			} elseif ($v instanceof Statement) {
				$tmp = self::filterArguments([$v->getEntity()]);
				$args[$k] = new Statement($tmp[0], self::filterArguments($v->arguments));
			}
		}
		return $args;
	}


	/**
	 * Replaces @extension with real extension name in service definition.
	 * @param  mixed
	 * @param  string
	 * @return mixed
	 */
	public static function prefixServiceName($config, $namespace)
	{
		if (is_string($config)) {
			if (strncmp($config, '@extension.', 10) === 0) {
				$config = '@' . $namespace . '.' . substr($config, 11);
			}
		} elseif ($config instanceof Statement) {
			return new Statement(
				self::prefixServiceName($config->getEntity(), $namespace),
				self::prefixServiceName($config->arguments, $namespace)
			);
		} elseif (is_array($config)) {
			foreach ($config as &$val) {
				$val = self::prefixServiceName($val, $namespace);
			}
		}
		return $config;
	}


	/**
	 * Returns an annotation value.
	 * @return string|NULL
	 */
	public static function parseAnnotation(\Reflector $ref, $name)
	{
		if (!Reflection::areCommentsAvailable()) {
			throw new Nette\InvalidStateException('You have to enable phpDoc comments in opcode cache.');
		}
		$name = preg_quote($name, '#');
		if ($ref->getDocComment() && preg_match("#[\\s*]@$name(?:\\s++([^@]\\S*)?|$)#", trim($ref->getDocComment(), '/*'), $m)) {
			return isset($m[1]) ? $m[1] : '';
		}
	}


	/**
	 * @return string|NULL
	 */
	public static function getReturnType(\ReflectionFunctionAbstract $func)
	{
		if ($type = Reflection::getReturnType($func)) {
			return $type;
		} elseif ($type = preg_replace('#[|\s].*#', '', (string) self::parseAnnotation($func, 'return'))) {
			if ($type === 'object' || $type === 'mixed') {
				return NULL;
			} elseif ($func instanceof \ReflectionMethod) {
				return $type === 'static' || $type === '$this'
					? $func->getDeclaringClass()->getName()
					: Reflection::expandClassName($type, $func->getDeclaringClass());
			} else {
				return ltrim($type, '\\');
			}
		}
	}

}
