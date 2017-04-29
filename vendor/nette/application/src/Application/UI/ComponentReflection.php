<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;
use Nette\Application\BadRequestException;
use Nette\Reflection\ClassType;


/**
 * Helpers for Presenter & Component.
 * @property-read string $name
 * @property-read string $fileName
 * @internal
 */
class ComponentReflection extends \ReflectionClass
{
	use Nette\SmartObject;

	/** @var array getPersistentParams cache */
	private static $ppCache = [];

	/** @var array getPersistentComponents cache */
	private static $pcCache = [];

	/** @var array isMethodCallable cache */
	private static $mcCache = [];


	/**
	 * @param  string|NULL
	 * @return array of persistent parameters.
	 */
	public function getPersistentParams($class = NULL)
	{
		$class = $class === NULL ? $this->getName() : $class;
		$params = & self::$ppCache[$class];
		if ($params !== NULL) {
			return $params;
		}
		$params = [];
		if (is_subclass_of($class, Component::class)) {
			$defaults = get_class_vars($class);
			foreach ($class::getPersistentParams() as $name => $default) {
				if (is_int($name)) {
					$name = $default;
					$default = $defaults[$name];
				}
				$params[$name] = [
					'def' => $default,
					'since' => $class,
				];
			}
			foreach ($this->getPersistentParams(get_parent_class($class)) as $name => $param) {
				if (isset($params[$name])) {
					$params[$name]['since'] = $param['since'];
					continue;
				}

				$params[$name] = $param;
			}
		}
		return $params;
	}


	/**
	 * @param  string|NULL
	 * @return array of persistent components.
	 */
	public function getPersistentComponents($class = NULL)
	{
		$class = $class === NULL ? $this->getName() : $class;
		$components = & self::$pcCache[$class];
		if ($components !== NULL) {
			return $components;
		}
		$components = [];
		if (is_subclass_of($class, Presenter::class)) {
			foreach ($class::getPersistentComponents() as $name => $meta) {
				if (is_string($meta)) {
					$name = $meta;
				}
				$components[$name] = ['since' => $class];
			}
			$components = $this->getPersistentComponents(get_parent_class($class)) + $components;
		}
		return $components;
	}


	/**
	 * Is a method callable? It means class is instantiable and method has
	 * public visibility, is non-static and non-abstract.
	 * @param  string  method name
	 * @return bool
	 */
	public function hasCallableMethod($method)
	{
		$class = $this->getName();
		$cache = & self::$mcCache[strtolower($class . ':' . $method)];
		if ($cache === NULL) {
			try {
				$cache = FALSE;
				$rm = new \ReflectionMethod($class, $method);
				$cache = $this->isInstantiable() && $rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic();
			} catch (\ReflectionException $e) {
			}
		}
		return $cache;
	}


	/**
	 * @return array
	 */
	public static function combineArgs(\ReflectionFunctionAbstract $method, $args)
	{
		$res = [];
		foreach ($method->getParameters() as $i => $param) {
			$name = $param->getName();
			list($type, $isClass) = self::getParameterType($param);
			if (isset($args[$name])) {
				$res[$i] = $args[$name];
				if (!self::convertType($res[$i], $type, $isClass)) {
					throw new BadRequestException(sprintf(
						'Argument $%s passed to %s() must be %s, %s given.',
						$name,
						($method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName(),
						$type === 'NULL' ? 'scalar' : $type,
						is_object($args[$name]) ? get_class($args[$name]) : gettype($args[$name])
					));
				}
			} elseif ($param->isDefaultValueAvailable()) {
				$res[$i] = $param->getDefaultValue();
			} elseif ($type === 'NULL' || $param->allowsNull()) {
				$res[$i] = NULL;
			} elseif ($type === 'array') {
				$res[$i] = [];
			} else {
				throw new BadRequestException(sprintf(
					'Missing parameter $%s required by %s()',
					$name,
					($method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName()
				));
			}
		}
		return $res;
	}


	/**
	 * Non data-loss type conversion.
	 * @param  mixed
	 * @param  string
	 * @return bool
	 */
	public static function convertType(&$val, $type, $isClass = FALSE)
	{
		if ($isClass) {
			return $val instanceof $type;

		} elseif ($type === 'callable') {
			return FALSE;

		} elseif ($type === 'NULL') { // means 'not array'
			return !is_array($val);

		} elseif ($type === 'array') {
			return is_array($val);

		} elseif (!is_scalar($val)) { // array, resource, NULL, etc.
			return FALSE;

		} else {
			$old = $tmp = ($val === FALSE ? '0' : (string) $val);
			settype($tmp, $type);
			if ($old !== ($tmp === FALSE ? '0' : (string) $tmp)) {
				return FALSE; // data-loss occurs
			}
			$val = $tmp;
		}
		return TRUE;
	}


	/**
	 * Returns an annotation value.
	 * @return array|FALSE
	 */
	public static function parseAnnotation(\Reflector $ref, $name)
	{
		if (!preg_match_all('#[\\s*]@' . preg_quote($name, '#') . '(?:\(\\s*([^)]*)\\s*\)|\\s|$)#', $ref->getDocComment(), $m)) {
			return FALSE;
		}
		static $tokens = ['true' => TRUE, 'false' => FALSE, 'null' => NULL];
		$res = [];
		foreach ($m[1] as $s) {
			foreach (preg_split('#\s*,\s*#', $s, -1, PREG_SPLIT_NO_EMPTY) ?: ['true'] as $item) {
				$res[] = array_key_exists($tmp = strtolower($item), $tokens) ? $tokens[$tmp] : $item;
			}
		}
		return $res;
	}


	/**
	 * @return [string, bool]
	 */
	public static function getParameterType(\ReflectionParameter $param)
	{
		$def = gettype($param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL);
		if (PHP_VERSION_ID >= 70000) {
			return $param->hasType()
				? [PHP_VERSION_ID >= 70100 ? $param->getType()->getName() : (string) $param->getType(), !$param->getType()->isBuiltin()]
				: [$def, FALSE];
		} elseif ($param->isArray() || $param->isCallable()) {
			return [$param->isArray() ? 'array' : 'callable', FALSE];
		} else {
			try {
				return ($ref = $param->getClass()) ? [$ref->getName(), TRUE] : [$def, FALSE];
			} catch (\ReflectionException $e) {
				if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
					throw new \LogicException(sprintf(
						"Class %s not found. Check type hint of parameter $%s in %s() or 'use' statements.",
						$m[1],
						$param->getName(),
						$param->getDeclaringFunction()->getDeclaringClass()->getName() . '::' . $param->getDeclaringFunction()->getName()
					));
				}
				throw $e;
			}
		}
	}


	/********************* compatiblity with Nette\Reflection ****************d*g**/


	/**
	 * Has class specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		return (bool) self::parseAnnotation($this, $name);
	}


	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return mixed
	 */
	public function getAnnotation($name)
	{
		$res = self::parseAnnotation($this, $name);
		return $res ? end($res) : NULL;
	}


	/**
	 * @return MethodReflection
	 */
	public function getMethod($name)
	{
		return new MethodReflection($this->getName(), $name);
	}


	/**
	 * @return MethodReflection[]
	 */
	public function getMethods($filter = -1)
	{
		foreach ($res = parent::getMethods($filter) as $key => $val) {
			$res[$key] = new MethodReflection($this->getName(), $val->getName());
		}
		return $res;
	}


	public function __toString()
	{
		trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
		return $this->getName();
	}


	public function __get($name)
	{
		trigger_error("getReflection()->$name is deprecated.", E_USER_DEPRECATED);
		return (new ClassType($this->getName()))->$name;
	}


	public function __call($name, $args)
	{
		if (method_exists(ClassType::class, $name)) {
			trigger_error("getReflection()->$name() is deprecated, use Nette\\Reflection\\ClassType::from(\$presenter)->$name()", E_USER_DEPRECATED);
			return call_user_func_array([new ClassType($this->getName()), $name], $args);
		}
		Nette\Utils\ObjectMixin::strictCall(get_class($this), $name);
	}

}
