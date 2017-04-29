<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator;

use ApiGen\Console\ProgressBar;


class GeneratorQueue
{

	/**
	 * @var ProgressBar
	 */
	private $progressBar;

	/**
	 * @var TemplateGenerator[]
	 */
	private $queue = [];


	public function __construct(ProgressBar $progressBar)
	{
		$this->progressBar = $progressBar;
	}


	public function run()
	{
		$this->progressBar->init($this->getStepCount());
		foreach ($this->getAllowedQueue() as $templateGenerator) {
			$templateGenerator->generate();
		}
	}


	public function addToQueue(TemplateGenerator $templateGenerator)
	{
		$this->queue[] = $templateGenerator;
	}


	/**
	 * @return TemplateGenerator[]
	 */
	public function getQueue()
	{
		return $this->queue;
	}


	/**
	 * @return TemplateGenerator[]
	 */
	private function getAllowedQueue()
	{
		return array_filter($this->queue, function (TemplateGenerator $generator) {
			if ($generator instanceof ConditionalTemplateGenerator) {
				return $generator->isAllowed();

			} else {
				return TRUE;
			}
		});
	}


	/**
	 * @return int
	 */
	private function getStepCount()
	{
		$steps = 0;
		foreach ($this->getAllowedQueue() as $templateGenerator) {
			if ($templateGenerator instanceof StepCounter) {
				$steps += $templateGenerator->getStepCount();
			}
		}
		return $steps;
	}

}
