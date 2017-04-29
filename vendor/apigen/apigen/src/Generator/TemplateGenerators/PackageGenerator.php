<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator\TemplateGenerators;

use ApiGen\Generator\ConditionalTemplateGenerator;
use ApiGen\Generator\StepCounter;
use ApiGen\Generator\TemplateGenerators\Loaders\NamespaceAndPackageLoader;
use ApiGen\Parser\Elements\ElementStorage;
use ApiGen\Templating\TemplateFactory;
use Nette;


/**
 * @method onGenerateProgress()
 */
class PackageGenerator extends Nette\Object implements ConditionalTemplateGenerator, StepCounter
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
		foreach ($this->elementStorage->getPackages() as $name => $package) {
			$template = $this->templateFactory->createNamedForElement(TemplateFactory::ELEMENT_PACKAGE, $name);
			$template = $this->namespaceAndPackageLoader->loadTemplateWithPackage($template, $name, $package);
			$template->save();
			$this->onGenerateProgress();
		}
	}


	/**
	 * @return int
	 */
	public function getStepCount()
	{
		return count($this->elementStorage->getPackages());
	}


	/**
	 * @return bool
	 */
	public function isAllowed()
	{
		return (bool) $this->elementStorage->getPackages();
	}

}
