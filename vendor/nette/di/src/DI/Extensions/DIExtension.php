<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\DI\Extensions;

use Nette;


/**
 * DI extension.
 */
class DIExtension extends Nette\DI\CompilerExtension
{
	public $defaults = [
		'debugger' => TRUE,
		'accessors' => FALSE,
		'excluded' => [],
		'parentClass' => NULL,
	];

	/** @var bool */
	private $debugMode;

	/** @var int */
	private $time;


	public function __construct($debugMode = FALSE)
	{
		$this->debugMode = $debugMode;
		$this->time = microtime(TRUE);
	}


	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();
		$builder->addExcludedClasses($config['excluded']);
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		if ($this->config['parentClass']) {
			$class->setExtends($this->config['parentClass']);
		}

		$initialize = $class->getMethod('initialize');
		$builder = $this->getContainerBuilder();

		if ($this->debugMode && $this->config['debugger']) {
			Nette\Bridges\DITracy\ContainerPanel::$compilationTime = $this->time;
			$initialize->addBody($builder->formatPhp('?;', [
				new Nette\DI\Statement('@Tracy\Bar::addPanel', [new Nette\DI\Statement(Nette\Bridges\DITracy\ContainerPanel::class)]),
			]));
		}

		foreach (array_filter($builder->findByTag('run')) as $name => $on) {
			$initialize->addBody('$this->getService(?);', [$name]);
		}
	}

}
