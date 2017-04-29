<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator\Resolvers;

use ApiGen\Configuration\Configuration;
use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\FileSystem\FileSystem;
use InvalidArgumentException;


class RelativePathResolver
{

	/**
	 * @var Configuration
	 */
	private $configuration;


	public function __construct(Configuration $configuration)
	{
		$this->configuration = $configuration;
	}


	/**
	 * @param string $fileName
	 * @return string
	 */
	public function getRelativePath($fileName)
	{
		foreach ($this->configuration->getOption(CO::SOURCE) as $directory) {
			if (strpos($fileName, $directory) === 0) {
				return $this->getFileNameWithoutSourcePath($fileName, $directory);
			}
		}

		throw new InvalidArgumentException(sprintf('Could not determine "%s" relative path', $fileName));
	}


	/**
	 * @param string $fileName
	 * @param string $directory
	 * @return string
	 */
	private function getFileNameWithoutSourcePath($fileName, $directory)
	{
		$directory = rtrim($directory, '/');
		$fileName = substr($fileName, strlen($directory) + 1);
		return FileSystem::normalizePath($fileName);
	}

}
