<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating;

use ApiGen\Configuration\Configuration;
use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Configuration\Theme\ThemeConfigOptions as TCO;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionFunction;
use ApiGen\Templating\Exceptions\UnsupportedElementException;
use Latte;
use Nette\Utils\ArrayHash;


class TemplateFactory
{

	const ELEMENT_SOURCE = 'source';
	const ELEMENT_PACKAGE = 'package';
	const ELEMENT_NAMESPACE = 'namespace';
	const ELEMENT_ANNOTATION_GROUP = 'annotationGroup';

	/**
	 * @var Latte\Engine
	 */
	private $latteEngine;

	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var TemplateNavigator
	 */
	private $templateNavigator;

	/**
	 * @var TemplateElementsLoader
	 */
	private $templateElementsLoader;

	/**
	 * @var Template
	 */
	private $builtTemplate;


	public function __construct(
		Latte\Engine $latteEngine,
		Configuration $configuration,
		TemplateNavigator $templateNavigator,
		TemplateElementsLoader $templateElementsLoader
	) {
		$this->latteEngine = $latteEngine;
		$this->configuration = $configuration;
		$this->templateNavigator = $templateNavigator;
		$this->templateElementsLoader = $templateElementsLoader;
	}


	/**
	 * @return Template
	 */
	public function create()
	{
		return $this->buildTemplate();
	}


	/**
	 * @param string $type
	 * @return Template
	 */
	public function createForType($type)
	{
		$template = $this->buildTemplate();
		$template->setFile($this->templateNavigator->getTemplatePath($type));
		$template->setSavePath($this->templateNavigator->getTemplateFileName($type));
		$template = $this->setEmptyDefaults($template);
		return $template;
	}


	/**
	 * @param string $name
	 * @param ReflectionElement|string $element
	 * @throws \Exception
	 * @return Template
	 */
	public function createNamedForElement($name, $element)
	{
		$template = $this->buildTemplate();
		$template->setFile($this->templateNavigator->getTemplatePath($name));

		if ($name === self::ELEMENT_SOURCE) {
			$template->setSavePath($this->templateNavigator->getTemplatePathForSourceElement($element));

		} elseif ($name === self::ELEMENT_NAMESPACE) {
			$template->setSavePath($this->templateNavigator->getTemplatePathForNamespace($element));

		} elseif ($name === self::ELEMENT_PACKAGE) {
			$template->setSavePath($this->templateNavigator->getTemplatePathForPackage($element));

		} elseif ($name === self::ELEMENT_ANNOTATION_GROUP) {
			$template->setSavePath($this->templateNavigator->getTemplatePathForAnnotationGroup($element));

		} else {
			throw new UnsupportedElementException($name . ' is not supported template type.');
		}
		return $template;
	}


	/**
	 * @param ReflectionElement $element
	 * @return Template
	 */
	public function createForReflection($element)
	{
		$template = $this->buildTemplate();

		if ($element instanceof ReflectionClass) {
			$template->setFile($this->templateNavigator->getTemplatePath('class'));
			$template->setSavePath($this->templateNavigator->getTemplatePathForClass($element));

		} elseif ($element instanceof ReflectionConstant) {
			$template->setFile($this->templateNavigator->getTemplatePath('constant'));
			$template->setSavePath($this->templateNavigator->getTemplatePathForConstant($element));

		} elseif ($element instanceof ReflectionFunction) {
			$template->setFile($this->templateNavigator->getTemplatePath('function'));
			$template->setSavePath($this->templateNavigator->getTemplatePathForFunction($element));
		}

		return $template;
	}


	/**
	 * @return Template
	 */
	private function buildTemplate()
	{
		if ($this->builtTemplate === NULL) {
			$options = $this->configuration->getOptions();
			$template = new Template($this->latteEngine);
			$template->setParameters([
				'config' => ArrayHash::from($options),
				'basePath' => $options[CO::TEMPLATE][TCO::TEMPLATES_PATH]
			]);
			$this->builtTemplate = $template;
		}
		return $this->templateElementsLoader->addElementsToTemplate($this->builtTemplate);
	}


	/**
	 * @return Template
	 */
	private function setEmptyDefaults(Template $template)
	{
		return $template->setParameters([
			'namespace' => NULL,
			'package' => NULL
		]);
	}

}
