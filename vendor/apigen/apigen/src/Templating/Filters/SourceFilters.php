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
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionFunction;


class SourceFilters extends Filters
{

	/**
	 * @var Configuration
	 */
	private $configuration;


	public function __construct(Configuration $configuration)
	{
		$this->configuration = $configuration;
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function staticFile($name)
	{
		$filename = $this->configuration->getOption(CO::DESTINATION) . '/' . $name;
		if (is_file($filename)) {
			$name .= '?' . sha1_file($filename);
		}
		return $name;
	}


	/**
	 * @param ReflectionElement|ReflectionConstant $element
	 * @param bool $withLine Include file line number into the link
	 * @return string
	 */
	public function sourceUrl(ReflectionElement $element, $withLine = TRUE)
	{
		$file = '';
		if ($this->isDirectUrl($element)) {
			$elementName = $element->getName();
			if ($element instanceof ReflectionClass) {
				$file = 'class-';

			} elseif ($element instanceof ReflectionConstant) {
				$file = 'constant-';

			} elseif ($element instanceof ReflectionFunction) {
				$file = 'function-';
			}

		} else {
			$elementName = $element->getDeclaringClassName();
			$file = 'class-';
		}

		$file .= $this->urlize($elementName);

		$url = sprintf($this->configuration->getOption(CO::TEMPLATE)['templates']['source']['filename'], $file);
		if ($withLine) {
			$url .= $this->getElementLinesAnchor($element);
		}
		return $url;
	}


	/**
	 * @return bool
	 */
	private function isDirectUrl(ReflectionElement $element)
	{
		if ($element instanceof ReflectionClass || $element instanceof ReflectionFunction
			|| ($element instanceof ReflectionConstant && $element->getDeclaringClassName() === NULL)
		) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @param ReflectionElement $element
	 * @return string
	 */
	private function getElementLinesAnchor(ReflectionElement $element)
	{
		$anchor = '#' . $element->getStartLine();
		if ($element->getStartLine() !== $element->getEndLine()) {
			$anchor .= '-' . $element->getEndLine();
		}
		return $anchor;
	}

}
