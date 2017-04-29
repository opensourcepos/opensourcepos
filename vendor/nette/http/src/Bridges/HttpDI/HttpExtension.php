<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\HttpDI;

use Nette;


/**
 * HTTP extension for Nette DI.
 */
class HttpExtension extends Nette\DI\CompilerExtension
{
	public $defaults = [
		'proxy' => [],
		'headers' => [
			'X-Powered-By' => 'Nette Framework',
			'Content-Type' => 'text/html; charset=utf-8',
		],
		'frames' => 'SAMEORIGIN', // X-Frame-Options
		'csp' => [], // Content-Security-Policy
	];

	/** @var bool */
	private $cliMode;


	public function __construct($cliMode = FALSE)
	{
		$this->cliMode = $cliMode;
	}


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$builder->addDefinition($this->prefix('requestFactory'))
			->setClass(Nette\Http\RequestFactory::class)
			->addSetup('setProxy', [$config['proxy']]);

		$builder->addDefinition($this->prefix('request'))
			->setClass(Nette\Http\Request::class)
			->setFactory('@Nette\Http\RequestFactory::createHttpRequest');

		$builder->addDefinition($this->prefix('response'))
			->setClass(Nette\Http\Response::class);

		$builder->addDefinition($this->prefix('context'))
			->setClass(Nette\Http\Context::class)
			->addSetup('::trigger_error', ['Service http.context is deprecated.', E_USER_DEPRECATED]);

		if ($this->name === 'http') {
			$builder->addAlias('nette.httpRequestFactory', $this->prefix('requestFactory'));
			$builder->addAlias('nette.httpContext', $this->prefix('context'));
			$builder->addAlias('httpRequest', $this->prefix('request'));
			$builder->addAlias('httpResponse', $this->prefix('response'));
		}
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		if ($this->cliMode) {
			return;
		}

		$initialize = $class->getMethod('initialize');
		$config = $this->getConfig();
		$headers = $config['headers'];

		if (isset($config['frames']) && $config['frames'] !== TRUE) {
			$frames = $config['frames'];
			if ($frames === FALSE) {
				$frames = 'DENY';
			} elseif (preg_match('#^https?:#', $frames)) {
				$frames = "ALLOW-FROM $frames";
			}
			$headers['X-Frame-Options'] = $frames;
		}

		if (!empty($config['csp'])) {
			$value = '';
			foreach ($config['csp'] as $type => $policy) {
				$value .= $type;
				foreach ((array) $policy as $item) {
					$value .= preg_match('#^[a-z-]+\z#', $item) ? " '$item'" : " $item";
				}
				$value .= '; ';
			}
			if (strpos($value, "'nonce'")) {
				$value = Nette\DI\ContainerBuilder::literal(
					'str_replace(?, ? . base64_encode(Nette\Utils\Random::generate(16, "\x00-\xFF")), ?)',
					["'nonce", "'nonce-", $value]
				);
			}
			$headers['Content-Security-Policy'] = $value;
		}

		foreach ($headers as $key => $value) {
			if ($value != NULL) { // intentionally ==
				$initialize->addBody('$this->getService(?)->setHeader(?, ?);', [$this->prefix('response'), $key, $value]);
			}
		}
	}

}
