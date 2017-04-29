<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Events;

use ApiGen\Console\ProgressBar;
use Kdyby\Events\Subscriber;


class ProgressBarIncrement implements Subscriber
{

	/**
	 * @var ProgressBar
	 */
	private $progressBar;


	public function __construct(ProgressBar $progressBar)
	{
		$this->progressBar = $progressBar;
	}


	/**
	 * @return string[]
	 */
	public function getSubscribedEvents()
	{
		return [
			'ApiGen\Generator\TemplateGenerators\NamespaceGenerator::onGenerateProgress',
			'ApiGen\Generator\TemplateGenerators\PackageGenerator::onGenerateProgress',
			'ApiGen\Generator\TemplateGenerators\ClassElementGenerator::onGenerateProgress',
			'ApiGen\Generator\TemplateGenerators\ConstantElementGenerator::onGenerateProgress',
			'ApiGen\Generator\TemplateGenerators\FunctionElementGenerator::onGenerateProgress',
			'ApiGen\Generator\TemplateGenerators\SourceCodeGenerator::onGenerateProgress'
		];
	}


	/**
	 * @param int $size
	 */
	public function onGenerateProgress($size = 1)
	{
		$this->progressBar->increment($size);
	}

}
