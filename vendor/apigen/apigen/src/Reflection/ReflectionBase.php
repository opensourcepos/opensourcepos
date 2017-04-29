<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection;

use ApiGen\Configuration\Configuration;
use ApiGen\Parser\Elements\Elements;
use ApiGen\Parser\ParserResult;
use ApiGen\Reflection\TokenReflection\Reflection;
use ApiGen\Reflection\TokenReflection\ReflectionFactory;
use ArrayObject;
use Nette;
use TokenReflection\IReflection;
use TokenReflection\IReflectionClass;
use TokenReflection\IReflectionFunction;
use TokenReflection\IReflectionMethod;
use TokenReflection\IReflectionParameter;
use TokenReflection\IReflectionProperty;


/**
 * @method string getDocComment()
 */
abstract class ReflectionBase extends Nette\Object implements Reflection
{

	/**
	 * @var string
	 */
	protected $reflectionType;

	/**
	 * @var IReflectionClass|IReflectionFunction|IReflectionMethod|IReflectionParameter|IReflectionProperty
	 */
	protected $reflection;

	/**
	 * @var Configuration
	 */
	protected $configuration;

	/**
	 * @var ParserResult
	 */
	protected $parserResult;

	/**
	 * @var ReflectionFactory
	 */
	protected $reflectionFactory;


	public function __construct(IReflection $reflection)
	{
		$this->reflectionType = get_class($this);
		$this->reflection = $reflection;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->reflection->getName();
	}


	/**
	 * @return string
	 */
	public function getPrettyName()
	{
		return $this->reflection->getPrettyName();
	}


	/**
	 * @return bool
	 */
	public function isInternal()
	{
		return $this->reflection->isInternal();
	}


	/**
	 * @return bool
	 */
	public function isTokenized()
	{
		return $this->reflection->isTokenized();
	}


	/**
	 * @return string
	 */
	public function getFileName()
	{
		return $this->reflection->getFileName();
	}


	/**
	 * @return int
	 */
	public function getStartLine()
	{
		$startLine = $this->reflection->getStartLine();
		if ($doc = $this->getDocComment()) {
			$startLine -= substr_count($doc, "\n") + 1;
		}
		return $startLine;
	}


	/**
	 * @return int
	 */
	public function getEndLine()
	{
		return $this->reflection->getEndLine();
	}


	public function setConfiguration(Configuration $configuration)
	{
		$this->configuration = $configuration;
	}


	public function setParserResult(ParserResult $parserResult)
	{
		$this->parserResult = $parserResult;
	}


	public function setReflectionFactory(ReflectionFactory $reflectionFactory)
	{
		$this->reflectionFactory = $reflectionFactory;
	}


	/**
	 * @return ArrayObject|ReflectionClass[]
	 */
	public function getParsedClasses()
	{
		return $this->parserResult->getElementsByType(Elements::CLASSES);
	}

}
