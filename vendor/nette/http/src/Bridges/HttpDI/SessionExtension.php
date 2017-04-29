<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\HttpDI;

use Nette;


/**
 * Session extension for Nette DI.
 */
class SessionExtension extends Nette\DI\CompilerExtension
{
	public $defaults = [
		'debugger' => FALSE,
		'autoStart' => 'smart', // true|false|smart
		'expiration' => NULL,
	];

	/** @var bool */
	private $debugMode;

	/** @var bool */
	private $cliMode;


	public function __construct($debugMode = FALSE, $cliMode = FALSE)
	{
		$this->debugMode = $debugMode;
		$this->cliMode = $cliMode;
	}


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig() + $this->defaults;
		$this->setConfig($config);

		$session = $builder->addDefinition($this->prefix('session'))
			->setClass(Nette\Http\Session::class);

		if ($config['expiration']) {
			$session->addSetup('setExpiration', [$config['expiration']]);
		}
		if (isset($config['cookieDomain']) && $config['cookieDomain'] === 'domain') {
			$config['cookieDomain'] = $builder::literal('$this->getByType(Nette\Http\IRequest::class)->getUrl()->getDomain(2)');
		}

		if ($this->debugMode && $config['debugger']) {
			$session->addSetup('@Tracy\Bar::addPanel', [
				new Nette\DI\Statement(Nette\Bridges\HttpTracy\SessionPanel::class)
			]);
		}

		unset($config['expiration'], $config['autoStart'], $config['debugger']);
		if (!empty($config)) {
			$session->addSetup('setOptions', [$config]);
		}

		if ($this->name === 'session') {
			$builder->addAlias('session', $this->prefix('session'));
		}
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		if ($this->cliMode) {
			return;
		}

		$initialize = $class->getMethod('initialize');
		$config = $this->getConfig();
		$name = $this->prefix('session');

		if ($config['autoStart'] === 'smart') {
			$initialize->addBody('$this->getService(?)->exists() && $this->getService(?)->start();', [$name, $name]);

		} elseif ($config['autoStart']) {
			$initialize->addBody('$this->getService(?)->start();', [$name]);
		}
	}

}
