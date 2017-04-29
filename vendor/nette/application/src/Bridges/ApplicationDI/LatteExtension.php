<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationDI;

use Nette;
use Latte;


/**
 * Latte extension for Nette DI.
 */
class LatteExtension extends Nette\DI\CompilerExtension
{
	public $defaults = [
		'xhtml' => FALSE,
		'macros' => [],
		'templateClass' => NULL,
	];

	/** @var bool */
	private $debugMode;

	/** @var string */
	private $tempDir;


	public function __construct($tempDir, $debugMode = FALSE)
	{
		$this->tempDir = $tempDir;
		$this->debugMode = $debugMode;
	}


	public function loadConfiguration()
	{
		if (!class_exists(Latte\Engine::class)) {
			return;
		}

		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('latteFactory'))
			->setClass(Latte\Engine::class)
			->addSetup('setTempDirectory', [$this->tempDir])
			->addSetup('setAutoRefresh', [$this->debugMode])
			->addSetup('setContentType', [$config['xhtml'] ? Latte\Compiler::CONTENT_XHTML : Latte\Compiler::CONTENT_HTML])
			->addSetup('Nette\Utils\Html::$xhtml = ?', [(bool) $config['xhtml']])
			->setImplement(Nette\Bridges\ApplicationLatte\ILatteFactory::class);

		$builder->addDefinition($this->prefix('templateFactory'))
			->setClass(Nette\Application\UI\ITemplateFactory::class)
			->setFactory(Nette\Bridges\ApplicationLatte\TemplateFactory::class)
			->setArguments(['templateClass' => $config['templateClass']]);

		foreach ($config['macros'] as $macro) {
			$this->addMacro($macro);
		}

		if ($this->name === 'latte') {
			$builder->addAlias('nette.latteFactory', $this->prefix('latteFactory'));
			$builder->addAlias('nette.templateFactory', $this->prefix('templateFactory'));
		}
	}


	/**
	 * @param  string
	 * @return void
	 */
	public function addMacro($macro)
	{
		$builder = $this->getContainerBuilder();
		$definition = $builder->getDefinition($this->prefix('latteFactory'));

		if (isset($macro[0]) && $macro[0] === '@') {
			if (strpos($macro, '::') === FALSE) {
				$method = 'install';
			} else {
				list($macro, $method) = explode('::', $macro);
			}
			$definition->addSetup('?->onCompile[] = function ($engine) { ?->' . $method . '($engine->getCompiler()); }', ['@self', $macro]);

		} else {
			if (strpos($macro, '::') === FALSE && class_exists($macro)) {
				$macro .= '::install';
			}
			$definition->addSetup('?->onCompile[] = function ($engine) { ' . $macro . '($engine->getCompiler()); }', ['@self']);
		}
	}

}
