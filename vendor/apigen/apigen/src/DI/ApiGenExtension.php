<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\DI;

use Nette\DI\CompilerExtension;


class ApiGenExtension extends CompilerExtension
{

	const TAG_CONSOLE_COMMAND = 'console.command';
	const TAG_LATTE_FILTER = 'latte.filter';
	const TAG_TEMPLATE_GENERATOR = 'template.generator';


	public function loadConfiguration()
	{
		$this->loadServicesFromConfig();
		$this->setupTemplating();
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$builder->prepareClassList();
		$this->setupConsole();
		$this->setupTemplatingFilters();
		$this->setupGeneratorQueue();
	}


	private function loadServicesFromConfig()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->loadFromFile(__DIR__ . '/apigen.services.neon');
		$this->compiler->parseServices($builder, $config);
	}


	private function setupTemplating()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('latteFactory'))
			->setClass('Latte\Engine')
			->addSetup('setTempDirectory', [$builder->expand('%tempDir%/cache/latte')]);
	}


	private function setupConsole()
	{
		$builder = $this->getContainerBuilder();

		$application = $builder->getDefinition($builder->getByType('ApiGen\Console\Application'));

		foreach (array_keys($builder->findByTag(self::TAG_CONSOLE_COMMAND)) as $serviceName) {
			$className = $builder->getDefinition($serviceName)->getClass();
			if ( ! $this->isPhar() && $className === 'ApiGen\Command\SelfUpdateCommand') {
				continue;
			}
			$application->addSetup('add', ['@' . $serviceName]);
		}
	}


	/**
	 * @return bool
	 */
	private function isPhar()
	{
		return substr(__FILE__, 0, 5) === 'phar:';
	}


	private function setupTemplatingFilters()
	{
		$builder = $this->getContainerBuilder();
		$latteFactory = $builder->getDefinition($builder->getByType('Latte\Engine'));
		foreach (array_keys($builder->findByTag(self::TAG_LATTE_FILTER)) as $serviceName) {
			$latteFactory->addSetup('addFilter', [NULL, ['@' . $serviceName, 'loader']]);
		}
	}


	private function setupGeneratorQueue()
	{
		$builder = $this->getContainerBuilder();
		$generator = $builder->getDefinition($builder->getByType('ApiGen\Generator\GeneratorQueue'));
		foreach (array_keys($builder->findByTag(self::TAG_TEMPLATE_GENERATOR)) as $serviceName) {
			$generator->addSetup('addToQueue', ['@' . $serviceName]);
		}
	}

}
