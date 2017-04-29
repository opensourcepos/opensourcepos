<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Parser;

use ApiGen\Charset\CharsetConvertor;
use ApiGen\Parser\Broker\Backend;
use ArrayObject;
use SplFileInfo;
use TokenReflection\Broker;
use TokenReflection\Exception\FileProcessingException;


class Parser
{

	/**
	 * @var Broker
	 */
	private $broker;

	/**
	 * @var CharsetConvertor
	 */
	private $charsetConvertor;

	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * @var ParserResult
	 */
	private $parserResult;


	public function __construct(Broker $broker, CharsetConvertor $charsetConvertor, ParserResult $parserResult)
	{
		$this->broker = $broker;
		$this->charsetConvertor = $charsetConvertor;
		$this->parserResult = $parserResult;
	}


	/**
	 * @param SplFileInfo[] $files
	 */
	public function parse($files)
	{
		foreach ($files as $file) {
			$content = $this->charsetConvertor->convertFileToUtf($file->getPathname());
			try {
				$this->broker->processString($content, $file->getPathname());

			} catch (FileProcessingException $exception) {
				$this->errors[] = $exception;
			}
		}

		$this->extractBrokerDataForParserResult($this->broker);
	}


	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	private function extractBrokerDataForParserResult(Broker $broker)
	{
		$allFoundClasses = $broker->getClasses(
			Backend::TOKENIZED_CLASSES | Backend::INTERNAL_CLASSES | Backend::NONEXISTENT_CLASSES
		);

		$classes = new ArrayObject($allFoundClasses);
		$constants = new ArrayObject($broker->getConstants());
		$functions = new ArrayObject($broker->getFunctions());
		$internalClasses = new ArrayObject($broker->getClasses(Backend::INTERNAL_CLASSES));
		$tokenizedClasses = new ArrayObject($broker->getClasses(Backend::TOKENIZED_CLASSES));

		$classes->uksort('strcasecmp');
		$constants->uksort('strcasecmp');
		$functions->uksort('strcasecmp');

		$this->loadToParserResult($classes, $constants, $functions, $internalClasses, $tokenizedClasses);
	}


	private function loadToParserResult(
		ArrayObject $classes,
		ArrayObject $constants,
		ArrayObject $functions,
		ArrayObject $internalClasses,
		ArrayObject $tokenizedClasses
	) {
		$this->parserResult->setClasses($classes);
		$this->parserResult->setConstants($constants);
		$this->parserResult->setFunctions($functions);
		$this->parserResult->setInternalClasses($internalClasses);
		$this->parserResult->setTokenizedClasses($tokenizedClasses);
	}

}
