<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters;

use ApiGen\Generator\Resolvers\RelativePathResolver;


class PathFilters extends Filters
{

	/**
	 * @var RelativePathResolver
	 */
	private $relativePathResolver;


	public function __construct(RelativePathResolver $relativePathResolver)
	{
		$this->relativePathResolver = $relativePathResolver;
	}


	/**
	 * @param string $fileName
	 * @return string
	 */
	public function relativePath($fileName)
	{
		return $this->relativePathResolver->getRelativePath($fileName);
	}

}
