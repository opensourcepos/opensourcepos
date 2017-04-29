<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection\Parts;

use ApiGen\Configuration\Configuration;
use ApiGen\Configuration\ConfigurationOptions as CO;


/**
 * @property-read $isDocumented
 * @property-read Configuration $configuration
 * @method bool isDeprecated()
 */
trait IsDocumentedMagic
{

	/**
	 * @return bool
	 */
	public function isDocumented()
	{
		if ($this->isDocumented === NULL) {
			$deprecated = $this->configuration->getOption(CO::DEPRECATED);
			$this->isDocumented = $deprecated || ! $this->isDeprecated();
		}

		return $this->isDocumented;
	}

}
