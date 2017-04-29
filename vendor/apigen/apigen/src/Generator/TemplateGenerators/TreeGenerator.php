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
use ApiGen\Configuration\Theme\ThemeConfigOptions as TCO;
use ApiGen\Generator\ConditionalTemplateGenerator;
use ApiGen\Parser\Elements\Elements;
use ApiGen\Parser\ParserResult;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Templating\TemplateFactory;
use ApiGen\Tree;


class TreeGenerator implements ConditionalTemplateGenerator
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
	 * @var array
	 */
	private $processed = [];

	/**
	 * @var array[]
	 */
	private $treeStorage = [
		Elements::CLASSES => [],
		Elements::INTERFACES => [],
		Elements::TRAITS => [],
		Elements::EXCEPTIONS => []
	];

	/**
	 * @var ParserResult
	 */
	private $parserResult;


	public function __construct(
		Configuration $configuration,
		TemplateFactory $templateFactory,
		ParserResult $parserResult
	) {
		$this->configuration = $configuration;
		$this->templateFactory = $templateFactory;
		$this->parserResult = $parserResult;
	}


	public function generate()
	{
		$template = $this->templateFactory->createForType(TCO::TREE);

		$classes = $this->parserResult->getClasses();
		foreach ($classes as $className => $reflection) {
			if ($this->canBeProcessed($reflection)) {
				$this->addToTreeByReflection($reflection);
			}
		}

		$this->sortTreeStorageElements();

		$template->setParameters([
			'classTree' => new Tree($this->treeStorage[Elements::CLASSES], $classes),
			'interfaceTree' => new Tree($this->treeStorage[Elements::INTERFACES], $classes),
			'traitTree' => new Tree($this->treeStorage[Elements::TRAITS], $classes),
			'exceptionTree' => new Tree($this->treeStorage[Elements::EXCEPTIONS], $classes)
		]);

		$template->save();
	}


	/**
	 * @return bool
	 */
	public function isAllowed()
	{
		return $this->configuration->getOption(CO::TREE);
	}


	/**
	 * @return bool
	 */
	private function canBeProcessed(ReflectionClass $reflection)
	{
		if ( ! $reflection->isMain()) {
			return FALSE;
		}
		if ( ! $reflection->isDocumented()) {
			return FALSE;
		}
		if (isset($this->processed[$reflection->getName()])) {
			return FALSE;
		}
		return TRUE;
	}


	private function addToTreeByReflection(ReflectionClass $reflection)
	{
		if ($reflection->getParentClassName() === NULL) {
			$type = $this->getTypeByReflection($reflection);
			$this->addToTreeByTypeAndName($type, $reflection->getName());

		} else {
			foreach (array_values(array_reverse($reflection->getParentClasses())) as $level => $parent) {
				$type = NULL;
				if ($level === 0) {
					// The topmost parent decides about the reflection type
					$type = $this->getTypeByReflection($reflection);
				}

				/** @var ReflectionClass $parent */
				$parentName = $parent->getName();
				if ( ! isset($this->treeStorage[$type][$parentName])) {
					$this->addToTreeByTypeAndName($type, $parentName);
				}
			}
		}
	}


	/**
	 * @return string
	 */
	private function getTypeByReflection(ReflectionClass $reflection)
	{
		if ($reflection->isInterface()) {
			return Elements::INTERFACES;

		} elseif ($reflection->isTrait()) {
			return Elements::TRAITS;

		} elseif ($reflection->isException()) {
			return Elements::EXCEPTIONS;

		} else {
			return Elements::CLASSES;
		}
	}


	/**
	 * @param string $type
	 * @param string $name
	 */
	private function addToTreeByTypeAndName($type, $name)
	{
		$this->treeStorage[$type][$name] = [];
		$this->processed[$name] = TRUE;
	}


	private function sortTreeStorageElements()
	{
		foreach ($this->treeStorage as $key => $elements) {
			ksort($elements, SORT_STRING);
			$this->treeStorage[$key] = $elements;
		}
	}

}
