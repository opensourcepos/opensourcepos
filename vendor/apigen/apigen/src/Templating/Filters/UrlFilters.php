<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters;

use ApiGen\Configuration\Configuration;
use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Generator\Markups\Markup;
use ApiGen\Generator\Resolvers\ElementResolver;
use ApiGen\Generator\SourceCodeHighlighter\SourceCodeHighlighter;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionFunction;
use ApiGen\Templating\Filters\Helpers\ElementLinkFactory;
use ApiGen\Templating\Filters\Helpers\LinkBuilder;
use ApiGen\Templating\Filters\Helpers\Strings;
use Latte\Runtime\Filters as LatteFilters;
use Nette\Utils\Validators;


class UrlFilters extends Filters
{

	/**
	 * @var SourceCodeHighlighter
	 */
	private $highlighter;

	/**
	 * @var Markup
	 */
	private $markup;

	/**
	 * @var ElementResolver
	 */
	private $elementResolver;

	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var LinkBuilder
	 */
	private $linkBuilder;

	/**
	 * @var ElementLinkFactory
	 */
	private $elementLinkFactory;


	public function __construct(
		Configuration $configuration,
		SourceCodeHighlighter $highlighter,
		Markup $markup,
		ElementResolver $elementResolver,
		LinkBuilder $linkBuilder,
		ElementLinkFactory $elementLinkFactory
	) {
		$this->highlighter = $highlighter;
		$this->markup = $markup;
		$this->elementResolver = $elementResolver;
		$this->configuration = $configuration;
		$this->linkBuilder = $linkBuilder;
		$this->elementLinkFactory = $elementLinkFactory;
	}


	/**
	 * Tries to parse a definition of a class/method/property/constant/function
	 * and returns the appropriate link if successful.
	 *
	 * @param string $definition
	 * @param ReflectionElement $reflectionElement
	 * @return string|NULL
	 */
	public function resolveLink($definition, ReflectionElement $reflectionElement)
	{
		if (empty($definition)) {
			return NULL;
		}

		$suffix = '';
		if (substr($definition, -2) === '[]') {
			$definition = substr($definition, 0, -2);
			$suffix = '[]';
		}

		$element = $this->elementResolver->resolveElement($definition, $reflectionElement, $expectedName);
		if ($element === NULL) {
			return $expectedName;
		}

		$classes = [];
		if ($element->isDeprecated()) {
			$classes[] = 'deprecated';
		}

		/** @var ReflectionFunction $element */
		if ( ! $element->isValid()) {
			$classes[] = 'invalid';
		}

		$link = $this->createLinkForElement($element, $classes);
		return '<code>' . $link . $suffix . '</code>';
	}


	/**
	 * @param string $value
	 * @param string $name
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	public function annotation($value, $name, ReflectionElement $reflectionElement)
	{
		$annotationProcessors = [
			'return' => $this->processReturnAnnotations($value, $reflectionElement),
			'throws' => $this->processThrowsAnnotations($value, $reflectionElement),
			'license' => $this->processLicenseAnnotations($value),
			'link' => $this->processLinkAnnotations($value),
			'see' => $this->processSeeAnnotations($value, $reflectionElement),
			'uses' => $this->processUsesAndUsedbyAnnotations($value, $reflectionElement),
			'usedby' => $this->processUsesAndUsedbyAnnotations($value, $reflectionElement),
		];

		if (isset($annotationProcessors[$name])) {
			return $annotationProcessors[$name];
		}

		return $this->doc($value, $reflectionElement);
	}


	/**
	 * Returns links for types.
	 *
	 * @param string $annotation
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	public function typeLinks($annotation, ReflectionElement $reflectionElement)
	{
		$links = [];

		list($types) = Strings::split($annotation);
		if ( ! empty($types) && $types[0] === '$') {
			$types = NULL;
		}

		foreach (explode('|', $types) as $type) {
			$type = $this->getTypeName($type, FALSE);
			$links[] = $this->resolveLink($type, $reflectionElement) ?: LatteFilters::escapeHtml(ltrim($type, '\\'));
		}

		return implode('|', $links);
	}


	/********************* description *********************/


	/**
	 * @param string $annotation
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	public function description($annotation, ReflectionElement $reflectionElement)
	{
		$description = trim(strpbrk($annotation, "\n\r\t $")) ?: $annotation;
		return $this->doc($description, $reflectionElement);
	}


	/**
	 * @param ReflectionElement $reflectionElement
	 * @param bool $block
	 * @return string
	 */
	public function shortDescription(ReflectionElement $reflectionElement, $block = FALSE)
	{
		return $this->doc($reflectionElement->getShortDescription(), $reflectionElement, $block);
	}


	/**
	 * @return string
	 */
	public function longDescription(ReflectionElement $element)
	{
		$long = $element->getLongDescription();

		// Merge lines
		$long = preg_replace_callback('~(?:<(code|pre)>.+?</\1>)|([^<]*)~s', function ($matches) {
			return ! empty($matches[2])
				? preg_replace('~\n(?:\t|[ ])+~', ' ', $matches[2])
				: $matches[0];
		}, $long);

		return $this->doc($long, $element, TRUE);
	}


	/********************* text formatter *********************/


	/**
	 * @param string $text
	 * @param ReflectionElement $reflectionElement
	 * @param bool $block
	 * @return string
	 */
	public function doc($text, ReflectionElement $reflectionElement, $block = FALSE)
	{
		$text = $this->resolveInternalAnnotation($text);

		// Process markup
		if ($block) {
			$text = $this->markup->block($text);

		} else {
			$text = $this->markup->line($text);
		}

		return $this->resolveLinkAndSeeAnnotation($text, $reflectionElement);
	}


	/**
	 * @param string $text
	 * @return string
	 */
	private function resolveInternalAnnotation($text)
	{
		$pattern = '~\\{@(\\w+)(?:(?:\\s+((?>(?R)|[^{}]+)*)\\})|\\})~';
		return preg_replace_callback($pattern, function ($matches) {
			if ($matches[1] !== 'internal') {
				return $matches[0];
			}

			if ($this->configuration->getOption(CO::INTERNAL) && isset($matches[2])) {
				return $matches[2];
			}

			return '';
		}, $text);
	}


	/**
	 * @param string $text
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	private function resolveLinkAndSeeAnnotation($text, ReflectionElement $reflectionElement)
	{
		return preg_replace_callback('~{@(?:link|see)\\s+([^}]+)}~', function ($matches) use ($reflectionElement) {
			list($url, $description) = Strings::split($matches[1]);

			if (Validators::isUri($url)) {
				return $this->linkBuilder->build($url, $description ?: $url);
			}

			if ($link = $this->resolveLink($matches[1], $reflectionElement)) {
				return $link;
			}

			return $matches[1];
		}, $text);
	}


	/********************* highlight *********************/


	/**
	 * @param string $source
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	public function highlightPhp($source, ReflectionElement $reflectionElement)
	{
		return $this->resolveLink($this->getTypeName($source), $reflectionElement)
			?: $this->highlighter->highlight((string) $source);
	}


	/**
	 * @param string $definition
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	public function highlightValue($definition, ReflectionElement $reflectionElement)
	{
		return $this->highlightPhp(preg_replace('~^(?:[ ]{4}|\t)~m', '', $definition), $reflectionElement);
	}


	/**
	 * @return string
	 */
	private function createLinkForElement($reflectionElement, array $classes)
	{
		return $this->elementLinkFactory->createForElement($reflectionElement, $classes);
	}


	/**
	 * @param string $value
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	private function processReturnAnnotations($value, ReflectionElement $reflectionElement)
	{
		$description = $this->getDescriptionFromValue($value, $reflectionElement);
		$typeLinks = $this->typeLinks($value, $reflectionElement);
		return $typeLinks . $description;
	}


	/**
	 * @param string $value
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	private function processThrowsAnnotations($value, ReflectionElement $reflectionElement)
	{
		$description = $this->getDescriptionFromValue($value, $reflectionElement);
		$typeLinks = $this->typeLinks($value, $reflectionElement);
		return $typeLinks . $description;
	}


	/**
	 * @param mixed $value
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	private function getDescriptionFromValue($value, ReflectionElement $reflectionElement)
	{
		$description = trim(strpbrk($value, "\n\r\t $")) ?: NULL;
		if ($description) {
			$description = '<br>' . $this->doc($description, $reflectionElement);
		}
		return $description;
	}


	/**
	 * @param string $value
	 * @return string
	 */
	private function processLicenseAnnotations($value)
	{
		list($url, $description) = Strings::split($value);
		return $this->linkBuilder->build($url, $description ?: $url);
	}


	/**
	 * @param string $value
	 * @return string
	 */
	private function processLinkAnnotations($value)
	{
		list($url, $description) = Strings::split($value);
		if (Validators::isUri($url)) {
			return $this->linkBuilder->build($url, $description ?: $url);
		}
		return NULL;
	}


	/**
	 * @param string $value
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	private function processSeeAnnotations($value, ReflectionElement $reflectionElement)
	{
		$doc = [];
		foreach (preg_split('~\\s*,\\s*~', $value) as $link) {
			if ($this->elementResolver->resolveElement($link, $reflectionElement) !== NULL) {
				$doc[] = $this->typeLinks($link, $reflectionElement);

			} else {
				$doc[] = $this->doc($link, $reflectionElement);
			}
		}
		return implode(', ', $doc);
	}


	/**
	 * @param string $value
	 * @param ReflectionElement $reflectionElement
	 * @return string
	 */
	private function processUsesAndUsedbyAnnotations($value, ReflectionElement $reflectionElement)
	{
		list($link, $description) = Strings::split($value);
		$separator = $reflectionElement instanceof ReflectionClass || ! $description ? ' ' : '<br>';
		if ($this->elementResolver->resolveElement($link, $reflectionElement) !== NULL) {
			$value = $this->typeLinks($link, $reflectionElement) . $separator . $description;
			return trim($value);
		}
		return NULL;
	}

}
