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
use ApiGen\Templating\Filters\Helpers\ElementUrlFactory;
use ApiGen\Templating\Filters\NamespaceAndPackageUrlFilters;
use ApiGen\Templating\Filters\SourceFilters;


class TemplateNavigator
{

	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var SourceFilters
	 */
	private $sourceFilters;

	/**
	 * @var ElementUrlFactory
	 */
	private $elementUrlFactory;

	/**
	 * @var NamespaceAndPackageUrlFilters
	 */
	private $namespaceAndPackageUrlFilters;


	public function __construct(
		Configuration $configuration,
		SourceFilters $sourceFilters,
		ElementUrlFactory $elementUrlFactory,
		NamespaceAndPackageUrlFilters $namespaceAndPackageUrlFilters
	) {
		$this->configuration = $configuration;
		$this->sourceFilters = $sourceFilters;
		$this->elementUrlFactory = $elementUrlFactory;
		$this->namespaceAndPackageUrlFilters = $namespaceAndPackageUrlFilters;
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function getTemplatePath($name)
	{
		$options = $this->configuration->getOptions();
		return $options[CO::TEMPLATE][TCO::TEMPLATES][$name]['template'];
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function getTemplateFileName($name)
	{
		$options = $this->configuration->getOptions();
		return $this->getDestination() . '/' . $options[CO::TEMPLATE][TCO::TEMPLATES][$name]['filename'];
	}


	/**
	 * @param string $namespace
	 * @return string
	 */
	public function getTemplatePathForNamespace($namespace)
	{
		return $this->getDestination() . '/' . $this->namespaceAndPackageUrlFilters->namespaceUrl($namespace);
	}


	/**
	 * @param string $package
	 * @return string
	 */
	public function getTemplatePathForPackage($package)
	{
		return $this->getDestination() . '/' . $this->namespaceAndPackageUrlFilters->packageUrl($package);
	}


	/**
	 * @return string
	 */
	public function getTemplatePathForClass(ReflectionClass $element)
	{
		return $this->getDestination() . '/' . $this->elementUrlFactory->createForClass($element);
	}


	/**
	 * @return string
	 */
	public function getTemplatePathForConstant(ReflectionConstant $element)
	{
		return $this->getDestination() . '/' . $this->elementUrlFactory->createForConstant($element);
	}


	/**
	 * @return string
	 */
	public function getTemplatePathForFunction(ReflectionFunction $element)
	{
		return $this->getDestination() . '/' . $this->elementUrlFactory->createForFunction($element);
	}


	/**
	 * @return string
	 */
	public function getTemplatePathForSourceElement(ReflectionElement $element)
	{
		return $this->getDestination() . '/' . $this->sourceFilters->sourceUrl($element, FALSE);
	}


	/**
	 * @param string $element
	 * @return string
	 */
	public function getTemplatePathForAnnotationGroup($element)
	{
		return $this->getDestination() . '/' . $this->elementUrlFactory->createForAnnotationGroup($element);
	}


	/**
	 * @return string
	 */
	private function getDestination()
	{
		return $this->configuration->getOption(CO::DESTINATION);
	}

}
