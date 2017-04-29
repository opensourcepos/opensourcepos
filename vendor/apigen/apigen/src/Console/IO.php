<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Console;

use ApiGen\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;


class IO implements IOInterface
{

	/**
	 * @var InputInterface
	 */
	private $input;

	/**
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * @var HelperSet
	 */
	private $helperSet;


	public function __construct(HelperSet $helperSet)
	{
		$this->input = new ArrayInput([]);
		$this->output = new NullOutput;
		$this->helperSet = $helperSet;
	}


	/**
	 * @return InputInterface
	 */
	public function getInput()
	{
		return $this->input;
	}


	public function setInput(InputInterface $input)
	{
		$this->input = $input;
	}


	/**
	 * @return OutputInterface
	 */
	public function getOutput()
	{
		return $this->output;
	}


	public function setOutput(OutputInterface $output)
	{
		$this->output = $output;
	}


	/**
	 * {@inheritdoc}
	 */
	public function writeln($message)
	{
		return $this->output->writeln($message);
	}


	/**
	 * {@inheritdoc}
	 */
	public function ask($question, $default = NULL)
	{
		/** @var QuestionHelper $helper */
		$helper = $this->helperSet->get('question');
		$question = new ConfirmationQuestion($question, $default);
		return $helper->ask($this->input, $this->output, $question);
	}

}
