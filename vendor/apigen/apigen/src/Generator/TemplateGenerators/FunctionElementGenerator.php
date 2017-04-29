<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator\TemplateGenerators;

use ApiGen\Generator\StepCounter;
use ApiGen\Generator\TemplateGenerator;
use ApiGen\Generator\TemplateGenerators\Loaders\NamespaceAndPackageLoader;
use ApiGen\Parser\Elements\ElementStorage;
use ApiGen\Reflection\ReflectionFunction;
use ApiGen\Templating\Template;
use ApiGen\Templating\TemplateFactory;
use Nette;


/**
 * @method onGenerateProgress()
 */
class FunctionElementGenerator extends Nette\Object implements TemplateGenerator, StepCounter
{

	/**
	 * @var array
	 */
	public $onGenerateProgress = [];

	/**
	 * @var TemplateFactory
	 */
	private $templateFactory;

	/**
	 * @var ElementStorage
	 */
	private $elementStorage;

	/**
	 * @var NamespaceAndPackageLoader
	 */
	private $namespaceAndPackageLoader;


	public function __construct(
		TemplateFactory $templateFactory,
		ElementStorage $elementStorage,
		NamespaceAndPackageLoader $namespaceAndPackageLoader
	) {
		$this->templateFactory = $templateFactory;
		$this->elementStorage = $elementStorage;
		$this->namespaceAndPackageLoader = $namespaceAndPackageLoader;
	}


	public function generate()
	{
		foreach ($this->elementStorage->getFunctions() as $name => $reflectionFunction) {
			$template = $this->templateFactory->createForReflection($reflectionFunction);
			$template = $this->loadTemplateWithParameters($template, $reflectionFunction);
			$template->save();
			$this->onGenerateProgress();
		}
	}


	/**
	 * @return int
	 */
	public function getStepCount()
	{
		return count($this->elementStorage->getFunctions());
	}


	/**
	 * @return Template
	 */
	private function loadTemplateWithParameters(Template $template, ReflectionFunction $function)
	{
		$template = $this->namespaceAndPackageLoader->loadTemplateWithElementNamespaceOrPackage($template, $function);
		$template->setParameters([
			'function' => $function
		]);
		return $template;
	}

}
