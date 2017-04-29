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
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Templating\Template;
use ApiGen\Templating\TemplateFactory;
use Nette;


/**
 * @method onGenerateProgress()
 */
class ConstantElementGenerator extends Nette\Object implements TemplateGenerator, StepCounter
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
		foreach ($this->elementStorage->getConstants() as $name => $reflectionConstant) {
			$template = $this->templateFactory->createForReflection($reflectionConstant);
			$template = $this->loadTemplateWithParameters($template, $reflectionConstant);
			$template->save();
			$this->onGenerateProgress();
		}
	}


	/**
	 * @return int
	 */
	public function getStepCount()
	{
		return count($this->elementStorage->getConstants());
	}


	/**
	 * @return Template
	 */
	private function loadTemplateWithParameters(Template $template, ReflectionConstant $constant)
	{
		$template = $this->namespaceAndPackageLoader->loadTemplateWithElementNamespaceOrPackage($template, $constant);
		$template->setParameters([
			'constant' => $constant
		]);
		return $template;
	}

}
