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
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Templating\Template;
use ApiGen\Templating\TemplateFactory;
use Nette;


/**
 * @method onGenerateProgress()
 */
class ClassElementGenerator extends Nette\Object implements TemplateGenerator, StepCounter
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
		foreach ($this->elementStorage->getClassElements() as $name => $reflectionClass) {
			$template = $this->templateFactory->createForReflection($reflectionClass);
			$template = $this->loadTemplateWithParameters($template, $reflectionClass);
			$template->save();
			$this->onGenerateProgress();
		}
	}


	/**
	 * @return int
	 */
	public function getStepCount()
	{
		return count($this->elementStorage->getClassElements());
	}


	/**
	 * @return Template
	 */
	private function loadTemplateWithParameters(Template $template, ReflectionClass $class)
	{
		$template = $this->namespaceAndPackageLoader->loadTemplateWithElementNamespaceOrPackage($template, $class);
		$template->setParameters([
			'class' => $class,
			'tree' => array_merge(array_reverse($class->getParentClasses()), [$class]),
			'directSubClasses' => $class->getDirectSubClasses(),
			'indirectSubClasses' => $class->getIndirectSubClasses(),
			'directImplementers' => $class->getDirectImplementers(),
			'indirectImplementers' => $class->getIndirectImplementers(),
			'directUsers' => $class->getDirectUsers(),
			'indirectUsers' => $class->getIndirectUsers(),
		]);
		return $template;
	}

}
