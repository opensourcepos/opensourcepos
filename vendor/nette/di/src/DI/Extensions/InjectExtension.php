<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\DI\Extensions;

use Nette;
use Nette\DI;
use Nette\Utils\Reflection;


/**
 * Calls inject methods and fills @inject properties.
 */
class InjectExtension extends DI\CompilerExtension
{
	const TAG_INJECT = 'inject';


	public function beforeCompile()
	{
		foreach ($this->getContainerBuilder()->getDefinitions() as $def) {
			if ($def->getTag(self::TAG_INJECT) && $def->getClass()) {
				$this->updateDefinition($def);
			}
		}
	}


	private function updateDefinition(DI\ServiceDefinition $def)
	{
		$class = $def->getClass();
		$setups = $def->getSetup();

		foreach (self::getInjectProperties($class) as $property => $type) {
			$builder = $this->getContainerBuilder();
			$inject = new DI\Statement('$' . $property, ['@\\' . ltrim($type, '\\')]);
			foreach ($setups as $key => $setup) {
				if ($setup->getEntity() === $inject->getEntity()) {
					$inject = $setup;
					$builder = NULL;
					unset($setups[$key]);
				}
			}
			self::checkType($class, $property, $type, $builder);
			array_unshift($setups, $inject);
		}

		foreach (array_reverse(self::getInjectMethods($def->getClass())) as $method) {
			$inject = new DI\Statement($method);
			foreach ($setups as $key => $setup) {
				if ($setup->getEntity() === $inject->getEntity()) {
					$inject = $setup;
					unset($setups[$key]);
				}
			}
			array_unshift($setups, $inject);
		}

		$def->setSetup($setups);
	}


	/**
	 * Generates list of inject methods.
	 * @return array
	 * @internal
	 */
	public static function getInjectMethods($class)
	{
		$res = [];
		foreach (get_class_methods($class) as $name) {
			if (substr($name, 0, 6) === 'inject') {
				$res[$name] = (new \ReflectionMethod($class, $name))->getDeclaringClass()->getName();
			}
		}
		uksort($res, function ($a, $b) use ($res) {
			return $res[$a] === $res[$b]
				? strcmp($a, $b)
				: (is_a($res[$a], $res[$b], TRUE) ? 1 : -1);
		});
		return array_keys($res);
	}


	/**
	 * Generates list of properties with annotation @inject.
	 * @return array
	 * @internal
	 */
	public static function getInjectProperties($class)
	{
		$res = [];
		foreach (get_class_vars($class) as $name => $foo) {
			$rp = new \ReflectionProperty($class, $name);
			if (DI\Helpers::parseAnnotation($rp, 'inject') !== NULL) {
				if ($type = DI\Helpers::parseAnnotation($rp, 'var')) {
					$type = Reflection::expandClassName($type, Reflection::getPropertyDeclaringClass($rp));
				}
				$res[$name] = $type;
			}
		}
		ksort($res);
		return $res;
	}


	/**
	 * Calls all methods starting with with "inject" using autowiring.
	 * @return void
	 */
	public static function callInjects(DI\Container $container, $service)
	{
		if (!is_object($service)) {
			throw new Nette\InvalidArgumentException(sprintf('Service must be object, %s given.', gettype($service)));
		}

		foreach (self::getInjectMethods($service) as $method) {
			$container->callMethod([$service, $method]);
		}

		foreach (self::getInjectProperties(get_class($service)) as $property => $type) {
			self::checkType($service, $property, $type, $container);
			$service->$property = $container->getByType($type);
		}
	}


	/** @internal */
	private static function checkType($class, $name, $type, $container = NULL)
	{
		$propName = Reflection::toString(new \ReflectionProperty($class, $name));
		if (!$type) {
			throw new Nette\InvalidStateException("Property $propName has no @var annotation.");
		} elseif (!class_exists($type) && !interface_exists($type)) {
			throw new Nette\InvalidStateException("Class or interface '$type' used in @var annotation at $propName not found. Check annotation and 'use' statements.");
		} elseif ($container && !$container->getByType($type, FALSE)) {
			throw new Nette\InvalidStateException("Service of type $type used in @var annotation at $propName not found. Did you register it in configuration file?");
		}
	}

}
