<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection\TokenReflection;

use ApiGen\Configuration\Configuration;
use ApiGen\Parser\ParserResult;
use ApiGen\Reflection\ReflectionBase;
use ApiGen\Reflection\ReflectionClass;
use ApiGen\Reflection\ReflectionConstant;
use ApiGen\Reflection\ReflectionExtension;
use ApiGen\Reflection\ReflectionFunction;
use ApiGen\Reflection\ReflectionMethod;
use ApiGen\Reflection\ReflectionMethodMagic;
use ApiGen\Reflection\ReflectionParameter;
use ApiGen\Reflection\ReflectionParameterMagic;
use ApiGen\Reflection\ReflectionProperty;
use ApiGen\Reflection\ReflectionPropertyMagic;
use RuntimeException;
use TokenReflection\IReflectionClass;
use TokenReflection\IReflectionConstant;
use TokenReflection\IReflectionExtension;
use TokenReflection\IReflectionFunction;
use TokenReflection\IReflectionMethod;
use TokenReflection\IReflectionParameter;
use TokenReflection\IReflectionProperty;


class ReflectionFactory
{

	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var ParserResult
	 */
	private $parserResult;


	public function __construct(Configuration $configuration, ParserResult $parserResult)
	{
		$this->configuration = $configuration;
		$this->parserResult = $parserResult;
	}


	/**
	 * @param IReflectionClass|IReflectionConstant|IReflectionFunction $tokenReflection
	 * @return ReflectionClass|ReflectionConstant|ReflectionMethod
	 */
	public function createFromReflection($tokenReflection)
	{
		$reflection = $this->createByReflectionType($tokenReflection);
		return $this->setDependencies($reflection);
	}


	/**
	 * @return ReflectionMethodMagic
	 */
	public function createMethodMagic(array $settings)
	{
		$reflection = new ReflectionMethodMagic($settings);
		return $this->setDependencies($reflection);
	}


	/**
	 * @return ReflectionParameterMagic
	 */
	public function createParameterMagic(array $settings)
	{
		$reflection = new ReflectionParameterMagic($settings);
		return $this->setDependencies($reflection);
	}


	/**
	 * @return ReflectionPropertyMagic
	 */
	public function createPropertyMagic(array $settings)
	{
		$reflection = new ReflectionPropertyMagic($settings);
		return $this->setDependencies($reflection);

	}


	/**
	 * @param IReflectionClass|IReflectionConstant|IReflectionMethod $reflection
	 * @return ReflectionClass|ReflectionConstant|ReflectionMethod
	 */
	private function createByReflectionType($reflection)
	{
		if ($reflection instanceof IReflectionClass) {
			return new ReflectionClass($reflection);

		} elseif ($reflection instanceof IReflectionConstant) {
			return new ReflectionConstant($reflection);

		} elseif ($reflection instanceof IReflectionMethod) {
			return new ReflectionMethod($reflection);

		} elseif ($reflection instanceof IReflectionProperty) {
			return new ReflectionProperty($reflection);

		} elseif ($reflection instanceof IReflectionParameter) {
			return new ReflectionParameter($reflection);

		} elseif ($reflection instanceof IReflectionFunction) {
			return new ReflectionFunction($reflection);

		} elseif ($reflection instanceof IReflectionExtension) {
			return new ReflectionExtension($reflection);
		}

		throw new RuntimeException('Invalid reflection class type ' . get_class($reflection));
	}


	/**
	 * @return ReflectionBase
	 */
	private function setDependencies(ReflectionBase $reflection)
	{
		$reflection->setConfiguration($this->configuration);
		$reflection->setParserResult($this->parserResult);
		$reflection->setReflectionFactory($this);
		return $reflection;
	}

}
