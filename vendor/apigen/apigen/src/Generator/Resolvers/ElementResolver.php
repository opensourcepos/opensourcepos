<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator\Resolvers;

use ApiGen\Parser\ParserResult;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionElement;
use ApiGen\Reflection\ReflectionFunction;
use ApiGen\Reflection\ReflectionMethod;
use ApiGen\Reflection\ReflectionParameter;
use ApiGen\Reflection\ReflectionProperty;
use ArrayObject;
use TokenReflection\Resolver;


class ElementResolver
{

	/**
	 * @var array
	 */
	private $simpleTypes = [
		'boolean' => 1,
		'integer' => 1,
		'float' => 1,
		'string' => 1,
		'array' => 1,
		'object' => 1,
		'resource' => 1,
		'callback' => 1,
		'callable' => 1,
		'NULL' => 1,
		'false' => 1,
		'true' => 1,
		'mixed' => 1
	];

	/**
	 * @var ParserResult
	 */
	private $parserResult;


	public function __construct(ParserResult $parserResult)
	{
		$this->parserResult = $parserResult;
	}


	/**
	 * @param string $name
	 * @param string $namespace
	 * @return ReflectionClass|NULL
	 */
	public function getClass($name, $namespace = '')
	{
		$parsedClasses = $this->parserResult->getClasses();
		$class = $this->findElementByNameAndNamespace($parsedClasses, $name, $namespace);
		if ($class && $class->isDocumented()) {
			return $class;
		}

		return NULL;
	}


	/**
	 * @param string $name
	 * @param string $namespace
	 * @return ReflectionConstant|NULL
	 */
	public function getConstant($name, $namespace = '')
	{
		$parsedConstants = $this->parserResult->getConstants();
		$constant = $this->findElementByNameAndNamespace($parsedConstants, $name, $namespace);
		if ($constant && $constant->isDocumented()) {
			return $constant;
		}

		return NULL;
	}


	/**
	 * @param string $name
	 * @param string $namespace
	 * @return ReflectionFunction|NULL
	 */
	public function getFunction($name, $namespace = '')
	{
		$parsedFunctions = $this->parserResult->getFunctions();
		$function = $this->findElementByNameAndNamespace($parsedFunctions, $name, $namespace);
		if ($function && $function->isDocumented()) {
			return $function;
		}

		return NULL;
	}


	/**
	 * Tries to parse a definition of a class/method/property/constant/function
	 *
	 * @param string $definition
	 * @param ReflectionElement|ReflectionParameter $reflectionElement Link context
	 * @param string $expectedName
	 * @return ReflectionElement|NULL
	 */
	public function resolveElement($definition, $reflectionElement, &$expectedName = NULL)
	{
		if ($this->isSimpleType($definition)) {
			return NULL;
		}

		$originalContext = $reflectionElement;
		$reflectionElement = $this->correctContextForParameterOrClassMember($reflectionElement);
		if ($reflectionElement === NULL) {
			return NULL;
		}

		// self, $this references
		if ($definition === 'self' || $definition === '$this') {
			return $reflectionElement instanceof ReflectionClass ? $reflectionElement : NULL;
		}

		$definitionBase = substr($definition, 0, strcspn($definition, '\\:'));
		$namespaceAliases = $reflectionElement->getNamespaceAliases();
		$className = Resolver::resolveClassFqn($definition, $namespaceAliases, $reflectionElement->getNamespaceName());

		if ($resolved = $this->resolveIfParsed($definition, $reflectionElement)) {
			return $resolved;
		}

		if ( ! empty($definitionBase) && isset($namespaceAliases[$definitionBase]) && $definition !== $className) {
			// Aliased class
			$expectedName = $className;

			if (strpos($className, ':') === FALSE) {
				return $this->getClass($className, $reflectionElement->getNamespaceName());

			} else {
				$definition = $className;
			}
		}

		if (($reflectionElement instanceof ClassReflectionInterface)
			&& ($pos = strpos($definition, '::') || $pos = strpos($definition, '->'))
		) {
			$reflectionElement = $this->resolveContextForClassProperty($definition, $reflectionElement, $pos);
			$definition = substr($definition, $pos + 2);

		} elseif ($originalContext instanceof ReflectionParameter) {
			return NULL;
		}

		if ( ! $this->isContextUsable($reflectionElement)) {
			return NULL;
		}

		return $this->resolveIfInContext($definition, $reflectionElement);
	}


	/**
	 * @param ReflectionClass|ReflectionParameter|ReflectionFunction|ReflectionElement $reflectionElement
	 * @return ReflectionClass|ReflectionFunction
	 */
	private function correctContextForParameterOrClassMember($reflectionElement)
	{
		if ($reflectionElement instanceof ReflectionParameter && $reflectionElement->getDeclaringClassName() === NULL) {
			// Parameter of function in namespace or global space
			return $this->getFunction($reflectionElement->getDeclaringFunctionName());

		} elseif ($reflectionElement instanceof ReflectionMethod || $reflectionElement instanceof ReflectionParameter
			|| ($reflectionElement instanceof ReflectionConstant && $reflectionElement->getDeclaringClassName() !== NULL)
			|| $reflectionElement instanceof ReflectionProperty
		) {
			// Member of a class
			return $this->getClass($reflectionElement->getDeclaringClassName());
		}
		return $reflectionElement;
	}


	/**
	 * @param string $definition
	 * @param int $pos
	 * @param ReflectionElement $reflectionElement
	 * @return ReflectionClass
	 */
	private function resolveContextForSelfProperty($definition, $pos, ReflectionElement $reflectionElement)
	{
		$class = $this->getClass(substr($definition, 0, $pos), $reflectionElement->getNamespaceName());
		if ($class === NULL) {
			$fqnName = Resolver::resolveClassFqn(
				substr($definition, 0, $pos), $reflectionElement->getNamespaceAliases(), $reflectionElement->getNamespaceName()
			);
			$class = $this->getClass($fqnName);
		}
		return $class;
	}


	/**
	 * @param string $definition
	 * @return bool
	 */
	private function isSimpleType($definition)
	{
		if (empty($definition) || isset($this->simpleTypes[$definition])) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * @param string $definition
	 * @param ReflectionElement $reflectionElement
	 * @return ReflectionClass|ReflectionConstant|ReflectionFunction|NULL
	 */
	private function resolveIfParsed($definition, ReflectionElement $reflectionElement)
	{
		$definition = $this->removeEndBrackets($definition);
		if ($class = $this->getClass($definition, $reflectionElement->getNamespaceName())) {
			return $class;

		} elseif ($constant = $this->getConstant($definition, $reflectionElement->getNamespaceName())) {
			return $constant;

		} elseif ($function = $this->getFunction($definition, $reflectionElement->getNamespaceName())) {
			return $function;
		}
		return NULL;
	}


	/**
	 * @param $definition
	 * @param ReflectionClass $context
	 * @return ReflectionConstant|ReflectionMethod|ReflectionProperty|NULL
	 */
	private function resolveIfInContext($definition, ReflectionClass $context)
	{
		$definition = $this->removeEndBrackets($definition);
		$definition = $this->removeStartDollar($definition);

		if ($context->hasProperty($definition)) {
			return $context->getProperty($definition);

		} elseif ($context->hasMethod($definition)) {
			return $context->getMethod($definition);

		} elseif ($context->hasConstant($definition)) {
			return $context->getConstant($definition);
		}
		return NULL;
	}


	/**
	 * @param string $definition
	 * @return string
	 */
	private function removeEndBrackets($definition)
	{
		if (substr($definition, -2) === '()') {
			return substr($definition, 0, -2);
		}
		return $definition;
	}


	/**
	 * @param string $definition
	 * @return string
	 */
	private function removeStartDollar($definition)
	{
		if ($definition[0] === '$') {
			return substr($definition, 1);
		}
		return $definition;
	}


	/**
	 * @param string $definition
	 * @param ReflectionClass $reflectionClass
	 * @param int $pos
	 * @return ReflectionClass
	 */
	private function resolveContextForClassProperty($definition, ReflectionClass $reflectionClass, $pos)
	{
		// Class::something or Class->something
		if (strpos($definition, 'parent::') === 0 && ($parentClassName = $reflectionClass->getParentClassName())) {
			return $this->getClass($parentClassName);

		} elseif (strpos($definition, 'self::') !== 0) {
			return $this->resolveContextForSelfProperty($definition, $pos, $reflectionClass);
		}
		return $reflectionClass;
	}


	/**
	 * @param NULL|ReflectionElement $reflectionElement
	 * @return bool
	 */
	private function isContextUsable($reflectionElement)
	{
		if ($reflectionElement === NULL || $reflectionElement instanceof ReflectionConstant
			|| $reflectionElement instanceof ReflectionFunction
		) {
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * @param array|ArrayObject $elements
	 * @param string $name
	 * @param string $namespace
	 * @return ReflectionClass|NULL
	 */
	private function findElementByNameAndNamespace($elements, $name, $namespace)
	{
		$namespacedName = $namespace . '\\' . $name;
		if (isset($elements[$namespacedName])) {
			return $elements[$namespacedName];
		}

		$shortName = ltrim($name, '\\');
		if (isset($elements[$shortName])) {
			return $elements[$shortName];
		}

		return NULL;
	}

}
