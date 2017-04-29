<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Parser\Elements;

use ApiGen\Configuration\Configuration;
use ApiGen\Generator\Resolvers\ElementResolver;
use ApiGen\Parser\ParserResult;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionFunction;


class ElementStorage
{

	/**
	 * @var array
	 */
	private $namespaces = [];

	/**
	 * @var array
	 */
	private $packages = [];

	/**
	 * @var array
	 */
	private $classes = [];

	/**
	 * @var array
	 */
	private $interfaces = [];

	/**
	 * @var array
	 */
	private $traits = [];

	/**
	 * @var array
	 */
	private $exceptions = [];

	/**
	 * @var array
	 */
	private $constants = [];

	/**
	 * @var array
	 */
	private $functions = [];

	/**
	 * @var bool
	 */
	private $areElementsCategorized = FALSE;

	/**
	 * @var ParserResult
	 */
	private $parserResult;

	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var GroupSorter
	 */
	private $groupSorter;

	/**
	 * @var ElementResolver
	 */
	private $elementResolver;


	public function __construct(
		ParserResult $parserResult,
		Configuration $configuration,
		GroupSorter $groupSorter,
		ElementResolver $elementResolver
	) {
		$this->parserResult = $parserResult;
		$this->configuration = $configuration;
		$this->groupSorter = $groupSorter;
		$this->elementResolver = $elementResolver;
	}


	/**
	 * @return ReflectionClass[]
	 */
	public function getClasses()
	{
		$this->ensureCategorization();
		return $this->classes;
	}


	/**
	 * @return array
	 */
	public function getNamespaces()
	{
		$this->ensureCategorization();
		return $this->namespaces;
	}


	/**
	 * @return array
	 */
	public function getPackages()
	{
		$this->ensureCategorization();
		return $this->packages;
	}


	/**
	 * @return array
	 */
	public function getInterfaces()
	{
		$this->ensureCategorization();
		return $this->interfaces;
	}


	/**
	 * @return array
	 */
	public function getTraits()
	{
		$this->ensureCategorization();
		return $this->traits;
	}


	/**
	 * @return array
	 */
	public function getExceptions()
	{
		$this->ensureCategorization();
		return $this->exceptions;
	}


	/**
	 * @return array
	 */
	public function getConstants()
	{
		$this->ensureCategorization();
		return $this->constants;
	}


	/**
	 * @return array
	 */
	public function getFunctions()
	{
		 $this->ensureCategorization();
		 return $this->functions;
	}


	/**
	 * @return ReflectionClass[]
	 */
	public function getClassElements()
	{
		return array_merge($this->getClasses(), $this->getTraits(), $this->getInterfaces(), $this->getExceptions());
	}


	/**
	 * @return array[]
	 */
	public function getElements()
	{
		$this->ensureCategorization();

		$elements = [
			Elements::CLASSES => $this->classes,
			Elements::CONSTANTS => $this->constants,
			Elements::FUNCTIONS => $this->functions,
			Elements::INTERFACES => $this->interfaces,
			Elements::TRAITS => $this->traits,
			Elements::EXCEPTIONS => $this->exceptions
		];
		return $elements;
	}


	private function categorizeParsedElements()
	{
		foreach ($this->parserResult->getTypes() as $type) {
			$elements = $this->parserResult->getElementsByType($type);
			foreach ($elements as $elementName => $element) {
				if ( ! $element->isDocumented()) {
					continue;
				}
				if ($element instanceof ReflectionConstant) {
					$elementType = Elements::CONSTANTS;
					$this->constants[$elementName] = $element;

				} elseif ($element instanceof ReflectionFunction) {
					$elementType = Elements::FUNCTIONS;
					$this->functions[$elementName] = $element;

				} elseif ($element->isInterface()) {
					$elementType = Elements::INTERFACES;
					$this->interfaces[$elementName] = $element;

				} elseif ($element->isTrait()) {
					$elementType = Elements::TRAITS;
					$this->traits[$elementName] = $element;

				} elseif ($element->isException()) {
					$elementType = Elements::EXCEPTIONS;
					$this->exceptions[$elementName] = $element;

				} else {
					$elementType = Elements::CLASSES;
					$this->classes[$elementName] = $element;
				}
				$this->categorizeElementToNamespaceAndPackage($elementName, $elementType, $element);
			}
		}
		$this->sortNamespacesAndPackages();
		$this->areElementsCategorized = TRUE;
		$this->addUsedByAnnotation();
	}


	/**
	 * @param string $elementName
	 * @param string $elementType
	 * @param ReflectionElement|ReflectionClass $element
	 */
	private function categorizeElementToNamespaceAndPackage($elementName, $elementType, ReflectionElement $element)
	{
		$packageName = $element->getPseudoPackageName();
		$this->packages[$packageName][$elementType][$elementName] = $element;

		$namespaceName = $element->getPseudoNamespaceName();
		$this->namespaces[$namespaceName][$elementType][$element->getShortName()] = $element;
	}


	private function sortNamespacesAndPackages()
	{
		$areNamespacesEnabled = $this->configuration->areNamespacesEnabled(
			$this->getNamespaceCount(),
			$this->getPackageCount()
		);

		$arePackagesEnabled = $this->configuration->arePackagesEnabled($areNamespacesEnabled);

		if ($areNamespacesEnabled) {
			$this->namespaces = $this->groupSorter->sort($this->namespaces);
			$this->packages = [];

		} elseif ($arePackagesEnabled) {
			$this->namespaces = [];
			$this->packages = $this->groupSorter->sort($this->packages);

		} else {
			$this->namespaces = [];
			$this->packages = [];
		}
	}


	/**
	 * @return int
	 */
	private function getNamespaceCount()
	{
		$nonDefaultNamespaces = array_diff(array_keys($this->namespaces), ['PHP', 'None']);
		return count($nonDefaultNamespaces);
	}


	/**
	 * @return int
	 */
	private function getPackageCount()
	{
		$nonDefaultPackages = array_diff(array_keys($this->packages), ['PHP', 'None']);
		return count($nonDefaultPackages);
	}


	private function addUsedByAnnotation()
	{
		foreach ($this->getElements() as $elementList) {
			foreach ($elementList as $parentElement) {
				$elements = $this->getSubElements($parentElement);

				/** @var ReflectionElement $element */
				foreach ($elements as $element) {
					$this->loadUsesToReferencedElementUsedby($element);
				}
			}
		}
	}


	private function ensureCategorization()
	{
		if ($this->areElementsCategorized === FALSE) {
			$this->categorizeParsedElements();
		}
	}


	/**
	 * @return array
	 */
	private function getSubElements(ReflectionElement $parentElement)
	{
		$elements = [$parentElement];
		if ($parentElement instanceof ReflectionClass) {
			$elements = array_merge(
				$elements,
				array_values($parentElement->getOwnMethods()),
				array_values($parentElement->getOwnConstants()),
				array_values($parentElement->getOwnProperties())
			);
		}
		return $elements;
	}


	private function loadUsesToReferencedElementUsedby(ReflectionElement $element)
	{
		$uses = $element->getAnnotation('uses');
		if ($uses === NULL) {
			return;
		}

		foreach ($uses as $value) {
			list($link, $description) = preg_split('~\s+|$~', $value, 2);
			$resolved = $this->elementResolver->resolveElement($link, $element);
			if ($resolved) {
				$resolved->addAnnotation('usedby', $element->getPrettyName() . ' ' . $description);
			}
		}
	}

}
