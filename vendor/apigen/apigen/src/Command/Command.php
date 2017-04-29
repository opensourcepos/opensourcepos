<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Command;

use Symfony;


class Command extends Symfony\Component\Console\Command\Command
{

	/**
	 * {@inheritdoc}
	 */
	public function addOption($name, $shortcut = NULL, $mode = NULL, $description = '', $default = NULL)
	{
		$name = $this->dashFormat($name);
		return parent::addOption($name, $shortcut, $mode, $description, $default);
	}


	/**
	 * @param string $name
	 * @return string
	 */
	private function dashFormat($name)
	{
		return preg_replace_callback('~([A-Z])~', function ($matches) {
			return '-' . strtolower($matches[1]);
		}, $name);
	}

}
