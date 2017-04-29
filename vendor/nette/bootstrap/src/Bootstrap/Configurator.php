<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette;

use Nette;
use Nette\DI;
use Tracy;


/**
 * Initial system DI container generator.
 */
class Configurator
{
	use SmartObject;

	const AUTO = TRUE,
		NONE = FALSE;

	const COOKIE_SECRET = 'nette-debug';

	/** @var callable[]  function (Configurator $sender, DI\Compiler $compiler); Occurs after the compiler is created */
	public $onCompile;

	/** @var array */
	public $defaultExtensions = [
		'php' => Nette\DI\Extensions\PhpExtension::class,
		'constants' => Nette\DI\Extensions\ConstantsExtension::class,
		'extensions' => Nette\DI\Extensions\ExtensionsExtension::class,
		'application' => [Nette\Bridges\ApplicationDI\ApplicationExtension::class, ['%debugMode%', ['%appDir%'], '%tempDir%/cache']],
		'decorator' => Nette\DI\Extensions\DecoratorExtension::class,
		'cache' => [Nette\Bridges\CacheDI\CacheExtension::class, ['%tempDir%']],
		'database' => [Nette\Bridges\DatabaseDI\DatabaseExtension::class, ['%debugMode%']],
		'di' => [Nette\DI\Extensions\DIExtension::class, ['%debugMode%']],
		'forms' => Nette\Bridges\FormsDI\FormsExtension::class,
		'http' => [Nette\Bridges\HttpDI\HttpExtension::class, ['%consoleMode%']],
		'latte' => [Nette\Bridges\ApplicationDI\LatteExtension::class, ['%tempDir%/cache/latte', '%debugMode%']],
		'mail' => Nette\Bridges\MailDI\MailExtension::class,
		'routing' => [Nette\Bridges\ApplicationDI\RoutingExtension::class, ['%debugMode%']],
		'security' => [Nette\Bridges\SecurityDI\SecurityExtension::class, ['%debugMode%']],
		'session' => [Nette\Bridges\HttpDI\SessionExtension::class, ['%debugMode%', '%consoleMode%']],
		'tracy' => [Tracy\Bridges\Nette\TracyExtension::class, ['%debugMode%', '%consoleMode%']],
		'inject' => Nette\DI\Extensions\InjectExtension::class,
	];

	/** @var string[] of classes which shouldn't be autowired */
	public $autowireExcludedClasses = [
		'stdClass',
	];

	/** @var array */
	protected $parameters;

	/** @var array */
	protected $dynamicParameters = [];

	/** @var array */
	protected $services = [];

	/** @var array [file|array, section] */
	protected $files = [];


	public function __construct()
	{
		$this->parameters = $this->getDefaultParameters();
	}


	/**
	 * Set parameter %debugMode%.
	 * @param  bool|string|array
	 * @return static
	 */
	public function setDebugMode($value)
	{
		if (is_string($value) || is_array($value)) {
			$value = static::detectDebugMode($value);
		} elseif (!is_bool($value)) {
			throw new Nette\InvalidArgumentException(sprintf('Value must be either a string, array, or boolean, %s given.', gettype($value)));
		}
		$this->parameters['debugMode'] = $value;
		$this->parameters['productionMode'] = !$this->parameters['debugMode']; // compatibility
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isDebugMode()
	{
		return $this->parameters['debugMode'];
	}


	/**
	 * Sets path to temporary directory.
	 * @param  string
	 * @return static
	 */
	public function setTempDirectory($path)
	{
		$this->parameters['tempDir'] = $path;
		return $this;
	}


	/**
	 * Sets the default timezone.
	 * @param  string
	 * @return static
	 */
	public function setTimeZone($timezone)
	{
		date_default_timezone_set($timezone);
		@ini_set('date.timezone', $timezone); // @ - function may be disabled
		return $this;
	}


	/**
	 * Adds new parameters. The %params% will be expanded.
	 * @return static
	 */
	public function addParameters(array $params)
	{
		$this->parameters = DI\Config\Helpers::merge($params, $this->parameters);
		return $this;
	}


	/**
	 * Adds new dynamic parameters.
	 * @return static
	 */
	public function addDynamicParameters(array $params)
	{
		$this->dynamicParameters = $params + $this->dynamicParameters;
		return $this;
	}


	/**
	 * Add instances of services.
	 * @return static
	 */
	public function addServices(array $services)
	{
		$this->services = $services + $this->services;
		return $this;
	}


	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$last = end($trace);
		$debugMode = static::detectDebugMode();
		return [
			'appDir' => isset($trace[1]['file']) ? dirname($trace[1]['file']) : NULL,
			'wwwDir' => isset($last['file']) ? dirname($last['file']) : NULL,
			'debugMode' => $debugMode,
			'productionMode' => !$debugMode,
			'consoleMode' => PHP_SAPI === 'cli',
		];
	}


	/**
	 * @param  string  error log directory
	 * @param  string  administrator email
	 * @return void
	 */
	public function enableTracy($logDirectory = NULL, $email = NULL)
	{
		$this->enableDebugger($logDirectory, $email);
	}


	/**
	 * Alias for enableTracy()
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function enableDebugger($logDirectory = NULL, $email = NULL)
	{
		Tracy\Debugger::$strictMode = TRUE;
		Tracy\Debugger::enable(!$this->parameters['debugMode'], $logDirectory, $email);
		Nette\Bridges\Framework\TracyBridge::initialize();
	}


	/**
	 * @return Nette\Loaders\RobotLoader
	 * @throws Nette\NotSupportedException if RobotLoader is not available
	 */
	public function createRobotLoader()
	{
		if (!class_exists(Nette\Loaders\RobotLoader::class)) {
			throw new Nette\NotSupportedException('RobotLoader not found, do you have `nette/robot-loader` package installed?');
		}

		$loader = new Nette\Loaders\RobotLoader;
		$loader->setTempDirectory($this->getCacheDirectory() . '/Nette.RobotLoader');
		$loader->setAutoRefresh($this->parameters['debugMode']);
		return $loader;
	}


	/**
	 * Adds configuration file.
	 * @param  string|array
	 * @return static
	 */
	public function addConfig($file)
	{
		$section = func_num_args() > 1 ? func_get_arg(1) : NULL;
		if ($section !== NULL) {
			trigger_error('Sections in config file are deprecated.', E_USER_DEPRECATED);
		}
		$this->files[] = [$file, $section === self::AUTO ? ($this->parameters['debugMode'] ? 'development' : 'production') : $section];
		return $this;
	}


	/**
	 * Returns system DI container.
	 * @return DI\Container
	 */
	public function createContainer()
	{
		$class = $this->loadContainer();
		$container = new $class($this->dynamicParameters);
		foreach ($this->services as $name => $service) {
			$container->addService($name, $service);
		}
		$container->initialize();
		if (class_exists(Nette\Environment::class)) {
			Nette\Environment::setContext($container); // back compatibility
		}
		return $container;
	}


	/**
	 * Loads system DI container class and returns its name.
	 * @return string
	 */
	public function loadContainer()
	{
		$loader = new DI\ContainerLoader(
			$this->getCacheDirectory() . '/Nette.Configurator',
			$this->parameters['debugMode']
		);
		$class = $loader->load(
			[$this, 'generateContainer'],
			[$this->parameters, array_keys($this->dynamicParameters), $this->files, PHP_VERSION_ID - PHP_RELEASE_VERSION]
		);
		return $class;
	}


	/**
	 * @return string
	 * @internal
	 */
	public function generateContainer(DI\Compiler $compiler)
	{
		$compiler->addConfig(['parameters' => $this->parameters]);
		$compiler->setDynamicParameterNames(array_keys($this->dynamicParameters));

		$loader = $this->createLoader();
		$fileInfo = [];
		foreach ($this->files as $info) {
			if (is_scalar($info[0])) {
				$fileInfo[] = "// source: $info[0] $info[1]";
				$info[0] = $loader->load($info[0], $info[1]);
			}
			$compiler->addConfig($this->fixCompatibility($info[0]));
		}
		$compiler->addDependencies($loader->getDependencies());

		$builder = $compiler->getContainerBuilder();
		$builder->addExcludedClasses($this->autowireExcludedClasses);

		foreach ($this->defaultExtensions as $name => $extension) {
			list($class, $args) = is_string($extension) ? [$extension, []] : $extension;
			if (class_exists($class)) {
				$args = DI\Helpers::expand($args, $this->parameters, TRUE);
				$compiler->addExtension($name, (new \ReflectionClass($class))->newInstanceArgs($args));
			}
		}

		$this->onCompile($this, $compiler);

		$classes = $compiler->compile();
		return implode("\n", $fileInfo) . "\n\n" . $classes;
	}


	/**
	 * @return DI\Config\Loader
	 */
	protected function createLoader()
	{
		return new DI\Config\Loader;
	}


	/**
	 * @return string
	 */
	protected function getCacheDirectory()
	{
		if (empty($this->parameters['tempDir'])) {
			throw new Nette\InvalidStateException('Set path to temporary directory using setTempDirectory().');
		}
		$dir = $this->parameters['tempDir'] . '/cache';
		if (!is_dir($dir)) {
			@mkdir($dir); // @ - directory may already exist
		}
		return $dir;
	}


	/**
	 * Back compatibility with < v2.3
	 * @return array
	 */
	protected function fixCompatibility($config)
	{
		if (isset($config['nette']['security']['frames'])) {
			$config['nette']['http']['frames'] = $config['nette']['security']['frames'];
			unset($config['nette']['security']['frames']);
		}
		foreach (['application', 'cache', 'database', 'di' => 'container', 'forms', 'http',
			'latte', 'mail' => 'mailer', 'routing', 'security', 'session', 'tracy' => 'debugger'] as $new => $old) {
			if (isset($config['nette'][$old])) {
				$new = is_int($new) ? $old : $new;
				if (isset($config[$new])) {
					throw new Nette\DeprecatedException("You can use (deprecated) section 'nette.$old' or new section '$new', but not both of them.");
				} else {
					trigger_error("Configuration section 'nette.$old' is deprecated, use section '$new' (without 'nette')", E_USER_DEPRECATED);
				}
				$config[$new] = $config['nette'][$old];
				unset($config['nette'][$old]);
			}
		}
		if (isset($config['nette']['xhtml'])) {
			trigger_error("Configuration option 'nette.xhtml' is deprecated, use section 'latte.xhtml' instead.", E_USER_DEPRECATED);
			$config['latte']['xhtml'] = $config['nette']['xhtml'];
			unset($config['nette']['xhtml']);
		}

		if (empty($config['nette'])) {
			unset($config['nette']);
		}
		return $config;
	}


	/********************* tools ****************d*g**/


	/**
	 * Detects debug mode by IP addresses or computer names whitelist detection.
	 * @param  string|array
	 * @return bool
	 */
	public static function detectDebugMode($list = NULL)
	{
		$addr = isset($_SERVER['REMOTE_ADDR'])
			? $_SERVER['REMOTE_ADDR']
			: php_uname('n');
		$secret = isset($_COOKIE[self::COOKIE_SECRET]) && is_string($_COOKIE[self::COOKIE_SECRET])
			? $_COOKIE[self::COOKIE_SECRET]
			: NULL;
		$list = is_string($list)
			? preg_split('#[,\s]+#', $list)
			: (array) $list;
		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$list[] = '127.0.0.1';
			$list[] = '::1';
		}
		return in_array($addr, $list, TRUE) || in_array("$secret@$addr", $list, TRUE);
	}

}
