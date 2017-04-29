<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator\TemplateGenerators;

use ApiGen\Configuration\Configuration;
use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Generator\TemplateGenerator;
use ApiGen\Parser\Elements\ElementExtractor;
use ApiGen\Parser\Elements\Elements;
use ApiGen\Templating\Template;
use ApiGen\Templating\TemplateFactory;


class AnnotationGroupsGenerator implements TemplateGenerator
{

	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var TemplateFactory
	 */
	private $templateFactory;

	/**
	 * @var ElementExtractor
	 */
	private $elementExtractor;


	public function __construct(
		Configuration $configuration,
		TemplateFactory $templateFactory,
		ElementExtractor $elementExtractor
	) {
		$this->configuration = $configuration;
		$this->templateFactory = $templateFactory;
		$this->elementExtractor = $elementExtractor;
	}


	public function generate()
	{
		$annotations = $this->configuration->getOption(CO::ANNOTATION_GROUPS);
		foreach ($annotations as $annotation) {
			$template = $this->templateFactory->createNamedForElement(TemplateFactory::ELEMENT_ANNOTATION_GROUP, $annotation);
			$template = $this->setElementsWithAnnotationToTemplate($template, $annotation);
			$template->save();
		}
	}


	/**
	 * @param Template $template
	 * @param string $annotation
	 * @return Template
	 */
	private function setElementsWithAnnotationToTemplate(Template $template, $annotation)
	{
		$elements = $this->elementExtractor->extractElementsByAnnotation($annotation);

		$template->setParameters([
			'annotation' => $annotation,
			'hasElements' => (bool) count(array_filter($elements, 'count')),
			'annotationClasses' => $elements[Elements::CLASSES],
			'annotationInterfaces' => $elements[Elements::INTERFACES],
			'annotationTraits' => $elements[Elements::TRAITS],
			'annotationExceptions' => $elements[Elements::EXCEPTIONS],
			'annotationConstants' => $elements[Elements::CONSTANTS],
			'annotationMethods' => $elements[Elements::METHODS],
			'annotationFunctions' => $elements[Elements::FUNCTIONS],
			'annotationProperties' => $elements[Elements::PROPERTIES]
		]);

		return $template;
	}

}
