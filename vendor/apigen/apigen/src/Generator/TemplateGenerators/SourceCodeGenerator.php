<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator\TemplateGenerators;

use ApiGen\Charset\CharsetConvertor;
use ApiGen\Configuration\Configuration;
use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Configuration\Theme\ThemeConfigOptions as TCO;
use ApiGen\Generator\ConditionalTemplateGenerator;
use ApiGen\Generator\Resolvers\RelativePathResolver;
use ApiGen\Generator\SourceCodeHighlighter\SourceCodeHighlighter;
use ApiGen\Generator\StepCounter;
use ApiGen\Parser\Elements\ElementStorage;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Templating\TemplateFactory;
use Nette;


/**
 * @method onGenerateProgress()
 */
class SourceCodeGenerator extends Nette\Object implements ConditionalTemplateGenerator, StepCounter
{

	/**
	 * @var array
	 */
	public $onGenerateProgress = [];

	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var ElementStorage
	 */
	private $elementStorage;

	/**
	 * @var TemplateFactory
	 */
	private $templateFactory;

	/**
	 * @var RelativePathResolver
	 */
	private $relativePathResolver;

	/**
	 * @var CharsetConvertor
	 */
	private $charsetConvertor;

	/**
	 * @var SourceCodeHighlighter
	 */
	private $sourceCodeHighlighter;


	public function __construct(
		Configuration $configuration,
		ElementStorage $elementStorage,
		TemplateFactory $templateFactory,
		RelativePathResolver $relativePathResolver,
		CharsetConvertor $charsetConvertor,
		SourceCodeHighlighter $sourceCodeHighlighter
	) {
		$this->configuration = $configuration;
		$this->elementStorage = $elementStorage;
		$this->templateFactory = $templateFactory;
		$this->relativePathResolver = $relativePathResolver;
		$this->charsetConvertor = $charsetConvertor;
		$this->sourceCodeHighlighter = $sourceCodeHighlighter;
	}


	public function generate()
	{
		foreach ($this->elementStorage->getElements() as $type => $elementList) {
			foreach ($elementList as $element) {
				/** @var ReflectionElement $element */
				if ($element->isTokenized()) {
					$this->generateForElement($element);
					$this->onGenerateProgress();
				}
			}
		}
	}


	/**
	 * @return bool
	 */
	public function isAllowed()
	{
		return $this->configuration->getOption(CO::SOURCE_CODE);
	}


	/**
	 * @return int
	 */
	public function getStepCount()
	{
		$tokenizedFilter = function (ReflectionClass $class) {
			return $class->isTokenized();
		};

		$count = count(array_filter($this->elementStorage->getClasses(), $tokenizedFilter))
			+ count(array_filter($this->elementStorage->getInterfaces(), $tokenizedFilter))
			+ count(array_filter($this->elementStorage->getTraits(), $tokenizedFilter))
			+ count(array_filter($this->elementStorage->getExceptions(), $tokenizedFilter))
			+ count($this->elementStorage->getConstants())
			+ count($this->elementStorage->getFunctions());

		return $count;
	}


	private function generateForElement(ReflectionElement $element)
	{
		$template = $this->templateFactory->createNamedForElement(TCO::SOURCE, $element);
		$template->setParameters([
			'fileName' => $this->relativePathResolver->getRelativePath($element->getFileName()),
			'source' => $this->getHighlightedCodeFromElement($element)
		]);
		$template->save();
	}


	/**
	 * @return string
	 */
	private function getHighlightedCodeFromElement(ReflectionElement $element)
	{
		$content = $this->charsetConvertor->convertFileToUtf($element->getFileName());
		return $this->sourceCodeHighlighter->highlightAndAddLineNumbers($content);
	}

}
