<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Console\Question;

use Symfony\Component\Console\Question\ConfirmationQuestion as BaseConfirmationQuestion;


class ConfirmationQuestion extends BaseConfirmationQuestion
{

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion()
	{
		return sprintf(
			'<info>%s</info> [<comment>%s</comment>] ',
			parent::getQuestion(),
			$this->getDefault() === TRUE ? 'yes' : 'no'
		);
	}

}
