<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Configuration\Readers;

use Nette\Neon\Neon;


class NeonFile extends AbstractFile implements ReaderInterface
{

	/**
	 * {@inheritdoc}
	 */
	public function read()
	{
		$json = file_get_contents($this->path);
		return (array) Neon::decode($json);
	}

}
