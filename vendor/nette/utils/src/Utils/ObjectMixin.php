<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;
use Nette\MemberAccessException;


/**
 * Nette\Object behaviour mixin.
 */
class ObjectMixin
{
	use Nette\StaticClass;

	/** @var array [name => [type => callback]] used by extension methods */
	private static $extMethods = [];


	/********************* strictness ****************d*g**/


	/**
	 * @throws MemberAccessException
	 */
	public static function strictGet($class, $name)
	{
		$rc = new \ReflectionClass($class);
		$hint = self::getSuggestion(array_merge(
			array_filter($rc->getProperties(\ReflectionProperty::IS_PUBLIC), function ($p) { return !$p->isStatic(); }),
			self::parseFullDoc($rc, '~^[ \t*]*@property(?:-read)?[ \t]+(?:\S+[ \t]+)??\$(\w+)~m')
		), $name);
		throw new MemberAccessException("Cannot read an undeclared property $class::\$$name" . ($hint ? ", did you mean \$$hint?" : '.'));
	}


	/**
	 * @throws MemberAccessException
	 */
	public static function strictSet($class, $name)
	{
		$rc = new \ReflectionClass($class);
		$hint = self::getSuggestion(array_merge(
			array_filter($rc->getProperties(\ReflectionProperty::IS_PUBLIC), function ($p) { return !$p->isStatic(); }),
			self::parseFullDoc($rc, '~^[ \t*]*@property(?:-write)?[ \t]+(?:\S+[ \t]+)??\$(\w+)~m')
		), $name);
		throw new MemberAccessException("Cannot write to an undeclared property $class::\$$name" . ($hint ? ", did you mean \$$hint?" : '.'));
	}


	/**
	 * @throws MemberAccessException
	 */
	public static function strictCall($class, $method, $additionalMethods = [])
	{
		$hint = self::getSuggestion(array_merge(
			get_class_methods($class),
			self::parseFullDoc(new \ReflectionClass($class), '~^[ \t*]*@method[ \t]+(?:\S+[ \t]+)??(\w+)\(~m'),
			$additionalMethods
		), $method);

		if (method_exists($class, $method)) { // called parent::$method()
			$class = 'parent';
		}
		throw new MemberAccessException("Call to undefined method $class::$method()" . ($hint ? ", did you mean $hint()?" : '.'));
	}


	/**
	 * @throws MemberAccessException
	 */
	public static function strictStaticCall($class, $method)
	{
		$hint = self::getSuggestion(
			array_filter((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC), function ($m) { return $m->isStatic(); }),
			$method
		);
		throw new MemberAccessException("Call to undefined static method $class::$method()" . ($hint ? ", did you mean $hint()?" : '.'));
	}


	/********************* Nette\Object ****************d*g**/


	/**
	 * __call() implementation.
	 * @param  object
	 * @param  string
	 * @param  array
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public static function call($_this, $name, $args)
	{
		$class = get_class($_this);
		$isProp = self::hasProperty($class, $name);

		if ($name === '') {
			throw new MemberAccessException("Call to class '$class' method without name.");

		} elseif ($isProp === 'event') { // calling event handlers
			if (is_array($_this->$name) || $_this->$name instanceof \Traversable) {
				foreach ($_this->$name as $handler) {
					Callback::invokeArgs($handler, $args);
				}
			} elseif ($_this->$name !== NULL) {
				throw new Nette\UnexpectedValueException("Property $class::$$name must be array or NULL, " . gettype($_this->$name) . ' given.');
			}

		} elseif ($isProp && $_this->$name instanceof \Closure) { // closure in property
			return call_user_func_array($_this->$name, $args);

		} elseif (($methods = &self::getMethods($class)) && isset($methods[$name]) && is_array($methods[$name])) { // magic @methods
			list($op, $rp, $type) = $methods[$name];
			if (count($args) !== ($op === 'get' ? 0 : 1)) {
				throw new Nette\InvalidArgumentException("$class::$name() expects " . ($op === 'get' ? 'no' : '1') . ' argument, ' . count($args) . ' given.');

			} elseif ($type && $args && !self::checkType($args[0], $type)) {
				throw new Nette\InvalidArgumentException("Argument passed to $class::$name() must be $type, " . gettype($args[0]) . ' given.');
			}

			if ($op === 'get') {
				return $rp->getValue($_this);
			} elseif ($op === 'set') {
				$rp->setValue($_this, $args[0]);
			} elseif ($op === 'add') {
				$val = $rp->getValue($_this);
				$val[] = $args[0];
				$rp->setValue($_this, $val);
			}
			return $_this;

		} elseif ($cb = self::getExtensionMethod($class, $name)) { // extension methods
			return Callback::invoke($cb, $_this, ...$args);

		} else {
			self::strictCall($class, $name, array_keys(self::getExtensionMethods($class)));
		}
	}


	/**
	 * __callStatic() implementation.
	 * @param  string
	 * @param  string
	 * @param  array
	 * @return void
	 * @throws MemberAccessException
	 */
	public static function callStatic($class, $method, $args)
	{
		self::strictStaticCall($class, $method);
	}


	/**
	 * __get() implementation.
	 * @param  object
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public static function &get($_this, $name)
	{
		$class = get_class($_this);
		$uname = ucfirst($name);
		$methods = &self::getMethods($class);

		if ($name === '') {
			throw new MemberAccessException("Cannot read a class '$class' property without name.");

		} elseif (isset($methods[$m = 'get' . $uname]) || isset($methods[$m = 'is' . $uname])) { // property getter
			if ($methods[$m] === 0) {
				$methods[$m] = (new \ReflectionMethod($class, $m))->returnsReference();
			}
			if ($methods[$m] === TRUE) {
				return $_this->$m();
			} else {
				$val = $_this->$m();
				return $val;
			}

		} elseif (isset($methods[$name])) { // public method as closure getter
			if (preg_match('#^(is|get|has)([A-Z]|$)#', $name) && !(new \ReflectionMethod($class, $name))->getNumberOfRequiredParameters()) {
				trigger_error("Did you forget parentheses after $name" . self::getSource() . '?', E_USER_WARNING);
			}
			$val = Callback::closure($_this, $name);
			return $val;

		} elseif (isset($methods['set' . $uname])) { // property getter
			throw new MemberAccessException("Cannot read a write-only property $class::\$$name.");

		} else {
			self::strictGet($class, $name);
		}
	}


	/**
	 * __set() implementation.
	 * @param  object
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	public static function set($_this, $name, $value)
	{
		$class = get_class($_this);
		$uname = ucfirst($name);
		$methods = &self::getMethods($class);

		if ($name === '') {
			throw new MemberAccessException("Cannot write to a class '$class' property without name.");

		} elseif (self::hasProperty($class, $name)) { // unsetted property
			$_this->$name = $value;

		} elseif (isset($methods[$m = 'set' . $uname])) { // property setter
			$_this->$m($value);

		} elseif (isset($methods['get' . $uname]) || isset($methods['is' . $uname])) { // property setter
			throw new MemberAccessException("Cannot write to a read-only property $class::\$$name.");

		} else {
			self::strictSet($class, $name);
		}
	}


	/**
	 * __unset() implementation.
	 * @param  object
	 * @param  string  property name
	 * @return void
	 * @throws MemberAccessException
	 */
	public static function remove($_this, $name)
	{
		$class = get_class($_this);
		if (!self::hasProperty($class, $name)) {
			throw new MemberAccessException("Cannot unset the property $class::\$$name.");
		}
	}


	/**
	 * __isset() implementation.
	 * @param  object
	 * @param  string  property name
	 * @return bool
	 */
	public static function has($_this, $name)
	{
		$name = ucfirst($name);
		$methods = &self::getMethods(get_class($_this));
		return $name !== '' && (isset($methods['get' . $name]) || isset($methods['is' . $name]));
	}


	/********************* magic @properties ****************d*g**/


	/**
	 * Returns array of magic properties defined by annotation @property.
	 * @return array of [name => bit mask]
	 */
	public static function getMagicProperties($class)
	{
		static $cache;
		$props = &$cache[$class];
		if ($props !== NULL) {
			return $props;
		}

		$rc = new \ReflectionClass($class);
		preg_match_all(
			'~^  [ \t*]*  @property(|-read|-write)  [ \t]+  [^\s$]+  [ \t]+  \$  (\w+)  ()~mx',
			(string) $rc->getDocComment(), $matches, PREG_SET_ORDER
		);

		$props = [];
		foreach ($matches as list(, $type, $name)) {
			$uname = ucfirst($name);
			$write = $type !== '-read'
				&& $rc->hasMethod($nm = 'set' . $uname)
				&& ($rm = $rc->getMethod($nm)) && $rm->getName() === $nm && !$rm->isPrivate() && !$rm->isStatic();
			$read = $type !== '-write'
				&& ($rc->hasMethod($nm = 'get' . $uname) || $rc->hasMethod($nm = 'is' . $uname))
				&& ($rm = $rc->getMethod($nm)) && $rm->getName() === $nm && !$rm->isPrivate() && !$rm->isStatic();

			if ($read || $write) {
				$props[$name] = $read << 0 | ($nm[0] === 'g') << 1 | $rm->returnsReference() << 2 | $write << 3;
			}
		}

		foreach ($rc->getTraits() as $trait) {
			$props += self::getMagicProperties($trait->getName());
		}

		if ($parent = get_parent_class($class)) {
			$props += self::getMagicProperties($parent);
		}
		return $props;
	}


	/** @internal */
	public static function getMagicProperty($class, $name)
	{
		$props = self::getMagicProperties($class);
		return isset($props[$name]) ? $props[$name] : NULL;
	}


	/********************* magic @methods ****************d*g**/


	/**
	 * Returns array of magic methods defined by annotation @method.
	 * @return array
	 */
	public static function getMagicMethods($class)
	{
		$rc = new \ReflectionClass($class);
		preg_match_all('~^
			[ \t*]*  @method  [ \t]+
			(?: [^\s(]+  [ \t]+ )?
			(set|get|is|add)  ([A-Z]\w*)
			(?: ([ \t]* \()  [ \t]* ([^)$\s]*)  )?
		()~mx', (string) $rc->getDocComment(), $matches, PREG_SET_ORDER);

		$methods = [];
		foreach ($matches as list(, $op, $prop, $bracket, $type)) {
			if ($bracket !== '(') {
				trigger_error("Bracket must be immediately after @method $op$prop() in class $class.", E_USER_WARNING);
			}
			$name = $op . $prop;
			$prop = strtolower($prop[0]) . substr($prop, 1) . ($op === 'add' ? 's' : '');
			if ($rc->hasProperty($prop) && ($rp = $rc->getProperty($prop)) && !$rp->isStatic()) {
				$rp->setAccessible(TRUE);
				if ($op === 'get' || $op === 'is') {
					$type = NULL;
					$op = 'get';
				} elseif (!$type && preg_match('#@var[ \t]+(\S+)' . ($op === 'add' ? '\[\]#' : '#'), (string) $rp->getDocComment(), $m)) {
					$type = $m[1];
				}
				if ($rc->inNamespace() && preg_match('#^[A-Z]\w+(\[|\||\z)#', (string) $type)) {
					$type = $rc->getNamespaceName() . '\\' . $type;
				}
				$methods[$name] = [$op, $rp, $type];
			}
		}
		return $methods;
	}


	/**
	 * Finds whether a variable is of expected type and do non-data-loss conversion.
	 * @return bool
	 * @internal
	 */
	public static function checkType(&$val, $type)
	{
		if (strpos($type, '|') !== FALSE) {
			$found = NULL;
			foreach (explode('|', $type) as $type) {
				$tmp = $val;
				if (self::checkType($tmp, $type)) {
					if ($val === $tmp) {
						return TRUE;
					}
					$found[] = $tmp;
				}
			}
			if ($found) {
				$val = $found[0];
				return TRUE;
			}
			return FALSE;

		} elseif (substr($type, -2) === '[]') {
			if (!is_array($val)) {
				return FALSE;
			}
			$type = substr($type, 0, -2);
			$res = [];
			foreach ($val as $k => $v) {
				if (!self::checkType($v, $type)) {
					return FALSE;
				}
				$res[$k] = $v;
			}
			$val = $res;
			return TRUE;
		}

		switch (strtolower($type)) {
			case NULL:
			case 'mixed':
				return TRUE;
			case 'bool':
			case 'boolean':
				return ($val === NULL || is_scalar($val)) && settype($val, 'bool');
			case 'string':
				return ($val === NULL || is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) && settype($val, 'string');
			case 'int':
			case 'integer':
				return ($val === NULL || is_bool($val) || is_numeric($val)) && ((float) (int) $val === (float) $val) && settype($val, 'int');
			case 'float':
				return ($val === NULL || is_bool($val) || is_numeric($val)) && settype($val, 'float');
			case 'scalar':
			case 'array':
			case 'object':
			case 'callable':
			case 'resource':
			case 'null':
				return call_user_func("is_$type", $val);
			default:
				return $val instanceof $type;
		}
	}


	/********************* extension methods ****************d*g**/


	/**
	 * Adds a method to class.
	 * @param  string
	 * @param  string
	 * @param  mixed   callable
	 * @return void
	 */
	public static function setExtensionMethod($class, $name, $callback)
	{
		$name = strtolower($name);
		self::$extMethods[$name][$class] = Callback::check($callback);
		self::$extMethods[$name][''] = NULL;
	}


	/**
	 * Returns extension method.
	 * @param  string
	 * @param  string
	 * @return mixed
	 */
	public static function getExtensionMethod($class, $name)
	{
		$list = &self::$extMethods[strtolower($name)];
		$cache = &$list[''][$class];
		if (isset($cache)) {
			return $cache;
		}

		foreach ([$class] + class_parents($class) + class_implements($class) as $cl) {
			if (isset($list[$cl])) {
				return $cache = $list[$cl];
			}
		}
		return $cache = FALSE;
	}


	/**
	 * Returns extension methods.
	 * @param  string
	 * @return array
	 */
	public static function getExtensionMethods($class)
	{
		$res = [];
		foreach (array_keys(self::$extMethods) as $name) {
			if ($cb = self::getExtensionMethod($class, $name)) {
				$res[$name] = $cb;
			}
		}
		return $res;
	}


	/********************* utilities ****************d*g**/


	/**
	 * Finds the best suggestion (for 8-bit encoding).
	 * @return string|NULL
	 * @internal
	 */
	public static function getSuggestion(array $possibilities, $value)
	{
		$norm = preg_replace($re = '#^(get|set|has|is|add)(?=[A-Z])#', '', $value);
		$best = NULL;
		$min = (strlen($value) / 4 + 1) * 10 + .1;
		foreach (array_unique($possibilities, SORT_REGULAR) as $item) {
			$item = $item instanceof \Reflector ? $item->getName() : $item;
			if ($item !== $value && (
				($len = levenshtein($item, $value, 10, 11, 10)) < $min
				|| ($len = levenshtein(preg_replace($re, '', $item), $norm, 10, 11, 10) + 20) < $min
			)) {
				$min = $len;
				$best = $item;
			}
		}
		return $best;
	}


	private static function parseFullDoc(\ReflectionClass $rc, $pattern)
	{
		do {
			$doc[] = $rc->getDocComment();
			$traits = $rc->getTraits();
			while ($trait = array_pop($traits)) {
				$doc[] = $trait->getDocComment();
				$traits += $trait->getTraits();
			}
		} while ($rc = $rc->getParentClass());
		return preg_match_all($pattern, implode($doc), $m) ? $m[1] : [];
	}


	/**
	 * Checks if the public non-static property exists.
	 * @return bool|'event'
	 * @internal
	 */
	public static function hasProperty($class, $name)
	{
		static $cache;
		$prop = &$cache[$class][$name];
		if ($prop === NULL) {
			$prop = FALSE;
			try {
				$rp = new \ReflectionProperty($class, $name);
				if ($rp->isPublic() && !$rp->isStatic()) {
					$prop = $name >= 'onA' && $name < 'on_' ? 'event' : TRUE;
				}
			} catch (\ReflectionException $e) {
			}
		}
		return $prop;
	}


	/**
	 * Returns array of public (static, non-static and magic) methods.
	 * @return array
	 * @internal
	 */
	public static function &getMethods($class)
	{
		static $cache;
		if (!isset($cache[$class])) {
			$cache[$class] = array_fill_keys(get_class_methods($class), 0) + self::getMagicMethods($class);
			if ($parent = get_parent_class($class)) {
				$cache[$class] += self::getMethods($parent);
			}
		}
		return $cache[$class];
	}


	/** @internal */
	public static function getSource()
	{
		foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $item) {
			if (isset($item['file']) && dirname($item['file']) !== __DIR__) {
				return " in $item[file]:$item[line]";
			}
		}
	}

}
