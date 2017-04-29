<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Configuration;

use Symfony\Component\OptionsResolver\OptionsResolver;


interface OptionsResolverFactory
{

	/**
	 * @return OptionsResolver
	 */
	function create();

}
