<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection;

use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Reflection\Extractors\ClassMagicElementsExtractor;
use ApiGen\Reflection\Extractors\ClassTraitElementsExtractor;
use ApiGen\Reflection\Extractors\ParentClassElementsExtractor;
use ApiGen\Reflection\TokenReflection\ReflectionFactory;
use InvalidArgumentException;
use ReflectionProperty as Visibility;
use TokenReflection;
use TokenReflection\IReflectionClass;


class ReflectionClass extends ReflectionElement
{

	/**
	 * @var ReflectionClass[]
	 */
	private $parentClasses;

	/**
	 * @var ReflectionProperty[]
	 */
	private $properties;

	/**
	 * @var ReflectionProperty[]
	 */
	private $ownProperties;

	/**
	 * @var ReflectionConstant[]
	 */
	private $constants;

	/**
	 * @var ReflectionConstant[]
	 */
	private $ownConstants;

	/**
	 * @var ReflectionMethod[]
	 */
	private $methods;

	/**
	 * @var ReflectionMethod[]
	 */
	private $ownMethods;

	/**
	 * @var ClassMagicElementsExtractor
	 */
	private $classMagicElementExtractor;

	/**
	 * @var ClassTraitElementsExtractor
	 */
	private $classTraitElementExtractor;

	/**
	 * @var ParentClassElementsExtractor
	 */
	private $parentClassElementExtractor;


	public function __construct($reflectionClass)
	{
		parent::__construct($reflectionClass);
		$this->classMagicElementExtractor = new ClassMagicElementsExtractor($this);
		$this->classTraitElementExtractor = new ClassTraitElementsExtractor($this, $reflectionClass);
		$this->parentClassElementExtractor = new ParentClassElementsExtractor($this);
	}


	/**
	 * @return string
	 */
	public function getShortName()
	{
		return $this->reflection->getShortName();
	}


	/**
	 * @return bool
	 */
	public function isAbstract()
	{
		return $this->reflection->isAbstract();
	}


	/**
	 * @return bool
	 */
	public function isFinal()
	{
		return $this->reflection->isFinal();
	}


	/**
	 * @return bool
	 */
	public function isInterface()
	{
		return $this->reflection->isInterface();
	}


	/**
	 * @return bool
	 */
	public function isException()
	{
		return $this->reflection->isException();
	}


	/**
	 * @param string $class
	 * @return bool
	 */
	public function isSubclassOf($class)
	{
		return $this->reflection->isSubclassOf($class);
	}


	/**
	 * @return ReflectionMethod[]
	 */
	public function getMethods()
	{
		if ($this->methods === NULL) {
			$this->methods = $this->getOwnMethods();

			foreach ($this->getOwnTraits() as $trait) {
				if (!$trait instanceof ReflectionClass) {
					continue;
				}
				foreach ($trait->getOwnMethods() as $method) {
					if (isset($this->methods[$method->getName()])) {
						continue;
					}
					if (! $this->isDocumented() || $method->isDocumented()) {
						$this->methods[$method->getName()] = $method;
					}
				}
			}

			if (null !== $this->getParentClassName()) {
				foreach ($this->getParentClass()->getMethods() as $parentMethod) {
					if (!isset($this->methods[$parentMethod->getName()])) {
						$this->methods[$parentMethod->getName()] = $parentMethod;
					}
				}
			}

			foreach ($this->getOwnInterfaces() as $interface) {
				foreach ($interface->getMethods(null) as $parentMethod) {
					if (!isset($this->methods[$parentMethod->getName()])) {
						$this->methods[$parentMethod->getName()] = $parentMethod;
					}
				}
			}

			$this->methods = array_filter($this->methods, function(ReflectionMethod $method) {
				$classVisibilityLevel = $this->getVisibilityLevel();
				$methodVisibilityLevel = $method->configuration->getOption(CO::VISIBILITY_LEVELS);
				return $classVisibilityLevel === $methodVisibilityLevel;
			});
		}

		return $this->methods;
	}


	/**
	 * @return ReflectionMethod[]
	 */
	public function getOwnMethods()
	{
		if ($this->ownMethods === NULL) {
			$this->ownMethods = [];

			foreach ($this->reflection->getOwnMethods($this->getVisibilityLevel()) as $method) {
				$apiMethod = $this->reflectionFactory->createFromReflection($method);
				if ( ! $this->isDocumented() || $apiMethod->isDocumented()) {
					$this->ownMethods[$method->getName()] = $apiMethod;
				}
			}
		}
		return $this->ownMethods;
	}


	/**
	 * @return ReflectionMethodMagic[]
	 */
	public function getMagicMethods()
	{
		return $this->classMagicElementExtractor->getMagicMethods();
	}


	/**
	 * @return ReflectionMethodMagic[]
	 */
	public function getOwnMagicMethods()
	{
		return $this->classMagicElementExtractor->getOwnMagicMethods();
	}


	/**
	 * @return ReflectionMethod[]
	 */
	public function getTraitMethods()
	{
		return $this->classTraitElementExtractor->getTraitMethods();
	}


	/**
	 * @param string $name
	 * @return ReflectionMethod
	 */
	public function getMethod($name)
	{
		if ($this->hasMethod($name)) {
			return $this->methods[$name];
		}

		throw new InvalidArgumentException(sprintf(
			'Method %s does not exist in class %s', $name, $this->reflection->getName()
		));
	}


	/**
	 * @return ReflectionProperty[]
	 */
	public function getProperties()
	{
		if ($this->properties === NULL) {
			$this->properties = $this->getOwnProperties();
			foreach ($this->reflection->getProperties($this->getVisibilityLevel()) as $property) {
				/** @var ReflectionElement $property */
				if (isset($this->properties[$property->getName()])) {
					continue;
				}
				$apiProperty = $this->reflectionFactory->createFromReflection($property);
				if ( ! $this->isDocumented() || $apiProperty->isDocumented()) {
					$this->properties[$property->getName()] = $apiProperty;
				}
			}
		}
		return $this->properties;
	}


	/**
	 * @return ReflectionPropertyMagic[]
	 */
	public function getMagicProperties()
	{
		return $this->classMagicElementExtractor->getMagicProperties();
	}


	/**
	 * @return ReflectionPropertyMagic[]
	 */
	public function getOwnMagicProperties()
	{
		return $this->classMagicElementExtractor->getOwnMagicProperties();
	}


	/**
	 * @return ReflectionProperty[]
	 */
	public function getOwnProperties()
	{
		if ($this->ownProperties === NULL) {
			$this->ownProperties = [];
			foreach ($this->reflection->getOwnProperties($this->getVisibilityLevel()) as $property) {
				$apiProperty = $this->reflectionFactory->createFromReflection($property);
				if ( ! $this->isDocumented() || $apiProperty->isDocumented()) {
					/** @var ReflectionElement $property */
					$this->ownProperties[$property->getName()] = $apiProperty;
				}
			}
		}
		return $this->ownProperties;
	}


	/**
	 * @return ReflectionProperty[]
	 */
	public function getTraitProperties()
	{
		return $this->classTraitElementExtractor->getTraitProperties();
	}


	/**
	 * @param string $name
	 * @return ReflectionProperty
	 */
	public function getProperty($name)
	{
		if ($this->hasProperty($name)) {
			return $this->properties[$name];
		}

		throw new InvalidArgumentException(sprintf(
			'Property %s does not exist in class %s', $name, $this->reflection->getName()
		));
	}


	/**
	 * @return ReflectionConstant[]
	 */
	public function getConstants()
	{
		if ($this->constants === NULL) {
			$this->constants = [];
			foreach ($this->reflection->getConstantReflections() as $constant) {
				$apiConstant = $this->reflectionFactory->createFromReflection($constant);
				if ( ! $this->isDocumented() || $apiConstant->isDocumented()) {
					/** @var ReflectionElement $constant */
					$this->constants[$constant->getName()] = $apiConstant;
				}
			}
		}

		return $this->constants;
	}


	/**
	 * @return ReflectionConstant[]|array
	 */
	public function getOwnConstants()
	{
		if ($this->ownConstants === NULL) {
			$this->ownConstants = [];
			$className = $this->reflection->getName();
			foreach ($this->getConstants() as $constantName => $constant) {
				if ($className === $constant->getDeclaringClassName()) {
					$this->ownConstants[$constantName] = $constant;
				}
			}
		}
		return $this->ownConstants;
	}


	/**
	 * @param string $name
	 * @return ReflectionConstant
	 */
	public function getConstantReflection($name)
	{
		if (isset($this->getConstants()[$name])) {
			return $this->getConstants()[$name];
		}

		throw new InvalidArgumentException(sprintf(
			'Constant %s does not exist in class %s', $name, $this->reflection->getName()
		));
	}


	/**
	 * @param string $name
	 * @return ReflectionConstant
	 */
	public function getConstant($name)
	{
		return $this->getConstantReflection($name);
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasConstant($name)
	{
		return isset($this->getConstants()[$name]);
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasOwnConstant($name)
	{
		return isset($this->getOwnConstants()[$name]);
	}


	/**
	 * @param string $name
	 * @return ReflectionConstant
	 */
	public function getOwnConstant($name)
	{
		if (isset($this->getOwnConstants()[$name])) {
			return $this->getOwnConstants()[$name];
		}

		throw new InvalidArgumentException(sprintf(
			'Constant %s does not exist in class %s', $name, $this->reflection->getName()
		));
	}


	/**
	 * @return ReflectionClass
	 */
	public function getParentClass()
	{
		if ($className = $this->reflection->getParentClassName()) {
			return $this->getParsedClasses()[$className];
		}
		return $className;
	}


	/**
	 * @return string|NULL
	 */
	public function getParentClassName()
	{
		return $this->reflection->getParentClassName();
	}


	/**
	 * @return ReflectionClass[]
	 */
	public function getParentClasses()
	{
		if ($this->parentClasses === NULL) {
			$this->parentClasses = array_map(function (IReflectionClass $class) {
				return $this->getParsedClasses()[$class->getName()];
			}, $this->reflection->getParentClasses());
		}
		return $this->parentClasses;
	}


	/**
	 * @return array
	 */
	public function getParentClassNameList()
	{
		return $this->reflection->getParentClassNameList();
	}


	/**
	 * @param string|object $interface
	 * @return bool
	 */
	public function implementsInterface($interface)
	{
		return $this->reflection->implementsInterface($interface);
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getInterfaces()
	{
		return array_map(function (IReflectionClass $class) {
			return $this->getParsedClasses()[$class->getName()];
		}, $this->reflection->getInterfaces());
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getOwnInterfaces()
	{
		return array_map(function (IReflectionClass $class) {
			return $this->getParsedClasses()[$class->getName()];
		}, $this->reflection->getOwnInterfaces());
	}


	/**
	 * @return string[]
	 */
	public function getOwnInterfaceNames()
	{
		return $this->reflection->getOwnInterfaceNames();
	}


	/**
	 * @return ReflectionClass[]|string[]
	 */
	public function getTraits()
	{
		return array_map(function (IReflectionClass $class) {
			if ( ! isset($this->getParsedClasses()[$class->getName()])) {
				return $class->getName();

			} else {
				return $this->getParsedClasses()[$class->getName()];
			}
		}, $this->reflection->getTraits());
	}


	/**
	 * @return array
	 */
	public function getTraitNames()
	{
		return $this->reflection->getTraitNames();
	}


	/**
	 * @return array
	 */
	public function getOwnTraitNames()
	{
		return $this->reflection->getOwnTraitNames();
	}


	/**
	 * @return array
	 */
	public function getTraitAliases()
	{
		return $this->reflection->getTraitAliases();
	}


	/**
	 * @return ReflectionClass[]|string[]
	 */
	public function getOwnTraits()
	{
		return array_map(function (IReflectionClass $class) {
			if ( ! isset($this->getParsedClasses()[$class->getName()])) {
				return $class->getName();

			} else {
				return $this->getParsedClasses()[$class->getName()];
			}
		}, $this->reflection->getOwnTraits());
	}


	/**
	 * @return bool
	 */
	public function isTrait()
	{
		return $this->reflection->isTrait();
	}


	/**
	 * @param string $trait
	 * @return bool
	 */
	public function usesTrait($trait)
	{
		return $this->reflection->usesTrait($trait);
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getDirectSubClasses()
	{
		$subClasses = [];
		foreach ($this->getParsedClasses() as $class) {
			if ($class->isDocumented() && $this->getName() === $class->getParentClassName()) {
				$subClasses[] = $class;
			}
		}
		uksort($subClasses, 'strcasecmp');
		return $subClasses;
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getIndirectSubClasses()
	{
		$subClasses = [];
		foreach ($this->getParsedClasses() as $class) {
			if ($class->isDocumented() && $this->getName() !== $class->getParentClassName()
				&& $class->isSubclassOf($this->getName())
			) {
				$subClasses[] = $class;
			}
		}
		uksort($subClasses, 'strcasecmp');
		return $subClasses;
	}


	/**
	 * @return array
	 */
	public function getDirectImplementers()
	{
		if ( ! $this->isInterface()) {
			return [];
		}
		return $this->parserResult->getDirectImplementersOfInterface($this);
	}


	/**
	 * @return array
	 */
	public function getIndirectImplementers()
	{
		if ( ! $this->isInterface()) {
			return [];
		}
		return $this->parserResult->getIndirectImplementersOfInterface($this);
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getDirectUsers()
	{
		if ( ! $this->isTrait()) {
			return [];
		}
		return $this->classTraitElementExtractor->getDirectUsers();
	}


	/**
	 * @return ReflectionClass[]|array
	 */
	public function getIndirectUsers()
	{
		if ( ! $this->isTrait()) {
			return [];
		}
		return $this->classTraitElementExtractor->getIndirectUsers();
	}


	/**
	 * @return array {[ className => ReflectionMethod[] ]}
	 */
	public function getInheritedMethods()
	{
		return $this->parentClassElementExtractor->getInheritedMethods();
	}


	/**
	 * @return array
	 */
	public function getInheritedMagicMethods()
	{
		return $this->classMagicElementExtractor->getInheritedMagicMethods();
	}


	/**
	 * @return array
	 */
	public function getUsedMethods()
	{
		$usedMethods = $this->classTraitElementExtractor->getUsedMethods();
		return $this->sortUsedMethods($usedMethods);
	}


	/**
	 * @return array
	 */
	public function getUsedMagicMethods()
	{
		$usedMethods = $this->classMagicElementExtractor->getUsedMagicMethods();
		return $this->sortUsedMethods($usedMethods);
	}


	/**
	 * @return array
	 */
	public function getInheritedConstants()
	{
		return $this->parentClassElementExtractor->getInheritedConstants();
	}


	/**
	 * @return array {[ className => ReflectionProperty[] ]}
	 */
	public function getInheritedProperties()
	{
		return $this->parentClassElementExtractor->getInheritedProperties();
	}


	/**
	 * @return ReflectionPropertyMagic[]|array
	 */
	public function getInheritedMagicProperties()
	{
		return $this->classMagicElementExtractor->getInheritedMagicProperties();
	}


	/**
	 * @return array {[ traitName => ReflectionProperty[] ]}
	 */
	public function getUsedProperties()
	{
		return $this->classTraitElementExtractor->getUsedProperties();
	}


	/**
	 * @return array
	 */
	public function getUsedMagicProperties()
	{
		return $this->classMagicElementExtractor->getUsedMagicProperties();
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasProperty($name)
	{
		if ($this->properties === NULL) {
			$this->getProperties();
		}
		return isset($this->properties[$name]);
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasMethod($name)
	{
		return isset($this->getMethods()[$name]);
	}


	/**
	 * @return bool
	 */
	public function isValid()
	{
		if ($this->reflection instanceof TokenReflection\Invalid\ReflectionClass) {
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * @return bool
	 */
	public function isDocumented()
	{
		if ($this->isDocumented === NULL && parent::isDocumented()) {
			$fileName = $this->reflection->getFilename();
			$skipDocPath = $this->configuration->getOption(CO::SKIP_DOC_PATH);
			foreach ($skipDocPath as $mask) {
				if (fnmatch($mask, $fileName, FNM_NOESCAPE)) {
					$this->isDocumented = FALSE;
					break;
				}
			}
		}

		return $this->isDocumented;
	}


	/**
	 * @return bool
	 */
	public function isVisibilityLevelPublic()
	{
		return $this->getVisibilityLevel() & Visibility::IS_PUBLIC;
	}


	/**
	 * @return ReflectionFactory
	 */
	public function getReflectionFactory()
	{
		return $this->reflectionFactory;
	}


	/**
	 * @return int
	 */
	public function getVisibilityLevel()
	{
		return $this->configuration->getOption(CO::VISIBILITY_LEVELS);
	}


	/**
	 * @return array
	 */
	private function sortUsedMethods(array $usedMethods)
	{
		array_walk($usedMethods, function (&$methods) {
			ksort($methods);
			array_walk($methods, function (&$aliasedMethods) {
				if ( ! isset($aliasedMethods['aliases'])) {
					$aliasedMethods['aliases'] = [];
				}
				ksort($aliasedMethods['aliases']);
			});
		});

		return $usedMethods;
	}

}
